<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Variation;
use App\PurchaseLine;
use App\TransactionSellLine;
use App\StockAdjustmentLine;
use Illuminate\Support\Facades\DB;

// Prendre un produit au hasard pour tester
$variation = Variation::where('sub_sku', '1976')->first();

if (!$variation) {
    echo "Variation 1976 non trouvée\n";
    exit;
}

echo "=== TEST HISTORIQUE SKU 1976 ===\n\n";

// Vérifier si la vue/route existe
echo "1. Vérification des données brutes:\n";

// Achats
$purchases = PurchaseLine::where('variation_id', $variation->id)
    ->join('transactions as t', 'purchase_lines.transaction_id', '=', 't.id')
    ->count();
echo "   Achats: {$purchases}\n";

// Ventes
$sales = TransactionSellLine::where('variation_id', $variation->id)
    ->join('transactions as t', 'transaction_sell_lines.transaction_id', '=', 't.id')
    ->count();
echo "   Ventes: {$sales}\n";

// Ajustements
$adjustments = DB::table('stock_adjustment_lines')
    ->where('variation_id', $variation->id)
    ->count();
echo "   Ajustements: {$adjustments}\n";

$total = $purchases + $sales + $adjustments;
echo "   TOTAL: {$total}\n\n";

echo "2. Vérification de la structure de stock_adjustment_lines:\n";
$columns = DB::select("SHOW COLUMNS FROM stock_adjustment_lines");
foreach($columns as $col) {
    echo "   - {$col->Field}\n";
}

echo "\n3. Test de la requête utilisée par l'interface:\n";
// Simuler la requête que fait UltimatePOS pour l'historique
$history_query = "
    SELECT 
        'purchase' as type,
        t.transaction_date,
        t.ref_no as reference,
        pl.quantity as quantity_changed,
        NULL as new_quantity
    FROM purchase_lines pl
    JOIN transactions t ON pl.transaction_id = t.id
    WHERE pl.variation_id = {$variation->id}
    
    UNION ALL
    
    SELECT 
        'sell' as type,
        t.transaction_date,
        t.invoice_no as reference,
        -tsl.quantity as quantity_changed,
        NULL as new_quantity
    FROM transaction_sell_lines tsl
    JOIN transactions t ON tsl.transaction_id = t.id
    WHERE tsl.variation_id = {$variation->id}
    
    ORDER BY transaction_date DESC
    LIMIT 10
";

try {
    $results = DB::select($history_query);
    echo "   Résultats: " . count($results) . " lignes\n";
    foreach($results as $r) {
        echo "   - {$r->transaction_date} | {$r->type} | {$r->reference} | Qty: {$r->quantity_changed}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST ===\n";
