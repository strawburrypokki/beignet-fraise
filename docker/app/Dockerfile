FROM php:7.4-cli-bullseye as builder
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update && apt-get install --no-install-recommends -y \
    git zip libzip-dev unzip curl
WORKDIR /app
COPY ./ /app
RUN composer install --no-dev


FROM php:7.4-cli-bullseye
RUN apt-get update && apt-get install --no-install-recommends -y \
    libzip-dev
RUN docker-php-ext-install sockets zip

USER www-data
WORKDIR /var/www/html
COPY --chown=www-data:www-data ./ /var/www/html
COPY --from=builder --chown=www-data:www-data /app/vendor /var/www/html/vendor
CMD ["bin/console", "twitch"]