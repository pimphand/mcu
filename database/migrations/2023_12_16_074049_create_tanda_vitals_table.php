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
        Schema::create('tanda_vitals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->boolean('keluhan_utama')->nullable();
            $table->string('keluhan_utama_text')->nullable();
            $table->boolean('riwayat_penyakit_sekarang')->nullable();
            $table->string('riwayat_penyakit_sekarang_text')->nullable();
            $table->boolean('riwayat_penyakit_terdahulu')->nullable();
            $table->string('riwayat_penyakit_terdahulu_text')->nullable();
            $table->boolean('alergi')->nullable();
            $table->string('alergi_text')->nullable();
            $table->boolean('merokok')->nullable();
            $table->boolean('konsumsi_alkohol')->nullable();
            $table->boolean('riwayat_trauma')->nullable();
            $table->string('riwayat_trauma_text')->nullable();
            $table->integer('tinggi_badan')->nullable();
            $table->integer('berat_badan')->nullable();
            $table->string('imt')->nullable();
            $table->integer('imt_nilai')->nullable();
            $table->integer('tekanan_darah')->nullable();
            $table->integer('frekuensi_nadi')->nullable();
            $table->integer('frekuensi_nafas')->nullable();
            $table->integer('suhu')->nullable();
            $table->boolean('ttv_diperiksa')->nullable();
            $table->boolean('ibu_hamil')->nullable();
            $table->boolean('vaksin_hepatitis')->nullable();
            $table->boolean('vaksin_tetanus')->nullable();
            $table->boolean('selesai')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by','tanda_vitals_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by','tanda_vitals_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('employee_id', 'tanda_vitals_employee_id_fk')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('participant_id', 'tanda_vitals_participant_id_fk')->references('id')->on('participants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanda_vitals');
    }
};
