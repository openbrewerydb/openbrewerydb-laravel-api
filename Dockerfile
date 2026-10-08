########################
# Base Image
########################
FROM serversideup/php:8.4-fpm-nginx-alpine-v4.5.1 AS base

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

# Drop back to the www-data user
USER www-data

# Set the working directory
WORKDIR /var/www/html

# Install the composer dependencies in their own layer so they are cached between code changes
COPY --chown=www-data:www-data composer.json composer.lock /var/www/html/

RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader --no-cache

# Copy the application files
COPY --chown=www-data:www-data . /var/www/html

# Generate the optimized autoloader and run the composer scripts
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

########################
# Production Image
########################
FROM base AS production

# Create the SQLite database, migrate the tables, and seed the data
RUN php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');" \
    && php artisan db:tune-sqlite-reads
