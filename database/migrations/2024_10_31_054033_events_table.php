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
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->string('event_image_uuid');
            $table->string('event_name');
            $table->string('description');
            $table->date('date');
            $table->time('time');
            $table->string('location');
            $table->string('status');
            $table->string('admin');
            $table->unsignedBigInteger('event_type_id');
            $table->json('submissions')->nullable();
            $table->timestamps();
            $table->foreign('event_type_id')->references('event_type_id')->on('event_types')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
