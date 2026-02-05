<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VÉRIFICATION SUBSCRIPTION & PACKAGE ===\n\n";

// 1. Vérifier les subscriptions actives
echo "1. Subscriptions actives:\n";
$subscriptions = DB::table('subscriptions')
    ->where('status', 'approved')
    ->get();

foreach ($subscriptions as $sub) {
    echo "   Business ID: {$sub->business_id}, Package ID: {$sub->package_id}\n";
}

// 2. Vérifier le contenu du package
echo "\n2. Contenu du Package ID 1:\n";
$package = DB::table('packages')->where('id', 1)->first();
echo "   Nom: {$package->name}\n";

// Vérifier custom_permissions
if (!empty($package->custom_permissions)) {
    echo "   custom_permissions: {$package->custom_permissions}\n";
    $perms = json_decode($package->custom_permissions, true);
    if ($perms && is_array($perms)) {
        echo "   Modules activés dans custom_permissions:\n";
        foreach ($perms as $perm) {
            echo "     - {$perm}\n";
        }
    }
} else {
    echo "   custom_permissions: VIDE\n";
}

// 3. Vérifier package_modules pour ce package
echo "\n3. Modules du Package ID 1:\n";
$modules = DB::table('package_modules')
    ->where('package_id', 1)
    ->get();

if ($modules->count() > 0) {
    foreach ($modules as $mod) {
        echo "   - {$mod->module_key} (enabled: {$mod->is_enabled})\n";
    }
} else {
    echo "   ✗ Aucun module trouvé\n";
}

echo "\n=== SOLUTION ===\n";
echo "Il faut ajouter 'multiservices' dans les custom_permissions du package\n";
echo "OU vérifier que le package_modules est bien configuré.\n";
