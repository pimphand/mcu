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
        Schema::create('ekgs', function (Blueprint $table) {
            $table->id();
            $table->boolean('takikardi')->nullable();
            $table->boolean('bradikardi')->nullable();
            $table->boolean('aritmia')->nullable();
            $table->boolean('aresst')->nullable();
            $table->string('penemuan_lain')->nullable();
            $table->boolean('keadaan_jantung_normal')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->boolean('selesai')->nullable();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by', 'ekgs_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'ekgs_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('participant_id', 'ekgs_participant_id_fk')->references('id')->on('participants')->nullOnDelete();
            $table->foreign('employee_id', 'ekgs_employee_id_fk')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekgs');
    }
};
