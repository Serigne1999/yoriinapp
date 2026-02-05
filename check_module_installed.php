<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Utils\ModuleUtil;
use Nwidart\Modules\Facades\Module;

echo "=== VÉRIFICATION MODULE MULTISERVICES ===\n\n";

$moduleUtil = new ModuleUtil();

echo "1. Module existe dans nWidart:\n";
$module = Module::find('Multiservices');
if ($module) {
    echo "   ✓ Module trouvé\n";
    echo "   Nom: {$module->getName()}\n";
    echo "   Status: " . ($module->isEnabled() ? 'Enabled' : 'Disabled') . "\n";
} else {
    echo "   ✗ Module NON trouvé\n";
}

echo "\n2. Test isModuleInstalled:\n";
$is_installed = $moduleUtil->isModuleInstalled('Multiservices');
echo "   Résultat: " . ($is_installed ? 'TRUE ✓' : 'FALSE ✗') . "\n";

echo "\n3. Vérification dans la table 'system':\n";
$system = DB::table('system')->first();
if ($system) {
    echo "   Colonne 'multiservices_version': ";
    if (isset($system->multiservices_version)) {
        echo $system->multiservices_version . "\n";
    } else {
        echo "N'EXISTE PAS ✗\n";
        echo "   → C'est ça le problème !\n";
    }
}

echo "\n=== SOLUTION ===\n";
echo "Il faut ajouter la colonne 'multiservices_version' dans la table 'system'\n";
