FROM php:8-cli-bullseye

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app