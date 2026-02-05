<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeIdToMultiserviceTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('multiservice_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->after('operator');
            $table->foreign('type_id')->references('id')->on('multiservices_transaction_types')->onDelete('set null');
            $table->index('type_id');
        });
    }

    public function down()
    {
        Schema::table('multiservice_transactions', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropIndex(['type_id']);
            $table->dropColumn('type_id');
        });
    }
}
