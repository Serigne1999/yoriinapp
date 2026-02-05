<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddExpenseTypeToCashTransactions extends Migration
{
    public function up()
    {
        // Modifier l'ENUM pour ajouter 'expense'
        DB::statement("ALTER TABLE multiservices_cash_transactions 
                       MODIFY COLUMN type ENUM('opening', 'funding', 'expense', 'deposit', 'withdrawal', 'closing') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE multiservices_cash_transactions 
                       MODIFY COLUMN type ENUM('opening', 'funding', 'deposit', 'withdrawal', 'closing') NOT NULL");
    }
}