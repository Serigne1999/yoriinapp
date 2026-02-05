<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\System;
use Illuminate\Support\Facades\DB;

echo "=== TEST SYSTEM::getProperty ===\n\n";

// Test direct en base
echo "1. Valeur directe en base de données:\n";
$system = DB::table('system')->first();
echo "   multiservices_version = " . ($system->multiservices_version ?? 'NULL') . "\n";

// Test via System::getProperty
echo "\n2. Via System::getProperty():\n";
$value = System::getProperty('multiservices_version');
echo "   Résultat = " . ($value ?? 'NULL') . "\n";

if (empty($value)) {
    echo "\n❌ PROBLÈME: System::getProperty retourne NULL!\n";
    echo "Il faut vider le cache de l'application.\n";
    
    // Forcer le refresh du cache
    echo "\nForçage du refresh...\n";
    \Illuminate\Support\Facades\Cache::flush();
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    
    // Retest
    $value = System::getProperty('multiservices_version');
    echo "Après cache clear: " . ($value ?? 'NULL') . "\n";
}
