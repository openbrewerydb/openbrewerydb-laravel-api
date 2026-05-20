############################################################################
# artisan app:refresh-search-indexes
############################################################################
if [ "${AUTORUN_OBDB_IMPORT_BREWERIES:=false}" = "true" ]; then
    echo "🔍 Importing latest brewery data..."
    php "$APP_BASE_DIR/artisan" app:import-breweries
fi

############################################################################
# artisan app:refresh-search-indexes
############################################################################
if [ "${AUTORUN_OBDB_REFRESH_SEARCH_INDEXES:=false}" = "true" ]; then
    echo "🔍 Refreshing search indexes..."
    php "$APP_BASE_DIR/artisan" app:refresh-search-indexes
fi

############################################################################
# artisan scribe:generate
############################################################################
if [ "${AUTORUN_OBDB_SCRIBE_GENERATE:=false}" = "true" ]; then
    echo "🔍 Generating Scribe documentation..."
    php "$APP_BASE_DIR/artisan" scribe:generate
fi