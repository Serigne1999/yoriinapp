<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST CONTROLLERS ===\n\n";

$controllers = [
    'Modules\Multiservices\Http\Controllers\MultiservicesController',
    'Modules\Multiservices\Http\Controllers\CommissionController',
    'Modules\Multiservices\Http\Controllers\ReportController',
];

foreach ($controllers as $controller) {
    echo "Controller: {$controller}\n";
    if (class_exists($controller)) {
        echo "  ✓ Classe existe\n";
        
        // Vérifier les méthodes
        $methods = ['index', 'create', 'store'];
        foreach ($methods as $method) {
            if (method_exists($controller, $method)) {
                echo "    ✓ Méthode {$method}() existe\n";
            } else {
                echo "    ✗ Méthode {$method}() N'EXISTE PAS\n";
            }
        }
    } else {
        echo "  ✗ Classe N'EXISTE PAS\n";
    }
    echo "\n";
}
