<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST ACCÈS MULTISERVICES ===\n\n";

// Trouver n'importe quel user admin
$user = \App\User::whereHas('roles', function($q) {
    $q->where('name', 'like', 'Admin#%');
})->first();

if (!$user) {
    echo "❌ Aucun user Admin trouvé\n";
    exit;
}

echo "User: {$user->username} (ID: {$user->id})\n";
echo "Business ID: {$user->business_id}\n\n";

// Tester l'accès au controller
echo "1. Test Controller:\n";
try {
    $controller = new \Modules\Multiservices\Http\Controllers\MultiservicesController();
    echo "   ✓ Controller instancié\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
}

// Tester l'accès au model
echo "\n2. Test Model:\n";
try {
    $count = \Modules\Multiservices\Entities\MultiserviceTransaction::count();
    echo "   ✓ {$count} transactions en base\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: {$e->getMessage()}\n";
}

// Tester si le scope existe
echo "\n3. Test Scope forBusiness:\n";
try {
    $transactions = \Modules\Multiservices\Entities\MultiserviceTransaction::forBusiness($user->business_id)->get();
    echo "   ✓ Scope fonctionne, {$transactions->count()} transactions\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: {$e->getMessage()}\n";
}
