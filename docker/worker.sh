#!/bin/sh

echo "==> ChatFlowAI Background Worker Starting..."

# Wait for database readiness using PHP
echo "==> Checking for database readiness..."
retries=0
while [ $retries -lt 30 ]; do
    php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); exit(\Illuminate\Support\Facades\Schema::hasTable('jobs') ? 0 : 1);" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "==> Database ready! 'jobs' table detected."
        break
    fi
    echo "==> Waiting for database initialization... (attempt $retries/30)"
    sleep 4
    retries=$((retries + 1))
done

php artisan config:clear || true
php artisan cache:clear || true

echo "==> Starting Laravel Queue Worker..."
exec php artisan queue:work --tries=3 --timeout=90
