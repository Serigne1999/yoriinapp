<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\BusinessLocation;
use Illuminate\Support\Facades\DB;

echo "=== ANALYSE DES LOCATIONS PROBLÉMATIQUES ===\n\n";

$locations = BusinessLocation::all();

$problematic = [];

foreach($locations as $loc) {
    // Compter le total d'entrées d'historique
    $total_history = DB::select("
        SELECT COUNT(*) as count FROM (
            SELECT pl.id
            FROM purchase_lines pl
            JOIN transactions t ON pl.transaction_id = t.id
            WHERE t.location_id = ?
            
            UNION ALL
            
            SELECT tsl.id
            FROM transaction_sell_lines tsl
            JOIN transactions t ON tsl.transaction_id = t.id
            WHERE t.location_id = ?
        ) as combined
    ", [$loc->id, $loc->id])[0]->count;
    
    echo "Location: {$loc->name}\n";
    echo "  Total entrées historique: {$total_history}\n";
    
    if ($total_history > 1000) {
        echo "  ⚠️ TROP D'ENTRÉES - Risque de crash JavaScript\n";
        $problematic[] = $loc->name;
    } else {
        echo "  ✅ OK\n";
    }
    echo "---\n";
}

if (!empty($problematic)) {
    echo "\n⚠️ LOCATIONS PROBLÉMATIQUES:\n";
    foreach($problematic as $name) {
        echo "- {$name}\n";
    }
    echo "\nCes locations ont trop de données pour l'interface actuelle.\n";
    echo "Solution: Limiter l'affichage aux 500 dernières entrées.\n";
}
