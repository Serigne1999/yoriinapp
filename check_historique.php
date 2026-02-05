<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Transaction;
use App\PurchaseLine;
use App\TransactionSellLine;

// Vérifier l'historique du produit SKU 1976
$variation_id = 1976;

echo "=== HISTORIQUE COMPLET SKU 1976 ===\n\n";

// Achats
$purchases = PurchaseLine::where('variation_id', $variation_id)
    ->join('transactions', 'purchase_lines.transaction_id', '=', 'transactions.id')
    ->select('transactions.transaction_date', 'transactions.ref_no', 'purchase_lines.quantity', 'transactions.status')
    ->orderBy('transactions.transaction_date', 'desc')
    ->get();

echo "=== ACHATS ({$purchases->count()}) ===\n";
foreach ($purchases as $p) {
    echo "{$p->transaction_date} - {$p->ref_no} - Qty: {$p->quantity} - Status: {$p->status}\n";
}

// Ventes
$sales = TransactionSellLine::where('variation_id', $variation_id)
    ->join('transactions', 'transaction_sell_lines.transaction_id', '=', 'transactions.id')
    ->select('transactions.transaction_date', 'transactions.invoice_no', 'transaction_sell_lines.quantity', 'transactions.status')
    ->orderBy('transactions.transaction_date', 'desc')
    ->get();

echo "\n=== VENTES ({$sales->count()}) ===\n";
foreach ($sales as $s) {
    echo "{$s->transaction_date} - {$s->invoice_no} - Qty: {$s->quantity} - Status: {$s->status}\n";
}

echo "\nTotal entrées: " . ($purchases->count() + $sales->count()) . "\n";
