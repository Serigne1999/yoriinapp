<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Variation;
use App\Product;
use App\BusinessLocation;
use Illuminate\Support\Facades\DB;

$variation_id = $_GET['variation_id'] ?? null;
$location_id = $_GET['location_id'] ?? null;

if (!$variation_id) {
    die("Paramètre variation_id manquant");
}

$variation = Variation::find($variation_id);
$product = Product::find($variation->product_id);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Historique Stock - <?= $product->name ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            position: sticky;
            top: 0;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .purchase {
            color: #28a745;
            font-weight: bold;
        }
        .sell {
            color: #dc3545;
            font-weight: bold;
        }
        .adjustment {
            color: #ffc107;
            font-weight: bold;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card h3 {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .summary-card p {
            margin: 10px 0 0 0;
            font-size: 28px;
            font-weight: bold;
        }
        .filters {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .filters select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        .export-btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .export-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Historique de Stock Complet</h1>
        
        <div class="info">
            <strong>Produit:</strong> <?= $product->name ?><br>
            <strong>SKU:</strong> <?= $variation->sub_sku ?><br>
            <?php if ($location_id): ?>
                <strong>Location:</strong> <?= BusinessLocation::find($location_id)->name ?>
            <?php endif; ?>
        </div>

        <?php
        // Calculer les statistiques
        $where_location = $location_id ? "AND t.location_id = $location_id" : "";
        
        $purchases = DB::select("
            SELECT SUM(pl.quantity) as total
            FROM purchase_lines pl
            JOIN transactions t ON pl.transaction_id = t.id
            WHERE pl.variation_id = ? AND t.status = 'received' $where_location
        ", [$variation_id])[0]->total ?? 0;
        
        $sales = DB::select("
            SELECT SUM(tsl.quantity) as total
            FROM transaction_sell_lines tsl
            JOIN transactions t ON tsl.transaction_id = t.id
            WHERE tsl.variation_id = ? AND t.status IN ('final', 'delivered') $where_location
        ", [$variation_id])[0]->total ?? 0;
        
        $current_stock = $purchases - $sales;
        ?>

        <div class="summary">
            <div class="summary-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3>Total Acheté</h3>
                <p><?= number_format($purchases, 2) ?></p>
            </div>
            <div class="summary-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>Total Vendu</h3>
                <p><?= number_format($sales, 2) ?></p>
            </div>
            <div class="summary-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>Stock Actuel</h3>
                <p><?= number_format($current_stock, 2) ?></p>
            </div>
        </div>

        <div class="filters">
            <strong>Filtres:</strong>
            <select id="typeFilter" onchange="filterTable()">
                <option value="">Tous les types</option>
                <option value="purchase">Achats</option>
                <option value="sell">Ventes</option>
                <option value="adjustment">Ajustements</option>
            </select>
            <button class="export-btn" onclick="exportToCSV()">📥 Exporter CSV</button>
        </div>

        <table id="historyTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Référence</th>
                    <th>Quantité</th>
                    <th>Stock Après</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $history = DB::select("
                    SELECT 
                        'purchase' as type,
                        t.transaction_date,
                        t.ref_no as reference,
                        pl.quantity as quantity,
                        t.status
                    FROM purchase_lines pl
                    JOIN transactions t ON pl.transaction_id = t.id
                    WHERE pl.variation_id = ? $where_location
                    
                    UNION ALL
                    
                    SELECT 
                        'sell' as type,
                        t.transaction_date,
                        t.invoice_no as reference,
                        -tsl.quantity as quantity,
                        t.status
                    FROM transaction_sell_lines tsl
                    JOIN transactions t ON tsl.transaction_id = t.id
                    WHERE tsl.variation_id = ? $where_location
                    
                    ORDER BY transaction_date DESC
                ", [$variation_id, $variation_id]);
                
                $running_stock = $current_stock;
                $reversed = array_reverse($history);
                $running_stock = 0;
                
                foreach ($reversed as $entry) {
                    $running_stock += $entry->quantity;
                }
                
                foreach ($history as $entry):
                    $type_class = $entry->type;
                    $type_label = $entry->type === 'purchase' ? '📦 Achat' : '🛒 Vente';
                    $qty_display = $entry->quantity > 0 ? "+{$entry->quantity}" : $entry->quantity;
                ?>
                <tr data-type="<?= $entry->type ?>">
                    <td><?= date('d/m/Y H:i', strtotime($entry->transaction_date)) ?></td>
                    <td><span class="<?= $type_class ?>"><?= $type_label ?></span></td>
                    <td><?= $entry->reference ?: '--' ?></td>
                    <td class="<?= $type_class ?>"><?= $qty_display ?></td>
                    <td><?= number_format($running_stock, 2) ?></td>
                    <td><?= $entry->status ?></td>
                </tr>
                <?php
                    $running_stock -= $entry->quantity;
                endforeach;
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function filterTable() {
            const filter = document.getElementById('typeFilter').value;
            const rows = document.querySelectorAll('#historyTable tbody tr');
            
            rows.forEach(row => {
                if (filter === '' || row.dataset.type === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function exportToCSV() {
            const table = document.getElementById('historyTable');
            let csv = [];
            
            // Headers
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
            csv.push(headers.join(','));
            
            // Rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cells = Array.from(row.querySelectorAll('td')).map(td => {
                        return '"' + td.textContent.trim().replace(/"/g, '""') + '"';
                    });
                    csv.push(cells.join(','));
                }
            });
            
            // Download
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'historique_stock_<?= $variation->sub_sku ?>_<?= date('Y-m-d') ?>.csv';
            link.click();
        }
    </script>
</body>
</html>
