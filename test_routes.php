<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST CHARGEMENT DES ROUTES ===\n\n";

// Charger manuellement les routes
$routeFile = __DIR__ . '/Modules/Multiservices/Routes/web.php';

if (file_exists($routeFile)) {
    echo "1. Fichier web.php existe: ✓\n";
    echo "   Chemin: {$routeFile}\n";
    
    // Inclure le fichier directement
    echo "\n2. Chargement manuel des routes...\n";
    require $routeFile;
    
    // Vérifier les routes
    echo "\n3. Routes enregistrées:\n";
    $routes = app('router')->getRoutes();
    $multiservices_routes = [];
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'multiservices')) {
            $multiservices_routes[] = $route->uri();
        }
    }
    
    if (count($multiservices_routes) > 0) {
        echo "   ✓ " . count($multiservices_routes) . " routes trouvées:\n";
        foreach ($multiservices_routes as $uri) {
            echo "     - {$uri}\n";
        }
    } else {
        echo "   ✗ Aucune route multiservices trouvée\n";
    }
} else {
    echo "✗ Fichier web.php n'existe pas!\n";
}
