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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id('submission_id');
            $table->unsignedBigInteger('event_id');
            $table->string('scholar_id', 7);
            $table->string('time_in_image_uuid')->nullable();
            $table->string('time_in_location')->nullable();
            $table->string('time_out_image_uuid')->nullable();
            $table->string('time_out_location')->nullable();
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->foreign('scholar_id')->references('scholar_id')->on('scholars')->onDelete('cascade');
            $table->unique(['event_id', 'scholar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};

