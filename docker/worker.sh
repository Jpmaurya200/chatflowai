#!/bin/sh

echo "==> ChatFlowAI Background Worker Starting..."

# Wait for database connection and tables
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "==> Checking for database readiness..."
    retries=0
    while [ $retries -lt 30 ]; do
        TABLE_EXISTS=$(mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "SHOW TABLES LIKE 'jobs';" 2>/dev/null | wc -l)
        if [ "$TABLE_EXISTS" -ge 2 ]; then
            echo "==> 'jobs' table detected. Database is ready!"
            break
        fi
        echo "==> Waiting for database initialization... (attempt $retries/30)"
        sleep 3
        retries=$((retries + 1))
    done
fi

php artisan config:clear || true
php artisan cache:clear || true

echo "==> Starting Laravel Queue Worker..."
exec php artisan queue:work --tries=3 --timeout=90
