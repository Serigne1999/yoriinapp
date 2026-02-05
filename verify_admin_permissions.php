<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "=== VÉRIFICATION PERMISSIONS ADMIN ===\n\n";

// Trouver tous les rôles Admin
$admin_roles = Role::where('name', 'like', 'Admin#%')->get();

echo "Rôles Admin trouvés: " . $admin_roles->count() . "\n";
echo str_repeat("-", 80) . "\n";

foreach ($admin_roles as $role) {
    echo "\nRôle: {$role->name} (ID: {$role->id})\n";
    
    // Récupérer les permissions du rôle
    $permissions = $role->permissions;
    
    // Filtrer les permissions multiservices
    $multiservices_perms = $permissions->filter(function($perm) {
        return str_starts_with($perm->name, 'multiservices.');
    });
    
    if ($multiservices_perms->count() > 0) {
        echo "✓ Permissions Multiservices ({$multiservices_perms->count()}):\n";
        foreach ($multiservices_perms as $perm) {
            echo "  - {$perm->name}\n";
        }
    } else {
        echo "✗ AUCUNE permission Multiservices trouvée!\n";
        echo "  Assignation en cours...\n";
        
        // Assigner les permissions
        $ms_permissions = Permission::where('name', 'like', 'multiservices.%')->get();
        $role->givePermissionTo($ms_permissions);
        
        echo "  ✓ {$ms_permissions->count()} permissions assignées!\n";
    }
}

echo "\n" . str_repeat("-", 80) . "\n";
echo "\n🎯 PROCHAINE ÉTAPE:\n";
echo "   1. Déconnectez-vous de YoriinApp\n";
echo "   2. Reconnectez-vous\n";
echo "   3. Le menu Multiservices devrait apparaître!\n";
