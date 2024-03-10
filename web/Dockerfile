FROM composer:2.5 as composer
FROM php:8.3-apache-bookworm

ARG APP_ENVIRONMENT
ARG HOST_UID=1000
ARG HOST_GID=1000
ARG CONTAINER_USER=docker
ARG CONTAINER_GROUP=docker
ARG ASSET_PATH=/build

ENV MATOMO_SITE_ID=${MATOMO_SITE_ID}
ENV ASSET_PATH=${ASSET_PATH}
ENV APP_ENVIRONMENT=${APP_ENVIRONMENT}
ENV HOST_UID=${HOST_UID}
ENV HOST_GID=${HOST_UID}
ENV CONTAINER_GROUP=${CONTAINER_GROUP}
ENV CONTAINER_USER=${CONTAINER_USER}
ENV PANTHER_NO_SANDBOX 1
ENV PANTHER_CHROME_ARGUMENTS='--ignore-certificate-errors'

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

#### add sources
# PREPERATION FOR YARN & NODEJS
RUN apt-get update && apt-get install -y \
        curl \
        gnupg2 \
        apt-transport-https

# ADD YARN sources
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

#################
#### apt installs
#################
RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y \
    acl \
    default-mysql-client \
    git-core \
    gosu \
    iproute2 \
    libmagickwand-dev \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    openjdk-17-jre-headless \
    poppler-utils \
    parallel \
    netcat-traditional \
    chromium \
    unzip \
    yarn \
    vim \
    wget \
    jq \
    libatk-bridge2.0-0 \
    libgbm-dev \
    libxkbcommon-x11-0 \
    libxcomposite1 \
    libxdamage1 \
    libxfixes3 \
    libxrandr2 \
    libgtk-3-0 \
    libxshmfence1 \
    zlib1g-dev && \
    apt-get clean autoclean && \
    apt-get autoremove --yes && \
    rm -rf /var/lib/{apt,cache,log}

RUN docker-php-ext-install -j$(nproc) pdo_mysql
RUN docker-php-ext-configure opcache --enable-opcache && \
        docker-php-ext-install opcache && \
        docker-php-ext-install exif && \
        pecl install apcu && \
        docker-php-ext-enable apcu && \
        docker-php-ext-install zip && \
        docker-php-ext-configure gd --with-jpeg=/usr/include/ && \
        docker-php-ext-install gd && \
        docker-php-ext-configure intl && \
#        docker-php-ext-install bcmath && \
        docker-php-ext-install intl && \
        #  configure xdebug
        docker-php-ext-install zip && \
        pecl install xdebug && docker-php-ext-enable xdebug && \
        sed -i -e 's/zend_extension/;zend_extension/g' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;

## create docker user on host
RUN groupadd --gid ${HOST_GID} ${CONTAINER_GROUP} && \
    useradd --uid ${HOST_UID} --gid ${HOST_GID} -ms /bin/bash ${CONTAINER_USER} && \
    usermod -a -G www-data ${CONTAINER_USER} && \
    chown ${CONTAINER_USER}:${CONTAINER_GROUP} /usr/local/bin/composer

# set correct timezone
ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

##############################################################
# COMPOSER INIT
##############################################################

ENV COMPOSER_HOME /home/docker/.composer

## contains dev-mode packages
## readd "sllh/composer-versions-check:^2.0" as soon as php8 deprecation messages are fixed for this package
## see https://github.com/soullivaneuh/composer-versions-check/pull/78
RUN gosu ${CONTAINER_USER} composer global config --no-plugins allow-plugins.pyrech/composer-changelogs true
RUN gosu ${CONTAINER_USER} composer global require "pyrech/composer-changelogs:^2.1" --prefer-dist --no-progress --classmap-authoritative
RUN gosu ${CONTAINER_USER} echo $COMPOSER_HOME

# copy php.ini, xdebug, startscript and vhost
COPY .docker/usr/local/etc/php /usr/local/etc/php

#COPY --chown=${CONTAINER_USER}:${CONTAINER_GROUP} composer.json composer.lock symfony.lock /var/www/html/

#RUN cd /var/www/html; \
#    gosu ${CONTAINER_USER} composer install --optimize-autoloader --no-scripts

#COPY --chown=${CONTAINER_USER}:${CONTAINER_GROUP} package.json yarn.lock /var/www/html/
#RUN cd /var/www/html; \
#    gosu ${CONTAINER_USER} ls -al; \
#    gosu ${CONTAINER_USER} yarn install;

WORKDIR /var/www/html
COPY . /var/www/html

RUN cd /var/www/html && \
    if [ "${APP_ENVIRONMENT}" != "dev" ]; then \
        # create not yet created folders for set_owner and set_facl
        mkdir -p "/var/www/html/var" && \
        bin/set_owner.sh && \
        bin/set_acl.sh ${CONTAINER_USER} && \
#        gosu ${CONTAINER_USER} yarn build && \
        gosu ${CONTAINER_USER} php bin/console cache:clear --env="${APP_ENVIRONMENT}" --no-debug; \
    fi

WORKDIR /var/www/html
# commit start-project with 775 permissions to repository
COPY .docker/start-project.sh /start-project.sh
ENTRYPOINT /start-project.sh
