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
        Schema::create('event_attendances', function (Blueprint $table) {
            $table->id('event_attendance_id');
            $table->unsignedBigInteger('event_id');
            $table->string('scholar_id', 7);
            $table->string('submission_image_uuid')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->timestamps();
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->foreign('scholar_id')->references('scholar_id')->on('scholars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_attendances');
    }
};
