<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('radiologis', function (Blueprint $table) {
            $table->id();
            $table->text('cor')->nullable();
            $table->text('diafragma_sinus')->nullable();
            $table->text('pulmo')->nullable();
            $table->text('kesan')->nullable();
            $table->boolean('selesai')->nullable();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by', 'radiologis_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'radiologis_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('participant_id', 'radiologis_participant_id_fk')->references('id')->on('participants')->nullOnDelete();
            $table->foreign('employee_id', 'radiologis_employee_id_fk')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiologis');
    }
};
