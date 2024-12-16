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
        Schema::create('return_services', function (Blueprint $table) {
            $table->id('return_service_id');
            $table->string('scholar_id', 7);
            $table->foreignId('submission_id')->constrained('submissions', 'submission_id');
            $table->foreignId('event_id')->constrained('events', 'event_id');
            $table->year('year');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('scholar_id')->references('scholar_id')->on('scholars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_services');
    }
};

