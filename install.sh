#!/bin/bash

set -Eeuo pipefail

trap 'printf "Development environment setup failed at line %s.\n" "$LINENO" >&2' ERR

readonly PROJECT_DIRECTORY="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIRECTORY"

if ! command -v docker >/dev/null 2>&1; then
    printf "Docker is required to set up the development environment.\n" >&2
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    printf "Docker is not running. Start Docker and try again.\n" >&2
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    printf "Docker Compose is required to set up the development environment.\n" >&2
    exit 1
fi

existing_install=false
if [[ -f .env || -f database/database.sqlite ]]; then
    existing_install=true
fi

# Install Composer dependencies
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$PROJECT_DIRECTORY:/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Build and start the development environment
./vendor/bin/sail up -d --build

# Protect existing local data from an unattended migrate:fresh
if [[ "$existing_install" == true ]]; then
    ./vendor/bin/sail artisan app:install
else
    ./vendor/bin/sail artisan app:install --force
fi

# Generate local AI agent guidelines, skills, and MCP configuration
./vendor/bin/sail artisan boost:install

printf "Development environment setup complete. Follow the steps in 'Importing Data' to refresh the dataset.\n"
