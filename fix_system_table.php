<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CORRECTION TABLE SYSTEM ===\n\n";

// 1. Supprimer la colonne si elle existe
if (Schema::hasColumn('system', 'multiservices_version')) {
    echo "1. Suppression de la colonne multiservices_version...\n";
    DB::statement("ALTER TABLE system DROP COLUMN multiservices_version");
    echo "   ✓ Colonne supprimée\n";
}

// 2. Vérifier si la ligne key/value existe
$exists = DB::table('system')->where('key', 'multiservices_version')->exists();

if ($exists) {
    echo "\n2. Ligne multiservices_version existe déjà\n";
} else {
    echo "\n2. Insertion de la ligne multiservices_version...\n";
    DB::table('system')->insert([
        'key' => 'multiservices_version',
        'value' => '1.0'
    ]);
    echo "   ✓ Ligne insérée\n";
}

// 3. Vérifier
echo "\n3. Vérification:\n";
$value = DB::table('system')->where('key', 'multiservices_version')->value('value');
echo "   multiservices_version = {$value}\n";

// 4. Test avec System::getProperty
use App\System;
$prop_value = System::getProperty('multiservices_version');
echo "   System::getProperty('multiservices_version') = " . ($prop_value ?? 'NULL') . "\n";

echo "\n✅ CORRECTION TERMINÉE !\n";
echo "\n🎯 Maintenant testez:\n";
echo "   php artisan cache:clear\n";
echo "   php test_superadmin_package.php\n";
