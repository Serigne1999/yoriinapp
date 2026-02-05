<?php

namespace Modules\Multiservices\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MultiservicesCancelPermissionSeeder extends Seeder
{
    public function run()
    {
        // Créer la permission si elle n'existe pas
        $permission = Permission::firstOrCreate([
            'name' => 'multiservices.cancel',
            'guard_name' => 'web'
        ]);
        
        echo "✅ Permission 'multiservices.cancel' créée/vérifiée\n";
        
        // Attribuer automatiquement aux admins
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        
        if ($adminRole) {
            $adminRole->givePermissionTo('multiservices.cancel');
            echo "✅ Permission attribuée au rôle Admin\n";
        }
        
        // Attribuer aux utilisateurs qui ont déjà multiservices.update
        $usersWithUpdate = \App\User::permission('multiservices.update')->get();
        
        foreach ($usersWithUpdate as $user) {
            $user->givePermissionTo('multiservices.cancel');
        }
        
        echo "✅ Permission attribuée à " . $usersWithUpdate->count() . " utilisateur(s) ayant multiservices.update\n";
        
        echo "\n🎉 Permission multiservices.cancel configurée avec succès !\n";
    }
}
