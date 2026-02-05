<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiservicesCashTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('multiservices_cash_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cash_register_id')->unsigned();
            
            $table->enum('type', ['opening', 'closing', 'deposit', 'withdrawal', 'adjustment', 'funding'])->comment('opening=ouverture, funding=alimentation, deposit=dépôt client, withdrawal=retrait client');
            $table->decimal('amount', 20, 2);
            
            $table->decimal('balance_before', 20, 2);
            $table->decimal('balance_after', 20, 2);
            
            $table->integer('multiservice_transaction_id')->unsigned()->nullable()->comment('Lien vers transaction client');
            $table->text('notes')->nullable();
            
            $table->integer('created_by')->unsigned();
            $table->timestamps();
            
            // Index
            $table->index('cash_register_id', 'ms_cash_tx_reg_idx');
            $table->index('type', 'ms_cash_tx_type_idx');
            $table->index('multiservice_transaction_id', 'ms_cash_tx_ms_tx_idx');
            $table->index('created_at', 'ms_cash_tx_created_idx');
            
            // Foreign keys temporairement désactivées
            // Les relations existent dans les models
        });
    }

    public function down()
    {
        Schema::dropIfExists('multiservices_cash_transactions');
    }
}