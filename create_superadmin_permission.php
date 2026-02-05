<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use App\User;

echo "=== CRÉATION PERMISSION SUPERADMIN ===\n\n";

// 1. Trouver l'utilisateur
$user = User::where('username', 'yoriinapp')->first();
if (!$user) {
    echo "❌ User 'yoriinapp' non trouvé\n";
    exit;
}
echo "✓ User: {$user->username} (ID: {$user->id})\n";

// 2. Créer/trouver la permission 'superadmin'
$permission = Permission::where('name', 'superadmin')->first();

if (!$permission) {
    echo "\nCréation de la permission 'superadmin'...\n";
    try {
        $permission = Permission::create([
            'name' => 'superadmin',
            'guard_name' => 'web'
        ]);
        echo "✓ Permission créée (ID: {$permission->id})\n";
    } catch (Exception $e) {
        echo "❌ Erreur: {$e->getMessage()}\n";
        exit;
    }
} else {
    echo "✓ Permission existe (ID: {$permission->id})\n";
}

// 3. Assigner la permission directement à l'utilisateur
if ($user->hasPermissionTo('superadmin')) {
    echo "\n✓ User a déjà la permission superadmin\n";
} else {
    echo "\nAssignation de la permission...\n";
    $user->givePermissionTo('superadmin');
    echo "✅ Permission assignée !\n";
}

// 4. Vérifier
echo "\n=== VÉRIFICATION ===\n";
$user = User::find($user->id); // Recharger
if ($user->can('superadmin')) {
    echo "✅ user->can('superadmin') = TRUE\n";
} else {
    echo "❌ user->can('superadmin') = FALSE\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 Connectez-vous avec 'yoriinapp' et accédez à /superadmin\n";
