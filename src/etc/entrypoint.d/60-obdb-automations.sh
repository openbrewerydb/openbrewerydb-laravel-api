############################################################################
# artisan app:refresh-search-indexes
############################################################################
if [ "${AUTORUN_OBDB_REFRESH_SEARCH_INDEXES:=false}" = "true" ]; then
    echo "🔍 Refreshing search indexes..."
    php "$APP_BASE_DIR/artisan" app:refresh-search-indexes
fi
