########################
# Base Image
########################
FROM serversideup/php:8.4-fpm-nginx-alpine-v3.5.2 AS base

LABEL org.opencontainers.image.title="Open Brewery DB API" \
      org.opencontainers.image.description="Laravel implementation of the Open Brewery DB API" \
      org.opencontainers.image.authors="Chris Mears (@chrisjm), Alex Justesen (@alexjustesen)" \
      org.opencontainers.image.source="https://github.com/alexjustesen/obdb-api"

ARG WWWGROUP

ENV PHP_MAX_EXECUTION_TIME="60" \
    PHP_OPCACHE_ENABLE="1" \
    PHP_POST_MAX_SIZE="8M" \
    PHP_UPLOAD_MAX_FILESIZE="8M" \
    SHOW_WELCOME_MESSAGE="false"

# Switch to the root user so we can do root things
USER root

# Install the additional packages
RUN install-php-extensions sqlite3 \
    && rm -rf /var/cache/apk/*

# Set the working directory
WORKDIR /var/www/html

# Copy the application files
COPY --chown=www-data:www-data . /var/www/html

# Install the composer dependencies
RUN composer install --no-dev --no-interaction --no-progress --no-suggest --optimize-autoloader --prefer-dist

########################
# Production Image
########################
FROM base AS production

# Drop back to the www-data user
USER www-data

# Create the SQLite database, migrate the tables, and seed the data
RUN php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');" \
    && php artisan migrate:fresh --force \
    && php artisan app:import-breweries
