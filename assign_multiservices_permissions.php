<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$business_id = 2; // CHANGEZ ICI avec l'ID du business

echo "🔍 Attribution des permissions Multiservices...\n\n";

// Permissions à créer
$permissions = [
    'multiservices.view',
    'multiservices.create',
    'multiservices.update',
    'multiservices.delete',
    'multiservices.report',
    'multiservices.settings',
];

foreach ($permissions as $perm) {
    $permission = Permission::firstOrCreate([
        'name' => $perm,
        'business_id' => $business_id
    ]);
    echo "✅ Permission '{$perm}' créée\n";
}

// Assigner au rôle Admin
$adminRole = Role::where('business_id', $business_id)
    ->where('name', 'Admin#' . $business_id)
    ->first();

if ($adminRole) {
    $adminRole->syncPermissions(array_merge(
        $adminRole->permissions->pluck('name')->toArray(),
        $permissions
    ));
    echo "\n✅ Permissions assignées au rôle Admin #{$business_id}\n";
} else {
    echo "\n❌ Rôle Admin non trouvé pour business_id {$business_id}\n";
}

echo "\n✅ Terminé !\n";
