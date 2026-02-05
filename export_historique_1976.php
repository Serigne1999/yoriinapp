<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\PurchaseLine;
use App\TransactionSellLine;

$variation_id = 1976;
$location_id = 23;

$output = "=== HISTORIQUE COMPLET SKU 1976 - TAMBACOUNDA ===\n";
$output .= "Date d'export: " . date('Y-m-d H:i:s') . "\n\n";

// Achats
$purchases = PurchaseLine::where('variation_id', $variation_id)
    ->join('transactions', 'purchase_lines.transaction_id', '=', 'transactions.id')
    ->where('transactions.location_id', $location_id)
    ->select('transactions.transaction_date', 'transactions.ref_no', 'purchase_lines.quantity', 'transactions.status')
    ->orderBy('transactions.transaction_date', 'desc')
    ->get();

$output .= "=== ACHATS ({$purchases->count()}) ===\n";
foreach ($purchases as $p) {
    $output .= "{$p->transaction_date} | {$p->ref_no} | Qty: {$p->quantity} | {$p->status}\n";
}

// Ventes
$sales = TransactionSellLine::where('variation_id', $variation_id)
    ->join('transactions', 'transaction_sell_lines.transaction_id', '=', 'transactions.id')
    ->where('transactions.location_id', $location_id)
    ->select('transactions.transaction_date', 'transactions.invoice_no', 'transaction_sell_lines.quantity', 'transactions.status')
    ->orderBy('transactions.transaction_date', 'desc')
    ->get();

$output .= "\n=== VENTES ({$sales->count()}) ===\n";
foreach ($sales as $s) {
    $output .= "{$s->transaction_date} | {$s->invoice_no} | Qty: {$s->quantity} | {$s->status}\n";
}

file_put_contents('historique_1976_export.csv', $output);
echo "✅ Export créé: historique_1976_export.csv\n";
