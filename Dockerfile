# Use an official PHP runtime as a parent image
FROM dunglas/frankenphp:php8.3 AS api-template


COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
ARG UID=1000
ARG GID=1000

# "Hash Sum mismatch" fix
COPY docker/badproxy /etc/apt/apt.conf.d/99fixbadproxy

RUN apt-get update && apt-get install -y ssh git bash libzip-dev unzip nano vim && \
    install-php-extensions \
	pdo_mysql \
	opcache

COPY docker/php/php.ini $PHP_INI_DIR/php.ini

ENV SERVER_NAME=:80
ENV COMPOSER_ALLOW_SUPERUSER=1
CMD ["/app/docker/run.sh"]

FROM api-template AS api-dev

RUN install-php-extensions xdebug
COPY docker/php/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini
COPY docker/php/opcache_dev.ini $PHP_INI_DIR/conf.d/opcache.ini


FROM api-template AS api-prod

#ENV FRANKENPHP_CONFIG="worker ./public/index.php"
#ENV APP_RUNTIME=Runtime\\FrankenPhpSymfony\\Runtime
COPY . /app
COPY docker/php/opcache_prod.ini $PHP_INI_DIR/conf.d/opcache.ini
RUN export DATABASE_URL=null && \
    /usr/bin/composer install --prefer-dist --no-progress --no-interaction --no-scripts --no-dev --ignore-platform-reqs && \
    /usr/bin/composer dump-autoload -o --no-interaction --no-dev

