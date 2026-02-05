<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('multiservice_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            
            $table->enum('operator', ['wave', 'orange_money', 'ria', 'moneygram', 'western_union', 'autres']);
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'transfer', 'all'])->default('all');
            $table->enum('commission_type', ['fixed', 'percentage']);
            $table->decimal('commission_value', 10, 4);
            
            // Plages de montants
            $table->decimal('min_amount', 20, 4)->nullable();
            $table->decimal('max_amount', 20, 4)->nullable();
            
            // Limites de commission
            $table->decimal('min_commission', 10, 4)->nullable();
            $table->decimal('max_commission', 10, 4)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            
            // Indexes
            $table->index('business_id');
            $table->index(['business_id', 'operator', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('multiservice_commissions');
    }
};
