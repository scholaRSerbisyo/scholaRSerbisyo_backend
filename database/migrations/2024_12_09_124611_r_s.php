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
            $table->foreignId('scholar_id')->constrained('scholars', 'scholar_id');
            $table->foreignId('submission_id')->constrained('submissions', 'submission_id');
            $table->foreignId('event_id')->constrained('events', 'event_id');
            $table->year('year');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
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
