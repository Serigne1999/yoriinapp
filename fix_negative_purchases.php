<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\PurchaseLine;
use App\Transaction;
use App\Variation;
use App\Product;

echo "=== NETTOYAGE DES ACHATS NÉGATIFS ===\n\n";

// Trouver tous les achats négatifs
$negative_purchases = PurchaseLine::where('quantity', '<', 0)->get();

echo "Achats négatifs trouvés: {$negative_purchases->count()}\n\n";

if ($negative_purchases->count() > 0) {
    foreach ($negative_purchases as $purchase) {
        $variation = Variation::find($purchase->variation_id);
        $product = Product::find($variation->product_id);
        $transaction = Transaction::find($purchase->transaction_id);
        
        echo "Produit: {$product->name} (SKU: {$variation->sub_sku})\n";
        echo "Quantité: {$purchase->quantity}\n";
        echo "Date: {$transaction->transaction_date}\n";
        echo "Ref: {$transaction->ref_no}\n";
        
        // Supprimer
        $purchase->delete();
        echo "✅ Supprimé\n---\n";
    }
    
    echo "\n✅ Tous les achats négatifs ont été supprimés\n";
} else {
    echo "✅ Aucun achat négatif trouvé\n";
}
