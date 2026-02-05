<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperatorsTable extends Migration
{
    public function up()
    {
        Schema::create('multiservice_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->string('name'); // Nom affiché (ex: "Wave", "Orange Money")
            $table->string('code'); // Code (ex: "wave", "orange_money")
            $table->string('color', 7)->default('#3b82f6'); // Couleur hex
            $table->string('icon')->nullable(); // Classe d'icône (ex: "fa-mobile")
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0); // Ordre d'affichage
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->unique(['business_id', 'code']); // Unique par business
            $table->index(['business_id', 'is_active']);
        });

        // Insérer les opérateurs par défaut pour les business existants
        $businesses = DB::table('business')->pluck('id');
        
        foreach ($businesses as $business_id) {
            $operators = [
                ['business_id' => $business_id, 'name' => 'Wave', 'code' => 'wave', 'color' => '#01c38d', 'icon' => 'fa-mobile', 'display_order' => 1],
                ['business_id' => $business_id, 'name' => 'Orange Money', 'code' => 'orange_money', 'color' => '#ff7900', 'icon' => 'fa-mobile-alt', 'display_order' => 2],
                ['business_id' => $business_id, 'name' => 'Free Money', 'code' => 'free_money', 'color' => '#ed1c24', 'icon' => 'fa-mobile-alt', 'display_order' => 3],
            ];
            
            foreach ($operators as $operator) {
                DB::table('multiservice_operators')->insert(array_merge($operator, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('multiservice_operators');
    }
}