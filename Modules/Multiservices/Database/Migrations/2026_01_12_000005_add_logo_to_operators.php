<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogoToOperators extends Migration
{
    public function up()
    {
        Schema::table('multiservice_operators', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('icon');
        });
    }

    public function down()
    {
        Schema::table('multiservice_operators', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
}
