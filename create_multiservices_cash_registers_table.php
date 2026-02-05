<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiservicesCashRegistersTable extends Migration
{
    public function up()
    {
        Schema::create('multiservices_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('location_id');
            $table->decimal('balance', 20, 4)->default(0); // Solde actuel
            $table->decimal('opening_balance', 20, 4)->default(0); // Solde d'ouverture du jour
            $table->date('last_opening_date')->nullable(); // Dernière date d'ouverture
            $table->timestamps();

            // Index
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');
            $table->unique(['business_id', 'location_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('multiservices_cash_registers');
    }
}
