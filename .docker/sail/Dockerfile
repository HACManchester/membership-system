# Local development runtime.
#
# Built on the official PHP image for the chosen version, with the same
# extensions as membership-deployment's production images, so each PHP upgrade
# is exercised locally on the base it will run on in production. (It used to
# install PHP from the ondrej/php PPA on Ubuntu, which ties every build to
# Launchpad being up.)
#
# Node isn't installed: yarn runs on the host (see CLAUDE.md).

ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-cli

ARG WWWGROUP
ARG DEBIAN_FRONTEND=noninteractive
ARG INSTALL_XDEBUG=false

WORKDIR /var/www/html

ENV TZ=UTC

# Older PHP images are based on Debian releases that have reached end of life
# and been moved to archive.debian.org.
RUN . /etc/os-release; \
    case "$VERSION_CODENAME" in \
        buster|bullseye) \
            case "$VERSION_CODENAME" in \
                buster) security="buster/updates" ;; \
                *) security="${VERSION_CODENAME}-security" ;; \
            esac; \
            echo "deb http://archive.debian.org/debian ${VERSION_CODENAME} main" > /etc/apt/sources.list; \
            echo "deb http://archive.debian.org/debian-security ${security} main" >> /etc/apt/sources.list; \
            rm -f /etc/apt/sources.list.d/*; \
            ;; \
    esac

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        gosu git unzip zip supervisor sqlite3 libcap2-bin default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Production's set (bin/webserver/Dockerfile in membership-deployment), plus
# pcov for coverage. Xdebug is opt-in, as in production: build with
# SAIL_INSTALL_XDEBUG=true, then set SAIL_XDEBUG_MODE to use it.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath opcache pdo_mysql mysqli zip intl gettext gd redis pcov
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then install-php-extensions xdebug; fi

RUN setcap "cap_net_bind_service=+ep" /usr/local/bin/php

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /usr/local/etc/php/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80

ENTRYPOINT ["start-container"]
