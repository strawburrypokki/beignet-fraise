FROM php:7.4-cli-bullseye

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install --no-install-recommends -y \
    git zip libzip-dev unzip curl

RUN docker-php-ext-install sockets zip

COPY ./ /app

USER 1000

WORKDIR /app

CMD ["bin/console", "twitch"]