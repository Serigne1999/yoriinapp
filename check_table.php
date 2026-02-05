<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== STRUCTURE DE LA TABLE package_modules ===\n\n";

// Vérifier si la table existe
if (!Schema::hasTable('package_modules')) {
    echo "❌ La table package_modules n'existe pas!\n";
    exit;
}

// Récupérer les colonnes
$columns = DB::select("SHOW COLUMNS FROM package_modules");

echo "Colonnes de la table:\n";
foreach ($columns as $column) {
    echo "  - {$column->Field} ({$column->Type})\n";
}

echo "\n--- Contenu actuel de la table ---\n";
$modules = DB::table('package_modules')->get();

if ($modules->count() == 0) {
    echo "Table vide\n";
} else {
    foreach ($modules as $module) {
        echo "\nID: {$module->id}\n";
        foreach ($module as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    }
}

