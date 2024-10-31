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
        Schema::create('schedules', function(Blueprint $table) {
            $table->id('schedule_id');
            $table->unsignedBigInteger('event_id');
            $table->date('date');
            $table->timestamps();
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
