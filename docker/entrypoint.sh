#!/bin/sh

# Set port from Railway environment (default to 80 if not provided)
TARGET_PORT="${PORT:-80}"
echo "==> Configuring Nginx to listen on port ${TARGET_PORT}..."
sed -i "s/listen 80;/listen ${TARGET_PORT};/g" /etc/nginx/http.d/default.conf
sed -i "s/listen \[::\]:80;/listen \[::\]:${TARGET_PORT};/g" /etc/nginx/http.d/default.conf

# Enable PHP errors in logs for diagnostics
mkdir -p /usr/local/etc/php/conf.d
cat <<EOF > /usr/local/etc/php/conf.d/docker-php-errors.ini
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
EOF

# Ensure runtime directories exist
mkdir -p /run/nginx /var/log/nginx /var/lib/nginx/tmp /var/lib/php/sessions /tmp
chmod 777 /tmp /var/lib/php/sessions
chown -R www-data:www-data /run/nginx /var/log/nginx /var/lib/nginx

# Ensure storage directories exist with full write permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Auto-initialize database schema if fresh
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "==> Testing database connection on ${DB_HOST}:${DB_PORT:-3306}..."
    TABLE_COUNT=$(mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "SHOW TABLES;" 2>&1 | wc -l)
    if [ -z "$TABLE_COUNT" ] || [ "$TABLE_COUNT" -le 1 ]; then
        echo "==> Fresh database detected! Importing schema and seed data from database/botsv3.sql..."
        mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < /var/www/html/database/botsv3.sql
        echo "==> Initial schema imported successfully!"
    else
        echo "==> Database already initialized (${TABLE_COUNT} tables found)."
    fi
fi

# Laravel runtime bootstrap
echo "==> Bootstrapping Laravel application..."
php artisan package:discover --ansi || true
php artisan storage:link --force || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Run database migrations for incremental updates
echo "==> Running database migrations..."
php artisan migrate --force || true

# Re-ensure permissions after cache/migrations
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Start PHP-FPM in background
echo "==> Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "==> Starting Nginx on port ${TARGET_PORT}..."
exec nginx -g "daemon off;"
