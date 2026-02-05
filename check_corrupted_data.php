<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\BusinessLocation;

echo "=== RECHERCHE DE DONNÉES CORROMPUES ===\n\n";

$locations = BusinessLocation::all();

foreach($locations as $loc) {
    echo "Location: {$loc->name}\n";
    
    // Chercher des achats avec quantité nulle ou négative
    $bad_purchases = \DB::select("
        SELECT COUNT(*) as count
        FROM purchase_lines pl
        JOIN transactions t ON pl.transaction_id = t.id
        WHERE t.location_id = ? AND (pl.quantity <= 0 OR pl.quantity > 1000000)
    ", [$loc->id])[0]->count;
    
    // Chercher des ventes avec quantité nulle
    $bad_sales = \DB::select("
        SELECT COUNT(*) as count
        FROM transaction_sell_lines tsl
        JOIN transactions t ON tsl.transaction_id = t.id
        WHERE t.location_id = ? AND (tsl.quantity <= 0 OR tsl.quantity > 1000000)
    ", [$loc->id])[0]->count;
    
    // Chercher des transactions sans date
    $no_date = \DB::select("
        SELECT COUNT(*) as count
        FROM transactions t
        WHERE t.location_id = ? AND t.transaction_date IS NULL
    ", [$loc->id])[0]->count;
    
    if ($bad_purchases > 0 || $bad_sales > 0 || $no_date > 0) {
        echo "  ⚠️ PROBLÈMES DÉTECTÉS:\n";
        if ($bad_purchases > 0) echo "    - Achats anormaux: {$bad_purchases}\n";
        if ($bad_sales > 0) echo "    - Ventes anormales: {$bad_sales}\n";
        if ($no_date > 0) echo "    - Transactions sans date: {$no_date}\n";
    } else {
        echo "  ✅ Aucune donnée corrompue\n";
    }
    echo "---\n";
}1~<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\BusinessLocation;

echo "=== RECHERCHE DE DONNÉES CORROMPUES ===\n\n";

$locations = BusinessLocation::all();

foreach($locations as $loc) {
    echo "Location: {$loc->name}\n";
    
    // Chercher des achats avec quantité nulle ou négative
    $bad_purchases = \DB::select("
        SELECT COUNT(*) as count
        FROM purchase_lines pl
        JOIN transactions t ON pl.transaction_id = t.id
        WHERE t.location_id = ? AND (pl.quantity <= 0 OR pl.quantity > 1000000)
    ", [$loc->id])[0]->count;
    
    // Chercher des ventes avec quantité nulle
    $bad_sales = \DB::select("
        SELECT COUNT(*) as count
        FROM transaction_sell_lines tsl
        JOIN transactions t ON tsl.transaction_id = t.id
        WHERE t.location_id = ? AND (tsl.quantity <= 0 OR tsl.quantity > 1000000)
    ", [$loc->id])[0]->count;
    
    // Chercher des transactions sans date
    $no_date = \DB::select("
        SELECT COUNT(*) as count
        FROM transactions t
        WHERE t.location_id = ? AND t.transaction_date IS NULL
    ", [$loc->id])[0]->count;
    
    if ($bad_purchases > 0 || $bad_sales > 0 || $no_date > 0) {
        echo "  ⚠️ PROBLÈMES DÉTECTÉS:\n";
        if ($bad_purchases > 0) echo "    - Achats anormaux: {$bad_purchases}\n";
        if ($bad_sales > 0) echo "    - Ventes anormales: {$bad_sales}\n";
        if ($no_date > 0) echo "    - Transactions sans date: {$no_date}\n";
    } else {
        echo "  ✅ Aucune donnée corrompue\n";
    }
    echo "---\n";
}
