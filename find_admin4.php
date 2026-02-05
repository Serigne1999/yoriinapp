<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== IDENTIFICATION DES UTILISATEURS PAR RÔLE ===\n\n";

// Mapper les rôles aux business
$roles = DB::table('roles')->where('name', 'like', 'Admin#%')->get();

foreach ($roles as $role) {
    // Extraire le business_id du nom du rôle (Admin#4 -> business 4)
    preg_match('/Admin#(\d+)/', $role->name, $matches);
    $business_id = $matches[1] ?? null;
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "Rôle: {$role->name} (ID: {$role->id})\n";
    
    if ($business_id) {
        // Trouver le business
        $business = DB::table('business')->where('id', $business_id)->first();
        
        if ($business) {
            echo "Business: {$business->name} (ID: {$business->id})\n";
            
            // Trouver les utilisateurs de ce business avec ce rôle
            $users = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.role_id', $role->id)
                ->where('users.business_id', $business_id)
                ->select('users.id', 'users.username', 'users.email', 'users.first_name', 'users.last_name')
                ->get();
            
            if ($users->count() > 0) {
                echo "\nUtilisateurs Admin de ce business:\n";
                echo str_repeat("-", 80) . "\n";
                printf("%-5s | %-20s | %-30s | %-20s\n", "ID", "Username", "Email", "Nom");
                echo str_repeat("-", 80) . "\n";
                
                foreach ($users as $user) {
                    $full_name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                    printf("%-5s | %-20s | %-30s | %-20s\n", 
                        $user->id,
                        substr($user->username ?? 'N/A', 0, 20),
                        substr($user->email ?? 'N/A', 0, 30),
                        substr($full_name ?: 'N/A', 0, 20)
                    );
                }
            } else {
                echo "Aucun utilisateur trouvé avec ce rôle\n";
            }
        }
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "\n💡 Pour vous connecter avec un compte Admin#4:\n";
echo "   Utilisez un des utilisateurs listés ci-dessus pour le Business ID 4\n";
