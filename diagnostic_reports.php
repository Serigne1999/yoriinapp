<?php
/**
 * DIAGNOSTIC ERREUR 500 - RAPPORTS COMPTES
 * 
 * Instructions:
 * 1. Copier ce fichier à la racine de votre projet UltimatePOS
 * 2. L'exécuter via: php diagnostic_reports.php
 * 3. Partager le résultat
 */

echo "=== DIAGNOSTIC ERREUR 500 - RAPPORTS COMPTES ===\n\n";

// 1. Vérifier le fichier de routes
$routesFile = __DIR__ . '/Modules/Multiservices/Routes/web.php';
echo "1. ROUTES FILE\n";
echo "   Chemin: $routesFile\n";
echo "   Existe: " . (file_exists($routesFile) ? 'OUI' : 'NON') . "\n";

if (file_exists($routesFile)) {
    echo "   Contenu:\n";
    echo "   " . str_repeat('-', 70) . "\n";
    $content = file_get_contents($routesFile);
    echo $content;
    echo "\n   " . str_repeat('-', 70) . "\n\n";
    
    // Vérifier si la route reports existe
    if (strpos($content, 'reports') !== false) {
        echo "   ✓ Route 'reports' trouvée\n\n";
    } else {
        echo "   ✗ Route 'reports' NON trouvée\n\n";
    }
}

// 2. Vérifier le contrôleur
$controllerFile = __DIR__ . '/Modules/Multiservices/Http/Controllers/OperatorAccountController.php';
echo "2. CONTROLLER FILE\n";
echo "   Chemin: $controllerFile\n";
echo "   Existe: " . (file_exists($controllerFile) ? 'OUI' : 'NON') . "\n\n";

if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    // Vérifier les méthodes
    echo "   Méthodes détectées:\n";
    if (strpos($content, 'public function index') !== false) {
        echo "   ✓ index()\n";
    }
    if (strpos($content, 'public function create') !== false) {
        echo "   ✓ create()\n";
    }
    if (strpos($content, 'public function store') !== false) {
        echo "   ✓ store()\n";
    }
    if (strpos($content, 'public function reports') !== false) {
        echo "   ✓ reports() [MÉTHODE SÉPARÉE]\n";
    } else {
        echo "   ✗ reports() [MÉTHODE SÉPARÉE NON TROUVÉE]\n";
        echo "   → Les rapports sont probablement dans index()\n";
    }
    echo "\n";
}

// 3. Vérifier la vue reports
$reportsView = __DIR__ . '/Modules/Multiservices/Resources/views/accounts/reports.blade.php';
echo "3. REPORTS VIEW\n";
echo "   Chemin: $reportsView\n";
echo "   Existe: " . (file_exists($reportsView) ? 'OUI' : 'NON') . "\n\n";

// 4. Extraire l'URL du formulaire
if (file_exists($reportsView)) {
    $content = file_get_contents($reportsView);
    
    // Rechercher la balise form
    if (preg_match('/<form[^>]*action=["\']([^"\']+)["\']/', $content, $matches)) {
        echo "   URL du formulaire: " . $matches[1] . "\n\n";
    }
    
    // Rechercher route()
    if (preg_match('/route\(["\']([^"\']+)["\']/', $content, $matches)) {
        echo "   Route utilisée: " . $matches[1] . "\n\n";
    }
}

// 5. Recommandations
echo "=== RECOMMANDATIONS ===\n\n";
echo "Si la route 'reports' n'existe PAS dans web.php:\n";
echo "→ Le formulaire doit pointer vers 'multiservices.accounts.index'\n";
echo "→ Et la méthode index() gère déjà les rapports\n\n";

echo "Si la route 'reports' existe:\n";
echo "→ Créer une méthode reports() dans le contrôleur\n";
echo "→ Ou rediriger vers index() avec ?tab=reports\n\n";

echo "=== FIN DIAGNOSTIC ===\n";
