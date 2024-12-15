<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->string('push_token')->nullable();
        });
    }

    public function down()
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->dropColumn('push_token');
        });
    }
};
