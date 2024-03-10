guess-game
=============

Have a look at **https://guess-game.stage.projektmotor.de/** for more information.

### How to start local dev?

1. ##### Setup a proxy with nginx (which includes docker-gen)
   Place the following ```docker-compose.yml``` in the folder of your choice, e.g. ```/var/apps/nginx-proxy/``` or ```/<your-home-dir>/workspace/nginx-proxy/```.
    ```yaml
    version: "3.4"

    services:
        nginx-proxy:
            image: jwilder/nginx-proxy:alpine
            container_name: nginx-proxy
            ports:
                - target: 80
                  published: 80
                  protocol: tcp
                - target: 443
                  published: 443
                  protocol: tcp
            volumes:
                - /var/run/docker.sock:/tmp/docker.sock:ro
                - ./certs:/etc/nginx/certs
                - ./vhosts:/etc/nginx/conf.d
            networks:
                - guess-game-dev

    networks:
        guess-game-dev:
           external: true
    ```

2. ##### Install local ssl certificates
   The following could be automated with own Dockerfile
   including jwilder/nginx-proxy and mkCert - alike to dev-tls docker file. <br>

   First you have to settle on a domain to use for guess-game.
   We will use `guess-game.local` in the following expamples.
   Using only `guess-game` would be troubling for certificates since most browsers
   do not accept wildcard certificates for second-level domains:
   e.g. browser will not accept certs created with `*.guess-game` for subdomain `api.guess-game`

   ###### install [mkCert](https://github.com/FiloSottile/mkcert)
   ```BASH
    # linux
    curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
    chmod +x mkcert-v*-linux-amd64
    sudo cp mkcert-v*-linux-amd64 /usr/local/bin/mkcert
   ```
   ```BASH
   # macOS
    brew install mkcert
    brew install nss # if you use Firefox
   ```
   ###### Install rootCA
   This automatically adds mkcert's rootCA to your systems trusted CAs so you no longer will be bugged by untrusted certificate notifications in your local browser.
    ```BASH
    mkcert --install
    ```
   ###### create Certs
   Navigate to the nginx-proxy certs volume e.g. `cd /var/apps/nginx-proxy/certs` or ```/<your-home-dir>/workspace/nginx-proxy/certs```
   ```BASH
   mkcert -key-file guess-game.local.key -cert-file guess-game.local.crt guess-game.local *.guess-game.local
   ```
   This generates a certificate for all subdomains of `guess-game.local`
3. ##### setup dns
    ```BASH
    # /etc/hosts
    127.0.0.1	guess-game.local
    ```
4. ##### start nginx-proxy

    ```BASH
    # this need to be run only once
    docker network create guess-game-dev
    ```

    ```BASH
    # /var/apps/nginx-proxy/ or /<your-home-dir>/workspace/nginx-proxy/
    docker-compose up -d
    ```

5. ##### create .env
   ```BASH
   #<yourWorkSpace>/guess-game
   cp .env.dist .env
   ```
   Adjust sensible vars like e.g. `JWT_KEY` and `DB_PASSWORD` as needed.
   Make sure the `DOMAIN_NAME` matches the one used when creating certificates.

6. ##### add in your ```.bashrc``` (or ```.zshrc```)
    ```bash
    $ vi ~/.bashrc

    ...
    export HOST_UID=$(id -u)      # UID is now available for docker-compose.yml
    export HOST_GID=$(id -g)      # GID is now available for docker-compose.yml

7. ##### start guess-game stack
    ```BASH
    docker-compose up -d
    ```
   nginx-proxy will create a vhost entry for each of guess-game's services which has an environment variable `VIRTUAL_HOST` set.
   You can check the created hosts in a volume:
    ```BASH
    cat path-to-nginx-proxy/vhosts/default.conf
    ```

8. ##### Create Api-Model classes (only for dev environment)
    ```BASH
    composer create-api-client
    ```
9. ##### access guess-game
   ###### via domain names with self signed certificates
   Advantage: There is no need to accept insecure certs on every first website request.
   * https://guess-game.local
   To access with your mobile devices in your local WAN you have to replace 'guess-game.local' with your local ip address:
   * Find local ip address in Windows 7 [[1]](https://www.groovypost.com/howto/microsoft/windows-7/find-your-local-ip-address-windows-7-cmd/)
   * Find local ip address in Ubuntu [[1]](https://help.ubuntu.com/stable/ubuntu-help/net-findip.html.en) [[2]](https://itsfoss.com/check-ip-address-ubuntu/)
10. ##### optional: use XDebug
    * Activate XDebug in `.env`:
        ```
        PHP_XDEBUG_ENABLED=1
        ```
    * PHPStorm setup:
        * Settings... -> Languages & Frameworks -> PHP -> Servers: Add
            * name: has to be same as PHP_IDE_CONFIG value
            * port: 80
            * path-mapping: path of project root in host system
        * Setting -> Languages & Frameworks -> PHP -> Debug -> DBGp Proxy:
            * `Port`: 9000
    * Start containers:
        ```bash
        $ docker-compose up -d
        ```
    * After clicking "Start Listening for PHP Debug Connections" in PHPStorm you can jump to web and cli breakpoints.
    * To activate/deactivate XDebug simply adjust ENV-Variable `PHP_XDEBUG_ENABLED` in `docker-compose.yml`
      and restart containers (`docker-compose down && docker-compose up -d`)

#### Cheat Sheet

* Execute symfony command
    ```bash
    $ docker-compose exec web php bin/console [SF-CONSOLE-COMMAND]
    ```
* Start webpack encore
    ```bash
    $ docker-compose exec web node_modules/.bin/encore dev-server
    ```
* Show containers and their status
    ```bash
    $ docker-compose ps
    ```
* Container shell access
    ```bash
    $ docker-compose exec web bash
    ```
* CLI connection to MySQL:
    ```bash
    $ mysql -u guess-game -p -hmysql
    ```
* Stop services/container
    ```bash
    $ docker-compose stop
    ```
* Stop and delete container (incl. volumes, images und networks except data volumes)
    ```bash
    $ docker-compose down
    ```
