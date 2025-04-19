#!/bin/bash

# Create coverage directory if it doesn't exist
mkdir -p coverage

# Run Pest with coverage
XDEBUG_MODE=coverage ./vendor/bin/sail artisan test --coverage --coverage-html coverage/html --coverage-clover coverage/clover.xml
