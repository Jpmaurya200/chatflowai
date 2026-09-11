#!/bin/sh

# Set port from Railway environment (default to 80 if not provided)
TARGET_PORT="${PORT:-80}"
echo "==> Configuring Nginx to listen on port ${TARGET_PORT}..."
sed -i "s/listen 80;/listen ${TARGET_PORT};/g" /etc/nginx/http.d/default.conf
sed -i "s/listen \[::\]:80;/listen \[::\]:${TARGET_PORT};/g" /etc/nginx/http.d/default.conf

# Ensure Alpine Nginx runtime directories exist
mkdir -p /run/nginx /var/log/nginx /var/lib/nginx/tmp
chown -R www-data:www-data /run/nginx /var/log/nginx /var/lib/nginx

# Laravel runtime bootstrap
echo "==> Bootstrapping Laravel application..."
php artisan package:discover --ansi || true
php artisan storage:link --force || true
php artisan config:clear || true
php artisan cache:clear || true

# Run database migrations
echo "==> Running database migrations..."
php artisan migrate --force || true

# Cache configurations for production performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Ensure proper storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start PHP-FPM in background
echo "==> Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "==> Starting Nginx on port ${TARGET_PORT}..."
exec nginx -g "daemon off;"
