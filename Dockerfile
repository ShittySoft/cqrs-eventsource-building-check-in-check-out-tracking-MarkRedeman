FROM php:7.0.7-cli

RUN pecl install xdebug-2.4.0 \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/my_app

CMD ["php -S 0.0.0.0:8080 -t public/"]
