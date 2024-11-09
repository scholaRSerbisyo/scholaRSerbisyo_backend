<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function(Blueprint $table) {
            $table->unsignedBigInteger('scholar_id');
            $table->string('submission_image_uuid');
            $table->time('time_in');
            $table->time('time_out')->nullable();
            $table->foreign('scholar_id')->references('scholar_id')->on('scholars')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};
