#!/bin/sh
set -e

# Discover packages at runtime when DB is accessible
php artisan package:discover --ansi || true

# Clear and cache configurations
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations
php artisan migrate --force || true

# Start PHP-FPM in the background
php-fpm -D

# Start Nginx in foreground
exec nginx -g "daemon off;"
