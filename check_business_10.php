<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

echo "=== DIAGNOSTIC COMPLET BUSINESS #10 (Yoriin App) ===\n\n";

// 1. Vérifier le package
echo "1. Package & Subscription:\n";
$subscription = DB::table('subscriptions')
    ->where('business_id', 10)
    ->where('status', 'approved')
    ->orderBy('id', 'desc')
    ->first();

if ($subscription) {
    $package = DB::table('packages')->where('id', $subscription->package_id)->first();
    echo "   ✓ Package: {$package->name} (ID: {$package->id})\n";
    
    $perms = json_decode($package->custom_permissions, true);
    if ($perms && in_array('multiservices', $perms)) {
        echo "   ✓ 'multiservices' est dans custom_permissions\n";
    } else {
        echo "   ✗ 'multiservices' N'EST PAS dans custom_permissions\n";
    }
}

// 2. Vérifier package_modules
echo "\n2. Package Modules:\n";
$pkg_module = DB::table('package_modules')
    ->where('package_id', $subscription->package_id)
    ->where('module_key', 'multiservices')
    ->first();

if ($pkg_module) {
    echo "   ✓ Module multiservices trouvé (enabled: {$pkg_module->is_enabled})\n";
} else {
    echo "   ✗ Module multiservices NON TROUVÉ dans package_modules\n";
}

// 3. Vérifier les permissions du rôle
echo "\n3. Permissions du rôle Admin#10:\n";
$role = Role::where('name', 'Admin#10')->first();

if ($role) {
    $ms_perms = $role->permissions->filter(function($p) {
        return str_starts_with($p->name, 'multiservices.');
    });
    
    if ($ms_perms->count() > 0) {
        echo "   ✓ {$ms_perms->count()} permissions multiservices assignées\n";
        foreach ($ms_perms as $p) {
            echo "     - {$p->name}\n";
        }
    } else {
        echo "   ✗ AUCUNE permission multiservices\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "4. RÉSUMÉ:\n";

$all_ok = true;
if (!$perms || !in_array('multiservices', $perms)) {
    echo "   ✗ Il manque 'multiservices' dans le package\n";
    $all_ok = false;
}
if (!$pkg_module || !$pkg_module->is_enabled) {
    echo "   ✗ Module non activé dans package_modules\n";
    $all_ok = false;
}
if (!$ms_perms || $ms_perms->count() == 0) {
    echo "   ✗ Permissions non assignées au rôle\n";
    $all_ok = false;
}

if ($all_ok) {
    echo "   ✅ TOUT EST CONFIGURÉ CORRECTEMENT !\n";
    echo "\n   🎯 Connectez-vous avec: saliou@salioumbengue99@outlook.com\n";
    echo "   Le menu Multiservices devrait apparaître.\n";
} else {
    echo "   ⚠️  Il y a des problèmes de configuration (voir ci-dessus)\n";
}
