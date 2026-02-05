<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('multiservice_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('location_id')->nullable();
            
            $table->enum('operator', ['wave', 'orange_money', 'ria', 'moneygram', 'western_union', 'autres']);
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'transfer']);
            
            // Informations expéditeur
            $table->string('sender_name')->nullable();
            $table->string('sender_phone', 20)->nullable();
            $table->string('sender_id_number', 50)->nullable();
            
            // Informations destinataire
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone', 20)->nullable();
            $table->string('receiver_id_number', 50)->nullable();
            
            // Montants
            $table->decimal('amount', 20, 4);
            $table->decimal('fee', 20, 4)->default(0);
            $table->decimal('total', 20, 4);
            $table->decimal('profit', 20, 4)->default(0);
            
            $table->string('reference_number')->unique();
            $table->enum('status', ['pending', 'completed', 'canceled', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('payment_method')->nullable();
            
            // Complétion
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('completed_by')->nullable();
            
            // Annulation
            $table->timestamp('canceled_at')->nullable();
            $table->unsignedInteger('canceled_by')->nullable();
            $table->text('cancel_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('business_id');
            $table->index('operator');
            $table->index('transaction_type');
            $table->index('status');
            $table->index(['business_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('multiservice_transactions');
    }
};
