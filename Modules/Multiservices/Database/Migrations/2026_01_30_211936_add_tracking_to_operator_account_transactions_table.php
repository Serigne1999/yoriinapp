<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackingToOperatorAccountTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('operator_account_transactions', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('created_by');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    public function down()
    {
        Schema::table('operator_account_transactions', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
}