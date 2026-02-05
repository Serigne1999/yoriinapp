<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;

$business_id = 24;

echo "🔧 Configuration des permissions Multiservices pour business_id {$business_id}...\n\n";

// 1. Créer les permissions GLOBALES si elles n'existent pas
$permissions = [
    'multiservices.view',
    'multiservices.create',
    'multiservices.update',
    'multiservices.delete',
    'multiservices.report',
    'multiservices.settings',
];

foreach ($permissions as $perm) {
    $permission = Permission::firstOrCreate(['name' => $perm]);
    echo "✅ Permission '{$perm}' créée/existante\n";
}

// 2. Trouver le rôle Admin du business
$adminRole = Role::where('business_id', $business_id)
    ->where('name', 'like', 'Admin%')
    ->first();

if (!$adminRole) {
    echo "\n❌ Rôle Admin non trouvé pour business {$business_id}\n";
    echo "Rôles disponibles:\n";
    $roles = Role::where('business_id', $business_id)->get();
    foreach ($roles as $r) {
        echo "  - {$r->name}\n";
    }
    exit;
}

echo "\n📋 Rôle trouvé: {$adminRole->name}\n";

// 3. Assigner toutes les permissions au rôle
$adminRole->syncPermissions(array_merge(
    $adminRole->permissions->pluck('name')->toArray(),
    $permissions
));
echo "✅ Permissions assignées au rôle Admin\n";

// 4. Vérifier tous les utilisateurs du business
$users = User::where('business_id', $business_id)->get();

echo "\n👥 Utilisateurs du business:\n";
foreach ($users as $user) {
    echo "  - {$user->username} ({$user->first_name} {$user->last_name})\n";
    
    // Vérifier le rôle
    $userRoles = $user->roles->pluck('name')->toArray();
    echo "    Rôles: " . implode(', ', $userRoles) . "\n";
    
    // Rafraîchir les permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Vérifier la permission
    $user = $user->fresh();
    if ($user->can('multiservices.view')) {
        echo "    ✅ Peut voir Multiservices\n";
    } else {
        echo "    ❌ Ne peut pas voir Multiservices\n";
    }
}

echo "\n✅ Configuration terminée !\n";
echo "Cache des permissions vidé.\n";
echo "Reconnectez-vous pour voir le menu.\n";
