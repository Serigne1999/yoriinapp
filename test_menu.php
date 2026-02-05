<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSTIC MODULE MULTISERVICES ===\n\n";

// 1. Vérifier package_modules
echo "1. État dans package_modules:\n";
$pkg = DB::table('package_modules')->where('module_key', 'multiservices')->first();
if ($pkg) {
    echo "   ✓ Trouvé - ID: {$pkg->id}, is_enabled: {$pkg->is_enabled}, package_id: {$pkg->package_id}\n";
} else {
    echo "   ✗ NON TROUVÉ\n";
}

// 2. Vérifier le package
echo "\n2. Package associé (ID: {$pkg->package_id}):\n";
$package = DB::table('packages')->where('id', $pkg->package_id)->first();
if ($package) {
    echo "   ✓ Package: {$package->name}\n";
    echo "   Description: {$package->description}\n";
} else {
    echo "   ✗ Package non trouvé\n";
}

// 3. Tester hasThePermissionInSubscription
echo "\n3. Test hasThePermissionInSubscription:\n";
$business_id = 1; // Ou votre business_id
$module_util = new ModuleUtil();

try {
    $has_permission = $module_util->hasThePermissionInSubscription($business_id, 'multiservices');
    echo "   Résultat: " . ($has_permission ? "TRUE ✓" : "FALSE ✗") . "\n";
} catch (Exception $e) {
    echo "   ERREUR: " . $e->getMessage() . "\n";
}

// 4. Vérifier DataController existe
echo "\n4. DataController:\n";
$class = 'Modules\Multiservices\Http\Controllers\DataController';
if (class_exists($class)) {
    echo "   ✓ Classe existe\n";
    $controller = new $class();
    if (method_exists($controller, 'modifyAdminMenu')) {
        echo "   ✓ Méthode modifyAdminMenu existe\n";
    } else {
        echo "   ✗ Méthode modifyAdminMenu N'EXISTE PAS\n";
    }
} else {
    echo "   ✗ Classe N'EXISTE PAS\n";
}

echo "\n=== FIN DIAGNOSTIC ===\n";
