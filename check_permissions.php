<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = auth()->id() ?? 1; // Remplace par ton ID utilisateur
$user = App\User::find($userId);

echo "=== PERMISSIONS PRODUITS ===\n";
echo "Utilisateur: " . $user->username . "\n";
echo "product.view: " . ($user->can('product.view') ? 'OUI' : 'NON') . "\n";
echo "product.create: " . ($user->can('product.create') ? 'OUI' : 'NON') . "\n";
echo "product.update: " . ($user->can('product.update') ? 'OUI' : 'NON') . "\n";
echo "product.delete: " . ($user->can('product.delete') ? 'OUI' : 'NON') . "\n";