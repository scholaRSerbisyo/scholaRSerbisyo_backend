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
        Schema::create('replies', function (Blueprint $table) {
            $table->id('reply_id');
            $table->string('reply_text');
            $table->unsignedBigInteger('comment_id');
            $table->string('scholar_id', 7);
            $table->timestamps();
            $table->foreign('comment_id')->references('comment_id')->on('comments')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('scholar_id')->references('scholar_id')->on('scholars')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};
