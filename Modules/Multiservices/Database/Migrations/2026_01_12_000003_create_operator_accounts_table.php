<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperatorAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('operator_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->string('operator'); // wave, orange_money, free_money
            $table->string('account_name');
            $table->string('account_number');
            $table->decimal('balance', 20, 2)->default(0);
            $table->decimal('initial_balance', 20, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->index(['business_id', 'operator']);
        });

        // Table pour l'historique des mouvements
        Schema::create('operator_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_account_id');
            $table->enum('type', ['deposit', 'withdrawal', 'adjustment']); // dépôt, retrait, ajustement manuel
            $table->decimal('amount', 20, 2);
            $table->decimal('balance_before', 20, 2);
            $table->decimal('balance_after', 20, 2);
            $table->string('reference')->nullable(); // référence transaction multiservices si applicable
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('operator_account_id')->references('id')->on('operator_accounts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_account_transactions');
        Schema::dropIfExists('operator_accounts');
    }
}
