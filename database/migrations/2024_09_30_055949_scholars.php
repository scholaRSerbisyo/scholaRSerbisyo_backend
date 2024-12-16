<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scholars', function (Blueprint $table) {
            $table->string('scholar_id', 7)->primary();
            $table->uuid('profile_image_uuid')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->date('birthdate');
            $table->string('gender');
            $table->string('course');
            $table->integer('age');
            $table->string('mobilenumber');
            $table->string('yearlevel');
            $table->unsignedBigInteger('scholar_type_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('baranggay_id');
            $table->string('push_token')->nullable();
            $table->timestamps();

            $table->foreign('scholar_type_id')->references('scholar_type_id')->on('scholar_types')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('cascade');
            $table->foreign('baranggay_id')->references('baranggay_id')->on('baranggays')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('scholars');
    }
};

