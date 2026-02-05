<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== STRUCTURE TABLE SYSTEM ===\n\n";

$columns = DB::select("SHOW COLUMNS FROM system");
foreach ($columns as $col) {
    echo "{$col->Field} ({$col->Type})\n";
}

echo "\n=== CONTENU TABLE SYSTEM ===\n\n";
$rows = DB::table('system')->get();
foreach ($rows as $row) {
    echo "key: {$row->key} = {$row->value}\n";
}
