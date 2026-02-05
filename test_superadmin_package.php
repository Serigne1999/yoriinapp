<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Utils\ModuleUtil;

echo "=== TEST SUPERADMIN_PACKAGE ===\n\n";

$moduleUtil = new ModuleUtil();
$permissions = $moduleUtil->getModuleData('superadmin_package');

echo "Modules détectés:\n";
foreach ($permissions as $module_name => $module_perms) {
    echo "\n[$module_name]\n";
    foreach ($module_perms as $perm) {
        echo "  - {$perm['name']}: {$perm['label']}\n";
    }
}

echo "\n=== VÉRIFICATION MULTISERVICES ===\n";
if (isset($permissions['Multiservices'])) {
    echo "✓ Module Multiservices détecté!\n";
} else {
    echo "✗ Module Multiservices NON détecté\n";
    
    echo "\nVérification DataController:\n";
    $class = 'Modules\Multiservices\Http\Controllers\DataController';
    if (class_exists($class)) {
        echo "  ✓ Classe existe\n";
        $controller = new $class();
        if (method_exists($controller, 'superadmin_package')) {
            echo "  ✓ Méthode superadmin_package existe\n";
            $result = $controller->superadmin_package();
            echo "  Résultat: " . print_r($result, true) . "\n";
        } else {
            echo "  ✗ Méthode superadmin_package N'EXISTE PAS\n";
        }
    } else {
        echo "  ✗ Classe N'EXISTE PAS\n";
    }
}
