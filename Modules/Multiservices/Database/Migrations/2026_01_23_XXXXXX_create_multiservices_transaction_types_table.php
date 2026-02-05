<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiservicesTransactionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multiservices_transaction_types', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->string('name', 100); // Ex: "Dépôt", "Retrait", "Paiement Facture"
            $table->string('code', 50)->unique(); // Ex: "deposit", "withdrawal", "bill_payment"
            $table->string('icon', 50)->nullable(); // Ex: "fa-arrow-down", "fa-money"
            $table->string('color', 20)->default('#3c8dbc'); // Couleur hex
            $table->boolean('is_active')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            // Index
            $table->index('business_id');
            $table->index(['business_id', 'is_active']);
            
            // Foreign key
            $table->foreign('business_id')
                  ->references('id')->on('business')
                  ->onDelete('cascade');
        });

        // Insérer les types par défaut
        DB::table('multiservices_transaction_types')->insert([
            [
                'business_id' => DB::raw('(SELECT id FROM business LIMIT 1)'),
                'name' => 'Dépôt',
                'code' => 'deposit',
                'icon' => 'fa-arrow-down',
                'color' => '#28a745',
                'is_active' => 1,
                'description' => 'Dépôt d\'argent sur un compte opérateur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => DB::raw('(SELECT id FROM business LIMIT 1)'),
                'name' => 'Retrait',
                'code' => 'withdrawal',
                'icon' => 'fa-arrow-up',
                'color' => '#ffc107',
                'is_active' => 1,
                'description' => 'Retrait d\'argent depuis un compte opérateur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => DB::raw('(SELECT id FROM business LIMIT 1)'),
                'name' => 'Transfert',
                'code' => 'transfer',
                'icon' => 'fa-exchange',
                'color' => '#17a2b8',
                'is_active' => 1,
                'description' => 'Transfert d\'argent entre comptes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('multiservices_transaction_types');
    }
}