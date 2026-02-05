<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class AddMultiservicesCancelPermission extends Migration
{
    public function up()
    {
        // Créer la permission
        Permission::create([
            'name' => 'multiservices.cancel',
            'guard_name' => 'web'
        ]);
        
        // Attribuer automatiquement aux admins (utilisateurs avec toutes les permissions)
        $adminUsers = \App\User::whereHas('roles', function($q) {
            $q->where('name', 'Admin');
        })->get();
        
        foreach ($adminUsers as $admin) {
            $admin->givePermissionTo('multiservices.cancel');
        }
    }

    public function down()
    {
        $permission = Permission::where('name', 'multiservices.cancel')->first();
        if ($permission) {
            $permission->delete();
        }
    }
}