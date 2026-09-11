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

# Clear any cached configs so environment variables take immediate effect
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Initialize database schema from database/botsv3.sql using PHP
echo "==> Initializing database schema..."
php database/import_schema.php || true

# Run database migrations for any incremental updates
echo "==> Running incremental migrations..."
php artisan migrate --force || true

# Re-ensure permissions after cache/migrations
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache
php artisan storage:link --force || true

# Start PHP-FPM in background
echo "==> Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "==> Starting Nginx on port ${TARGET_PORT}..."
exec nginx -g "daemon off;"
