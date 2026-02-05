<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ACTIVATION DU MODULE MULTISERVICES ===\n\n";

// Vérifier si le module existe déjà
$existing = DB::table('package_modules')->where('module_name', 'multiservices')->first();

if ($existing) {
    echo "⚠️  Le module existe déjà dans package_modules\n";
    echo "ID: {$existing->id}, Activé: " . ($existing->is_enabled ? 'OUI' : 'NON') . "\n\n";
    
    if ($existing->is_enabled) {
        echo "✅ Le module est déjà activé !\n";
        exit;
    }
    
    echo "Activation du module...\n";
    DB::table('package_modules')
        ->where('module_name', 'multiservices')
        ->update(['is_enabled' => 1]);
    
    echo "✅ Module activé avec succès !\n";
} else {
    echo "Création de l'entrée dans package_modules...\n";
    
    DB::table('package_modules')->insert([
        'module_name' => 'multiservices',
        'is_enabled' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Module créé et activé avec succès !\n";
}

echo "\n🎯 Prochaine étape: Clear le cache et tester le module\n";
