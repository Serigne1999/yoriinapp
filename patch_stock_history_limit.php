<?php
/**
 * Patch pour limiter l'historique de stock à 500 dernières entrées
 * Pour éviter le crash JavaScript sur les locations avec beaucoup de données
 */

$controller_path = __DIR__ . '/app/Http/Controllers/ReportController.php';

if (!file_exists($controller_path)) {
    die("❌ ReportController.php non trouvé\n");
}

echo "=== PATCH LIMITATION HISTORIQUE DE STOCK ===\n\n";

$content = file_get_contents($controller_path);

// Chercher la méthode qui génère l'historique
$patterns_to_find = [
    '/getStockHistory.*?\{/s',
    '/productStockHistory.*?\{/s',
    '/stock.*history.*?\{/si'
];

$found = false;
foreach ($patterns_to_find as $pattern) {
    if (preg_match($pattern, $content, $matches)) {
        echo "✅ Méthode d'historique trouvée\n";
        echo "Pattern: {$pattern}\n";
        echo "Match: " . substr($matches[0], 0, 100) . "...\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "⚠️ Méthode d'historique non trouvée automatiquement\n";
    echo "Recherche manuelle...\n\n";
    
    // Lister toutes les méthodes publiques
    preg_match_all('/public function (\w+)\s*\(/', $content, $all_methods);
    echo "Méthodes trouvées dans ReportController:\n";
    foreach ($all_methods[1] as $method) {
        if (stripos($method, 'stock') !== false || stripos($method, 'history') !== false || stripos($method, 'product') !== false) {
            echo "  - {$method}\n";
        }
    }
}

echo "\n=== INSTRUCTIONS MANUELLES ===\n";
echo "Il faut modifier la requête qui charge l'historique pour ajouter:\n";
echo "->limit(500)\n";
echo "ou\n";
echo "->take(500)\n";
echo "\nCherche dans le fichier les requêtes UNION qui combinent achats et ventes.\n";
