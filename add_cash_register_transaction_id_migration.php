<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashRegisterTransactionIdToMultiserviceTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('multiservice_transactions', function (Blueprint $table) {
            $table->unsignedInteger('cash_register_transaction_id')->nullable()->after('id');
            $table->foreign('cash_register_transaction_id')
                  ->references('id')
                  ->on('cash_register_transactions')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('multiservice_transactions', function (Blueprint $table) {
            $table->dropForeign(['cash_register_transaction_id']);
            $table->dropColumn('cash_register_transaction_id');
        });
    }
}
