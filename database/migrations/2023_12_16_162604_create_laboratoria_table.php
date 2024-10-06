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
        Schema::create('laboratoria', function (Blueprint $table) {
            $table->id();
            $table->string('hemoglobin')->nullable();
            $table->string('hematokrit')->nullable();
            $table->string('lekosit')->nullable();
            $table->string('trombosit')->nullable();
            $table->string('eritrosit')->nullable();
            $table->string('basofil')->nullable();
            $table->string('eosinofil')->nullable();
            $table->string('batang')->nullable();
            $table->string('segmen')->nullable();
            $table->string('limfosit')->nullable();
            $table->string('monosit')->nullable();

            $table->string('sgot')->nullable();
            $table->string('sgpt')->nullable();
            $table->string('ureum')->nullable();
            $table->string('creatinin')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->boolean('selesai')->nullable();

            $table->string('glukosa_puasa')->nullable();
            $table->string('cholesterol_total')->nullable();
            $table->string('asam_urat')->nullable();
            $table->string('glukosa_sewaktu')->nullable();
            $table->string('trigliserida')->nullable();
            $table->string('hdl_cholesterol')->nullable();
            $table->string('ldl_cholestero')->nullable();
            
            $table->string('reduksi')->nullable();
            $table->string('berat_jenis')->nullable();
            $table->string('ph_reaksi')->nullable();
            $table->string('warna')->nullable();
            $table->string('kekeruhan')->nullable();
            $table->string('urobilinogen')->nullable();
            $table->string('bilirubin')->nullable();
            $table->string('eritrosit_urine')->nullable();
            $table->string('keton')->nullable();
            $table->string('protein')->nullable();
            $table->string('sedimen_epitel')->nullable();
            $table->string('sedimen_eritrosit')->nullable();
            $table->string('sedimen_leukosit')->nullable();
            $table->string('sedimen_bakteri')->nullable();
            $table->string('sedimen_kristal')->nullable();

            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by', 'laboratoria_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'laboratoria_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('participant_id', 'laboratoria_participant_id_fk')->references('id')->on('participants')->nullOnDelete();
            $table->foreign('employee_id', 'laboratoria_employee_id_fk')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratoria');
    }
};
