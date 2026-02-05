<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== AJOUT MODULE MULTISERVICES AU PACKAGE #13 ===\n\n";

// Vérifier si le module existe déjà
$existing = DB::table('package_modules')
    ->where('package_id', 13)
    ->where('module_key', 'multiservices')
    ->first();

if ($existing) {
    echo "✓ Le module existe déjà (ID: {$existing->id})\n";
    
    if (!$existing->is_enabled) {
        DB::table('package_modules')
            ->where('id', $existing->id)
            ->update(['is_enabled' => 1]);
        echo "✓ Module activé\n";
    }
} else {
    echo "Création du module dans package_modules...\n";
    
    DB::table('package_modules')->insert([
        'package_id' => 13,
        'module_key' => 'multiservices',
        'is_enabled' => 1,
        'transaction_limit' => null,
        'features' => null,
        'starts_at' => null,
        'expires_at' => null,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Module créé et activé avec succès !\n";
}

echo "\n🎯 Maintenant testez:\n";
echo "   1. Clear le cache: php artisan cache:clear\n";
echo "   2. Connectez-vous avec le compte saliou\n";
echo "   3. Le menu Multiservices devrait apparaître !\n";
