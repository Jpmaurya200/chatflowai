<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "==> [DB Check] Checking database connection...\n";

try {
    $pdo = DB::connection()->getPdo();
    echo "==> [DB Check] Connected successfully!\n";

    if (!Schema::hasTable('bot_flows') || !Schema::hasTable('configurations')) {
        echo "==> [DB Check] Base tables missing. Starting import from database/botsv3.sql...\n";
        $sqlPath = __DIR__ . '/botsv3.sql';
        
        if (!file_exists($sqlPath)) {
            throw new Exception("File not found: " . $sqlPath);
        }

        $sql = file_get_contents($sqlPath);
        DB::unprepared($sql);
        echo "==> [DB Check] SUCCESS: Database imported successfully!\n";
    } else {
        echo "==> [DB Check] Base tables already exist. Skipping import.\n";
    }
} catch (\Throwable $e) {
    echo "==> [DB Check Error]: " . $e->getMessage() . "\n";
    // If DB::unprepared had an issue with a single statement, let's try statement by statement
    try {
        $lines = file(__DIR__ . '/botsv3.sql');
        $templine = '';
        $count = 0;
        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || trim($line) == '' || substr($line, 0, 2) == '/*') {
                continue;
            }
            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';') {
                DB::unprepared($templine);
                $templine = '';
                $count++;
            }
        }
        echo "==> [DB Check] Imported $count SQL statements successfully!\n";
    } catch (\Throwable $ex) {
        echo "==> [DB Check Statement Error]: " . $ex->getMessage() . "\n";
    }
}
