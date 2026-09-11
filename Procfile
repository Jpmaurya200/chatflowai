web: php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && /assets/scripts/pre-start.sh
worker: php artisan queue:work --tries=3
