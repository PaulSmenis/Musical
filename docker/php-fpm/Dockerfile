FROM php:8.0.3-fpm

RUN apt-get update && apt-get install -y wget git unzip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

ADD ./php.ini /usr/local/etc/php/php.ini

RUN wget https://getcomposer.org/installer -O - -q \
    | php -- --install-dir=/bin --filename=composer --quiet

WORKDIR /var/www/symfony