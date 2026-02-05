<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationToOperatorAccounts extends Migration
{
    public function up()
    {
        Schema::table('operator_accounts', function (Blueprint $table) {
            $table->unsignedInteger('location_id')->after('business_id');
            
            // Foreign key vers business_locations
            $table->foreign('location_id')
                  ->references('id')
                  ->on('business_locations')
                  ->onDelete('cascade');
            
            // Index pour améliorer les performances
            $table->index(['business_id', 'location_id', 'operator']);
        });
        
        // Mettre à jour les comptes existants avec la première location du business
        DB::statement("
            UPDATE operator_accounts oa
            SET location_id = (
                SELECT id 
                FROM business_locations 
                WHERE business_id = oa.business_id 
                LIMIT 1
            )
            WHERE location_id IS NULL OR location_id = 0
        ");
    }

    public function down()
    {
        Schema::table('operator_accounts', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropIndex(['business_id', 'location_id', 'operator']);
            $table->dropColumn('location_id');
        });
    }
}