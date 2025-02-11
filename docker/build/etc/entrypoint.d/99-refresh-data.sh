#!/bin/sh
script_name="refreash-data"

php "$APP_BASE_DIR/artisan" app:import-breweries
