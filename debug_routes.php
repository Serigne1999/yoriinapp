<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNOSTIC COMPLET DES ROUTES ===\n\n";

// 1. Vérifier que le ServiceProvider est chargé
echo "1. Providers chargés:\n";
$providers = app()->getLoadedProviders();
if (isset($providers['Modules\Multiservices\Providers\MultiservicesServiceProvider'])) {
    echo "   ✓ MultiservicesServiceProvider est chargé\n";
} else {
    echo "   ✗ MultiservicesServiceProvider N'EST PAS chargé\n";
}

// 2. Lister TOUTES les routes
echo "\n2. Routes contenant 'multiservices':\n";
$allRoutes = app('router')->getRoutes();
$count = 0;
foreach ($allRoutes as $route) {
    if (str_contains($route->uri(), 'multiservices')) {
        $count++;
        echo "   - [{$route->methods()[0]}] {$route->uri()} -> {$route->getActionName()}\n";
    }
}
echo "   Total: {$count} routes\n";

// 3. Tester si la route index existe
echo "\n3. Test route 'multiservices':\n";
try {
    $route = app('router')->getRoutes()->getByName('multiservices.index');
    if ($route) {
        echo "   ✓ Route 'multiservices.index' existe\n";
        echo "   URI: {$route->uri()}\n";
        echo "   Action: {$route->getActionName()}\n";
    } else {
        echo "   ✗ Route 'multiservices.index' N'EXISTE PAS\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur: {$e->getMessage()}\n";
}

// 4. Test route commissions
echo "\n4. Test route 'multiservices/commissions':\n";
try {
    $route = app('router')->getRoutes()->getByName('multiservices.commissions.index');
    if ($route) {
        echo "   ✓ Route existe\n";
        echo "   URI: {$route->uri()}\n";
    } else {
        echo "   ✗ Route N'EXISTE PAS\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur: {$e->getMessage()}\n";
}
