<?php

echo "==> [DB Init] Bootstrapping Laravel for database check...\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "==> [DB Init] Testing database connection...\n";
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "==> [DB Init] Connected successfully to database: " . config('database.connections.mysql.database') . " on " . config('database.connections.mysql.host') . "\n";

    if (!\Illuminate\Support\Facades\Schema::hasTable('configurations')) {
        echo "==> [DB Init] 'configurations' table missing. Importing database/botsv3.sql...\n";
        $sqlPath = __DIR__ . '/botsv3.sql';
        if (file_exists($sqlPath)) {
            // Read and execute in chunks if large, or unprepared statement
            $sql = file_get_contents($sqlPath);
            \Illuminate\Support\Facades\DB::unprepared($sql);
            echo "==> [DB Init] Initial database schema and data imported successfully!\n";
        } else {
            echo "==> [DB Init] Warning: botsv3.sql not found!\n";
        }
    } else {
        echo "==> [DB Init] Database is already initialized ('configurations' table exists).\n";
    }
} catch (\Throwable $e) {
    echo "==> [DB Init Error]: " . $e->getMessage() . "\n";
}
