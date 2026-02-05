<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== AJOUT DE MULTISERVICES AUX ENABLED_MODULES ===\n\n";

$businesses = DB::table('business')->get();

foreach ($businesses as $business) {
    echo "Business ID {$business->id} ({$business->name}):\n";
    
    // Récupérer les modules activés actuels
    $enabled_modules = !empty($business->enabled_modules) ? json_decode($business->enabled_modules, true) : [];
    
    if (!is_array($enabled_modules)) {
        $enabled_modules = [];
    }
    
    echo "  Modules actuels: " . implode(', ', $enabled_modules) . "\n";
    
    // Ajouter multiservices si pas déjà présent
    if (!in_array('multiservices', $enabled_modules)) {
        $enabled_modules[] = 'multiservices';
        
        DB::table('business')
            ->where('id', $business->id)
            ->update([
                'enabled_modules' => json_encode($enabled_modules)
            ]);
        
        echo "  ✓ 'multiservices' ajouté\n";
    } else {
        echo "  ✓ 'multiservices' déjà présent\n";
    }
    
    echo "  Nouveaux modules: " . implode(', ', $enabled_modules) . "\n\n";
}

echo "=== TERMINÉ ===\n";
echo "\n🎯 Maintenant:\n";
echo "1. Déconnectez-vous de YoriinApp\n";
echo "2. Reconnectez-vous\n";
echo "3. Le menu Multiservices devrait ENFIN apparaître ! 🎉\n";
