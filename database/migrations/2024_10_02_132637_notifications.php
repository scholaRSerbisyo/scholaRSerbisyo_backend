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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->string('event_title');
            $table->string('message');
            $table->boolean('broadcast');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('scholar_id');
            $table->timestamps();
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('scholar_id')->references('scholar_id')->on('scholars')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
