<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\User;

echo "=== ASSIGNATION RÔLE SUPERADMIN ===\n\n";


// 3. Assigner le rôle à l'utilisateur
if ($user->hasRole('superadmin')) {
  echo "\n✓ L'utilisateur a déjà le rôle superadmin\n";
} else {
    echo "\nAssignation du rôle...\n";
    $user->assignRole('superadmin');
    echo "✅ Rôle superadmin assigné à {$user->username} !\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 Vous pouvez maintenant:\n";
echo "   1. Vous connecter avec: {$user->username}\n";
echo "   2. Accéder à: https://yoriinapp.com/superadmin\n";
