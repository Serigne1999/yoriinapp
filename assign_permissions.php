<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== ASSIGNATION DES PERMISSIONS MULTISERVICES ===\n\n";

// Trouver le rôle Admin
$role = Role::where('name', 'like', 'Admin#%')->first();

if (!$role) {
    echo "❌ Rôle Admin non trouvé!\n";
    echo "Rôles disponibles:\n";
    foreach (Role::all() as $r) {
        echo "  - {$r->name}\n";
    }
    exit;
}

echo "✓ Rôle trouvé: {$role->name}\n";

// Récupérer les permissions multiservices
$permissions = Permission::where('name', 'like', 'multiservices.%')->get();
echo "✓ Permissions trouvées: {$permissions->count()}\n\n";

if ($permissions->count() == 0) {
    echo "❌ Aucune permission multiservices trouvée!\n";
    echo "Exécutez d'abord: php artisan db:seed --class=Modules\\\\Multiservices\\\\Database\\\\Seeders\\\\MultiservicesDatabaseSeeder\n";
    exit;
}

echo "Liste des permissions:\n";
foreach ($permissions as $perm) {
    echo "  - {$perm->name}\n";
}

// Assigner au rôle
echo "\nAssignation en cours...\n";
$role->givePermissionTo($permissions);

echo "\n✅ SUCCÈS ! Permissions assignées au rôle {$role->name}\n";
echo "\n🎯 Prochaine étape: Activer le module dans package_modules\n";
