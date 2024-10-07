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
        Schema::create('scholars', function (Blueprint $table) {
            $table->id('scholar_id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('age');
            $table->string('address');
            $table->string('mobilenumber', 11);
            $table->unsignedBigInteger('scholar_type_id')->default(1);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('baranggay_id');
            $table->timestamps();
            $table->foreign('scholar_type_id')->references('scholar_type_id')->on('scholar_types')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('baranggay_id')->references('baranggay_id')->on('baranggays')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholars');
    }
};
