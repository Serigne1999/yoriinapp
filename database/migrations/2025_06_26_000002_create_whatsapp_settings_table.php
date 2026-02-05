<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};