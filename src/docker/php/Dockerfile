FROM php:7.4.6-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN set -eux && \
  apk add --update-cache --no-cache --virtual=.build-dependencies tzdata && \
  cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
  echo "Asia/Tokyo" > /etc/timezone && \
  apk del .build-dependencies && \
  apk add --update --no-cache git autoconf g++ libtool make libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev && \
  docker-php-ext-configure opcache --enable-opcache && \
  docker-php-ext-install opcache bcmath pdo_mysql gd exif zip && \
  pecl install xdebug && \
  docker-php-ext-enable xdebug && \
  apk del autoconf g++ libtool make && \
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
  composer config -g repos.packagist composer https://packagist.jp && \
  composer global require hirak/prestissimo

COPY ./docker/php/config/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/config/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY . .

WORKDIR /var/www/html
