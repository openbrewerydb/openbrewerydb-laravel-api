########################
# Base Image
########################
FROM serversideup/php:8.5-fpm-nginx-alpine-v4.5.3@sha256:c6dc9226395874b088c49f3cbdb0b84b55857370f5feb1ac5cd0ffb725fa0016 AS base

LABEL org.opencontainers.image.title="Open Brewery DB API" \
      org.opencontainers.image.description="Laravel implementation of the Open Brewery DB API" \
      org.opencontainers.image.authors="Chris Mears (@chrisjm), Alex Justesen (@alexjustesen)" \
      org.opencontainers.image.source="https://github.com/openbrewerydb/openbrewerydb-laravel-api"

ENV PHP_OPCACHE_ENABLE="1" \
    SHOW_WELCOME_MESSAGE="false"

# Code is immutable inside the image, so skip OPcache timestamp checks
ENV PHP_OPCACHE_INTERNED_STRINGS_BUFFER="16" \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

# Keep warm PHP-FPM workers ready for traffic and recycle them periodically
ENV PHP_FPM_PM_CONTROL="dynamic" \
    PHP_FPM_PM_MAX_REQUESTS="500"

# Limit request sizes and execution time, the API only serves reads and small MCP payloads
ENV NGINX_CLIENT_MAX_BODY_SIZE="1M" \
    PHP_MAX_EXECUTION_TIME="30" \
    PHP_POST_MAX_SIZE="1M" \
    PHP_UPLOAD_MAX_FILE_SIZE="1M"

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
