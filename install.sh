#!/bin/bash

# Install Composer dependencies
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Build Sail development environment image
./vendor/bin/sail build

# Start the development environment
./vendor/bin/sail up -d

# Run the install script
./vendor/bin/sail artisan app:install --force

echo "Development environment setup complete. Follow the steps in 'Importing Data' to refresh the dataset."
