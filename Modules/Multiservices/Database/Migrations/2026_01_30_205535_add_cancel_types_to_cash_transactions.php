<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCancelTypesToCashTransactions extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE multiservices_cash_transactions 
                       MODIFY COLUMN type ENUM('opening', 'funding', 'funding_cancel', 'expense', 'expense_cancel', 'deposit', 'withdrawal', 'closing') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE multiservices_cash_transactions 
                       MODIFY COLUMN type ENUM('opening', 'funding', 'expense', 'deposit', 'withdrawal', 'closing') NOT NULL");
    }
}