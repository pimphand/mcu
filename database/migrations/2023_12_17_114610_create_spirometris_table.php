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
        Schema::create('spirometris', function (Blueprint $table) {
            $table->id();
            $table->boolean('lainnya')->nullable();
            $table->boolean('mixed')->nullable();
            $table->boolean('normal')->nullable();
            $table->boolean('obstructive')->nullable();
            $table->boolean('restrictive')->nullable();
            $table->string('hasil')->nullable();
            $table->string('retriksi')->nullable();
            $table->string('obstruksif')->nullable();
            $table->boolean('selesai')->nullable();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by', 'spirometris_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'spirometris_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('participant_id', 'spirometris_participant_id_fk')->references('id')->on('participants')->nullOnDelete();
            $table->foreign('employee_id', 'spirometris_employee_id_fk')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spirometris');
    }
};
