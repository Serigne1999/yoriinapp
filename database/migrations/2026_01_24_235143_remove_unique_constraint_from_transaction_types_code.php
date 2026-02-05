<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueConstraintFromTransactionTypesCode extends Migration
{
    public function up()
    {
        Schema::table('multiservices_transaction_types', function (Blueprint $table) {
            $table->dropUnique('multiservices_transaction_types_code_unique');
        });
    }

    public function down()
    {
        Schema::table('multiservices_transaction_types', function (Blueprint $table) {
            $table->unique('code');
        });
    }
}
