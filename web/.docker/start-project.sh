#!/usr/bin/env bash

# ${APP_ENVIRONMENT}
# => global env-var (introduced at build time, see Dockerfile)
# => could be overridden by runtime env-var
# add docker user (if not exist)

USER_EXIST=`id -u ${HOST_UID} > /dev/null 2>&1`
if [ $? = 1 ]; then
    groupadd --gid $HOST_GID $CONTAINER_GROUP
    useradd --uid $HOST_UID --gid $HOST_GID -ms /bin/bash $CONTAINER_USER

    echo "added system user: \"${CONTAINER_USER}\""
fi

bin/set_owner.sh
bin/set_acl.sh ${CONTAINER_USER}

##############################################################
# wait for es services to be available
##############################################################

# wait for mysql service to be available
until nc -z -v -w30 mysql 3306 > /dev/null 2>&1
do
    echo "Waiting for MySQL connection "
    sleep 5
done

if [ "${APP_ENVIRONMENT}" = "dev" ]; then
    gosu ${CONTAINER_USER} composer install --no-interaction
#    gosu ${CONTAINER_USER} vendor/bin/bdi detect drivers
    gosu ${CONTAINER_USER} yarn install --silent --non-interactive --network-timeout 1000000
#    gosu ${CONTAINER_USER} vendor/bin/bdi detect drivers
    gosu ${CONTAINER_USER} php bin/console assets:install --env=${APP_ENVIRONMENT}
elif [ "${APP_ENVIRONMENT}" = "test" ]; then
    gosu ${CONTAINER_USER} php bin/console cache:clear --env=test
    gosu ${CONTAINER_USER} php bin/console doctrine:schema:update --force --env=test
    gosu ${CONTAINER_USER} php bin/console app:event-store:create --env=test
#    gosu ${CONTAINER_USER} vendor/bin/bdi detect drivers
else
    gosu ${CONTAINER_USER} php bin/console doctrine:migrations:migrate -n --env="${APP_ENVIRONMENT}"
    gosu ${CONTAINER_USER} php bin/console assets:install --env=${APP_ENVIRONMENT}
#    gosu ${CONTAINER_USER} php bin/console app:event-store:replay --env="${APP_ENVIRONMENT}"
fi

gosu ${CONTAINER_USER} php bin/console lexik:jwt:generate-keypair --skip-if-exists


# notice: this should be done after composer-tasks, otherwise composer-task
#         could run with enabled xdebug (endless script execution!)
#
# enable/disable xdebug:
# - only enable in dev environment
# - only enable if PHP_XDEBUG_ENABLED is set to 1
if [ "${APP_ENVIRONMENT}" != "dev" ] || [ "$PHP_XDEBUG_ENABLED" != "1" ]; then
    sed -i -e 's/^zend_extension/;zend_extension/g' /usr/local/etc/php/conf.d/xdebug.ini
else
    PHP_XDEBUG_REMOTE_HOST=$(hostname --ip-address | awk -F '.' '{printf "%d.%d.%d.1",$1,$2,$3}')
    sed -i -e "s/xdebug\.client_host.*/xdebug.client_host=$PHP_XDEBUG_REMOTE_HOST/g" /usr/local/etc/php/conf.d/xdebug.ini
    sed -i -e 's/^;zend_extension/zend_extension/g' /usr/local/etc/php/conf.d/xdebug.ini
    echo "xdebug.mode=develop,debug" >> "/usr/local/etc/php/conf.d/xdebug.ini"
    echo "xdebug.client_host=$PHP_XDEBUG_REMOTE_HOST" >> "/usr/local/etc/php/conf.d/xdebug.ini"
    echo "xdebug.discover_client_host=0" >> "/usr/local/etc/php/conf.d/xdebug.ini"
    # echo "xdebug.output_dir=/tmp/xdebug" >> "/usr/local/etc/php/conf.d/xdebug.ini"
    # echo "xdebug.log=/tmp/xdebug/xdebug.log" >> "/usr/local/etc/php/conf.d/xdebug.ini"
    echo "xdebug.start_with_request=yes" >> "/usr/local/etc/php/conf.d/xdebug.ini"
fi

# add all docker networks to RemoteIPInternalProxy (needed for REMOTE_ADDR behind reverse proxy & logging)
#ip -h -o address | grep eth | awk '{ print $4 }' > /etc/apache2/conf-available/trusted-docker-proxies.conf

apache2-foreground -DFOREGROUND
