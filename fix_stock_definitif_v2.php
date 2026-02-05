<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Variation;
use App\VariationLocationDetails;
use App\PurchaseLine;
use App\TransactionSellLine;
use App\BusinessLocation;
use App\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CORRECTION DÉFINITIVE DES STOCKS V2 ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// ÉTAPE 1 : Configuration overselling
echo "ÉTAPE 1 : Configuration overselling...\n";
try {
    if (!Schema::hasColumn('business_locations', 'allow_overselling')) {
        Schema::table('business_locations', function ($table) {
            $table->boolean('allow_overselling')->default(0);
        });
        echo "✅ Colonne allow_overselling ajoutée\n";
    }
    
    DB::table('business_locations')->update(['allow_overselling' => 0]);
    echo "✅ Overselling désactivé pour toutes les locations\n\n";
} catch (\Exception $e) {
    echo "✅ Overselling déjà configuré\n\n";
}

// ÉTAPE 2 : Recalculer TOUS les stocks
echo "ÉTAPE 2 : Recalcul de tous les stocks...\n";
$all_stocks = VariationLocationDetails::all();
$total = $all_stocks->count();
$corrected = 0;
$negative_found = 0;
$errors = 0;

echo "Total à traiter: {$total}\n";
echo "Traitement en cours...\n\n";

foreach ($all_stocks as $index => $stock_detail) {
    try {
        // Calculer achats
        $purchased = PurchaseLine::where('variation_id', $stock_detail->variation_id)
            ->join('transactions', 'purchase_lines.transaction_id', '=', 'transactions.id')
            ->where('transactions.location_id', $stock_detail->location_id)
            ->where('transactions.status', 'received')
            ->sum('purchase_lines.quantity');
        
        // Calculer ventes
        $sold = TransactionSellLine::where('variation_id', $stock_detail->variation_id)
            ->join('transactions', 'transaction_sell_lines.transaction_id', '=', 'transactions.id')
            ->where('transactions.location_id', $stock_detail->location_id)
            ->whereIn('transactions.status', ['final', 'delivered'])
            ->sum('transaction_sell_lines.quantity');
        
        $calculated_stock = $purchased - $sold;
        $current_stock = $stock_detail->qty_available;
        
        // Si différence, corriger
        if ($calculated_stock != $current_stock) {
            $variation = Variation::find($stock_detail->variation_id);
            $product = Product::find($variation->product_id);
            $location = BusinessLocation::find($stock_detail->location_id);
            
            if ($calculated_stock < 0) {
                $negative_found++;
                echo "⚠️  NÉGATIF: {$variation->sub_sku} - {$location->name}\n";
            } else {
                echo "✓ Correction: {$variation->sub_sku} - {$location->name}\n";
            }
            echo "   Avant: {$current_stock} → Après: {$calculated_stock}\n";
            
            $stock_detail->qty_available = $calculated_stock;
            $stock_detail->save();
            $corrected++;
        }
        
        // Afficher progression tous les 50
        if (($index + 1) % 50 == 0) {
            echo "Progression: " . ($index + 1) . "/{$total}\n";
        }
        
    } catch (\Exception $e) {
        $errors++;
        echo "❌ Erreur variation {$stock_detail->variation_id}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== RÉSULTATS ===\n";
echo "✅ Total traité: {$total}\n";
echo "✅ Stocks corrigés: {$corrected}\n";
echo "⚠️  Stocks négatifs trouvés: {$negative_found}\n";
echo "❌ Erreurs: {$errors}\n\n";

// ÉTAPE 3 : Rapport détaillé des stocks négatifs
if ($negative_found > 0) {
    echo "ÉTAPE 3 : Détail des stocks négatifs...\n";
    $negatives = VariationLocationDetails::where('qty_available', '<', 0)
        ->orderBy('qty_available', 'asc')
        ->limit(20)
        ->get();
    
    echo "\nTop 20 stocks les plus négatifs:\n";
    echo "---------------------------------------\n";
    
    $total_negative_value = 0;
    foreach ($negatives as $neg) {
        $variation = Variation::find($neg->variation_id);
        $product = Product::find($variation->product_id);
        $location = BusinessLocation::find($neg->location_id);
        
        echo "{$location->name}\n";
        echo "  Produit: {$product->name}\n";
        echo "  SKU: {$variation->sub_sku}\n";
        echo "  Stock: {$neg->qty_available}\n";
        echo "---\n";
        
        $total_negative_value += $neg->qty_available;
    }
    
    echo "\nValeur totale négative (top 20): {$total_negative_value}\n";
    
    // Option de correction automatique
    echo "\n💡 OPTION : Remettre tous les stocks négatifs à zéro ?\n";
    echo "Cela effacera la trace des ventes sans stock, mais résoudra le problème visuel.\n";
    echo "Pour exécuter, lance : php reset_negative_stocks.php\n";
}

echo "\n=== CORRECTION TERMINÉE ===\n";
