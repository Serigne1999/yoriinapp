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
use Illuminate\Support\Facades\DB;

echo "=== CORRECTION DÉFINITIVE DES STOCKS ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// ÉTAPE 1 : Ajouter la colonne allow_overselling si elle n'existe pas
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
    echo "⚠️ Erreur overselling: " . $e->getMessage() . "\n\n";
}

// ÉTAPE 2 : Corriger les opening_stock incorrects
echo "ÉTAPE 2 : Correction des stocks d'ouverture...\n";
$wrong_opening = VariationLocationDetails::where('opening_stock', '>', 0)->count();
echo "Stocks d'ouverture à corriger: {$wrong_opening}\n";

if ($wrong_opening > 0) {
    VariationLocationDetails::where('opening_stock', '>', 0)->update(['opening_stock' => 0]);
    echo "✅ Stocks d'ouverture remis à zéro\n\n";
}

// ÉTAPE 3 : Recalculer TOUS les stocks
echo "ÉTAPE 3 : Recalcul de tous les stocks...\n";
$all_variations = VariationLocationDetails::all();
$corrected = 0;
$errors = 0;

foreach ($all_variations as $stock_detail) {
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
            $location = BusinessLocation::find($stock_detail->location_id);
            
            echo "Correction: {$variation->sub_sku} - {$location->name}\n";
            echo "  Avant: {$current_stock} → Après: {$calculated_stock}\n";
            
            $stock_detail->qty_available = $calculated_stock;
            $stock_detail->save();
            $corrected++;
        }
    } catch (\Exception $e) {
        $errors++;
        echo "❌ Erreur variation {$stock_detail->variation_id}: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Correction terminée : {$corrected} stocks corrigés, {$errors} erreurs\n\n";

// ÉTAPE 4 : Rapport final
echo "ÉTAPE 4 : Rapport final...\n";
$negative_count = VariationLocationDetails::where('qty_available', '<', 0)->count();
$negative_total = VariationLocationDetails::where('qty_available', '<', 0)->sum('qty_available');

echo "Stocks négatifs restants: {$negative_count}\n";
echo "Total valeur négative: {$negative_total}\n\n";

if ($negative_count > 0) {
    echo "⚠️ ATTENTION : Il reste des stocks négatifs !\n";
    echo "Options :\n";
    echo "1. Les laisser (pour traçabilité comptable)\n";
    echo "2. Les remettre à zéro (perte d'historique)\n\n";
    
    // Liste des 10 pires
    $top_negative = VariationLocationDetails::where('qty_available', '<', 0)
        ->orderBy('qty_available', 'asc')
        ->limit(10)
        ->get();
    
    echo "Top 10 stocks les plus négatifs:\n";
    foreach ($top_negative as $neg) {
        $variation = Variation::find($neg->variation_id);
        $location = BusinessLocation::find($neg->location_id);
        echo "- {$variation->sub_sku} ({$location->name}): {$neg->qty_available}\n";
    }
}

echo "\n=== CORRECTION TERMINÉE ===\n";
