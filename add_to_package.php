<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== AJOUT DE MULTISERVICES AUX PACKAGES ===\n\n";

// Liste des packages à mettre à jour
$package_ids = [1, 3, 5, 9, 13, 14]; // Tous les packages actifs qu'on a vu

foreach ($package_ids as $package_id) {
    $package = DB::table('packages')->where('id', $package_id)->first();
    
    if (!$package) {
        echo "Package ID {$package_id}: NON TROUVÉ\n";
        continue;
    }
    
    echo "Package ID {$package_id} ({$package->name}):\n";
    
    // Récupérer les permissions actuelles
    $current_perms = [];
    if (!empty($package->custom_permissions)) {
        $current_perms = json_decode($package->custom_permissions, true);
        if (!is_array($current_perms)) {
            $current_perms = [];
        }
    }
    
    // Ajouter multiservices si pas déjà présent
    if (!in_array('multiservices', $current_perms)) {
        $current_perms[] = 'multiservices';
        
        // Mettre à jour
        DB::table('packages')
            ->where('id', $package_id)
            ->update([
                'custom_permissions' => json_encode($current_perms)
            ]);
        
        echo "   ✓ 'multiservices' ajouté aux permissions\n";
    } else {
        echo "   ✓ 'multiservices' déjà présent\n";
    }
    
    echo "   Permissions actuelles: " . json_encode($current_perms) . "\n\n";
}

echo "=== TERMINÉ ===\n";
echo "Maintenant, rafraîchissez votre page et le menu devrait apparaître !\n";
