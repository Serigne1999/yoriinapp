<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiservicesCashRegistersTable extends Migration
{
    public function up()
    {
        Schema::create('multiservices_cash_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->integer('location_id')->unsigned();
            $table->integer('user_id')->unsigned()->comment('Agent qui ouvre');
            
            $table->enum('status', ['open', 'closed'])->default('open');
            
            $table->decimal('opening_amount', 20, 2)->default(0)->comment('Fond de départ');
            $table->decimal('expected_amount', 20, 2)->default(0)->comment('Montant théorique');
            $table->decimal('closing_amount', 20, 2)->nullable()->comment('Comptage réel');
            $table->decimal('shortage', 20, 2)->default(0)->comment('Manque');
            $table->decimal('excess', 20, 2)->default(0)->comment('Surplus');
            
            $table->datetime('opened_at');
            $table->datetime('closed_at')->nullable();
            $table->integer('closed_by')->unsigned()->nullable();
            
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('business_id');
            $table->index('location_id');
            $table->index('user_id');
            $table->index('status');
            $table->index(['business_id', 'location_id', 'status'], 'ms_cash_reg_bus_loc_stat_idx');
            
            // Foreign keys (commentées pour éviter les erreurs)
            // Les relations existent mais sans contrainte stricte en base
        });
    }

    public function down()
    {
        Schema::dropIfExists('multiservices_cash_registers');
    }
}
