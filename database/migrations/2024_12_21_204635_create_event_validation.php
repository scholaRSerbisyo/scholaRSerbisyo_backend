<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_validations', function (Blueprint $table) {
            $table->id('event_validation_id');
            $table->unsignedBigInteger('admin_id');
            $table->string('admin_type_name');
            $table->string('event_image_uuid');
            $table->string('event_name');
            $table->text('description');
            $table->date('date');
            $table->time('time_from');
            $table->time('time_to');
            $table->string('location');
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->unsignedBigInteger('event_type_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('baranggay_id')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')
                ->references('admin_id')
                ->on('admins')
                ->onDelete('cascade');

            $table->foreign('event_type_id')
                ->references('event_type_id')
                ->on('event_types')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('school_id')
                ->references('school_id')
                ->on('schools')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('baranggay_id')
                ->references('baranggay_id')
                ->on('baranggays')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Composite unique key
            $table->unique(['admin_id', 'event_type_id', 'school_id', 'baranggay_id'], 'event_validation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_validations');
    }
};