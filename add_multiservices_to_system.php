<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== AJOUT DE multiservices_version À LA TABLE SYSTEM ===\n\n";

// Vérifier si la colonne existe déjà
if (Schema::hasColumn('system', 'multiservices_version')) {
    echo "✓ La colonne 'multiservices_version' existe déjà\n";
} else {
    echo "Ajout de la colonne 'multiservices_version'...\n";
    
    // Ajouter à la fin de la table sans spécifier AFTER
    DB::statement("ALTER TABLE system ADD COLUMN multiservices_version VARCHAR(255) NULL DEFAULT '1.0'");
    
    echo "✓ Colonne ajoutée avec succès !\n";
}

// Vérifier
$system = DB::table('system')->first();
echo "\nValeur actuelle: " . ($system->multiservices_version ?? 'NULL') . "\n";

echo "\n✅ MODULE MULTISERVICES MAINTENANT INSTALLÉ !\n";
echo "\n🎯 Prochaines étapes:\n";
echo "1. Clear le cache: php artisan cache:clear\n";
echo "2. Testez: php test_superadmin_package.php\n";
echo "3. Allez dans Superadmin > Packages > Modifier un package\n";
echo "4. Le module 'Module Multiservices' devrait apparaître !\n";
echo "5. Déconnectez-vous et reconnectez-vous\n";
echo "6. Le menu Multiservices devrait ENFIN apparaître ! 🎉\n";
