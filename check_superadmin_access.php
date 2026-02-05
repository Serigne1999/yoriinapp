<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNOSTIC ACCÈS SUPERADMIN ===\n\n";

// 1. Vérifier si le module Superadmin est installé
echo "1. Module Superadmin installé ?\n";
$superadmin_version = \App\System::getProperty('superadmin_version');
if ($superadmin_version) {
    echo "   ✓ Version: {$superadmin_version}\n";
} else {
    echo "   ✗ Module Superadmin NON installé\n";
}

// 2. Trouver les users superadmin
echo "\n2. Utilisateurs Superadmin:\n";
$superadmins = \App\User::whereHas('roles', function($q) {
    $q->where('name', 'superadmin');
})->get();

if ($superadmins->count() > 0) {
    foreach ($superadmins as $user) {
        echo "   - ID: {$user->id}, Username: {$user->username}, Email: {$user->email}\n";
    }
} else {
    echo "   ✗ Aucun user avec le rôle 'superadmin'\n";
}

// 3. Vérifier les routes superadmin
echo "\n3. Routes Superadmin:\n";
$routes = app('router')->getRoutes();
$superadmin_routes = [];
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'superadmin')) {
        $superadmin_routes[] = $route->uri();
    }
}

if (count($superadmin_routes) > 0) {
    echo "   ✓ " . count($superadmin_routes) . " routes trouvées\n";
    echo "   Exemples:\n";
    foreach (array_slice($superadmin_routes, 0, 5) as $uri) {
        echo "     - {$uri}\n";
    }
} else {
    echo "   ✗ Aucune route superadmin trouvée\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
