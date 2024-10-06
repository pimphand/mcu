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
        Schema::create('rectals', function (Blueprint $table) {
            $table->id();
            $table->string('salmonella_thypi')->nullable();
            $table->string('shigella_sp')->nullable();
            $table->string('e_coli_pathogen')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->boolean('selesai')->nullable();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by', 'rectals_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'rectals_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('participant_id', 'rectals_participant_id_fk')->references('id')->on('participants')->nullOnDelete();
            $table->foreign('employee_id', 'rectals_employee_id_fk')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rectals');
    }
};
