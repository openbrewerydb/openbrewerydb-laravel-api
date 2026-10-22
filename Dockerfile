########################
# Base Image
########################
FROM serversideup/php:8.4-fpm-nginx-alpine-v4.5.3@sha256:925259fd12545f17d9bae68dd51ca9d5d8c997efd2ea6a66830ca4a11e6afdf5 AS base

LABEL org.opencontainers.image.title="Open Brewery DB API" \
      org.opencontainers.image.description="Laravel implementation of the Open Brewery DB API" \
      org.opencontainers.image.authors="Chris Mears (@chrisjm), Alex Justesen (@alexjustesen)" \
      org.opencontainers.image.source="https://github.com/openbrewerydb/openbrewerydb-laravel-api"

ENV PHP_OPCACHE_ENABLE="1" \
    SHOW_WELCOME_MESSAGE="false"

# Switch to the root user so we can do root things
USER root

COPY ./src/etc /etc

# Install the additional packages
RUN install-php-extensions excimer \
    && rm -rf /var/cache/apk/*

# Set the working directory
WORKDIR /var/www/html

# Install the composer dependencies in their own layer so they are cached between code changes
COPY composer.json composer.lock /var/www/html/

RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader --no-cache

# Copy the application files, owned by root so the runtime user cannot modify the code
COPY . /var/www/html

# Generate the optimized autoloader, run the composer scripts, link the public storage
# directory, and hand only the directories written to at runtime over to the www-data user
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan storage:link \
    && mkdir -p public/docs .scribe \
    && chown root:root /var/www/html \
    && chown -R www-data:www-data storage bootstrap/cache database public/docs .scribe

# Drop back to the www-data user
USER www-data

########################
# Production Image
########################
FROM base AS production

# Create the SQLite database, migrate the tables, and seed the data
RUN php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');" \
    && php artisan db:tune-sqlite-reads
