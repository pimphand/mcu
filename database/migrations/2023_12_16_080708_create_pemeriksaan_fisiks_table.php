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
        Schema::create('pemeriksaan_fisiks', function (Blueprint $table) {
            $table->id();
            $table->string('keadaan_umum')->nullable();
            $table->boolean('kepala')->nullable();
            $table->string('kepala_text')->nullable();
            $table->string('hidung')->nullable();
            $table->string('mata')->nullable();
            $table->string('telinga')->nullable();
            $table->string('kelainan_telinga')->nullable();
            $table->string('pupil')->nullable();
            $table->string('visus')->nullable();
            $table->string('buta_warna')->nullable();
            $table->text('gigi')->nullable();
            $table->string('kode_gigi')->nullable();
            $table->string('bibir')->nullable();
            $table->string('lidah')->nullable();
            $table->boolean('tenggorokan')->nullable();
            $table->string('tenggorokan_text')->nullable();
            $table->string('faring')->nullable();
            $table->string('leher_kgb')->nullable();
            $table->string('leher_jvp')->nullable();
            $table->string('jantung_inspeksi')->nullable();
            $table->string('jantung_auskultasi')->nullable();
            $table->string('jantung_palpasi')->nullable();
            $table->string('jantung_perkusi')->nullable();

            $table->string('paru_inspeksi')->nullable();
            $table->string('paru_auskultasi_vasikuler')->nullable();
            $table->string('paru_auskultasi_ronkhi')->nullable();
            $table->string('paru_auskultasi_wheezing')->nullable();
            $table->string('paru_palpasi')->nullable();
            $table->string('paru_perkusi')->nullable();

            $table->string('abdomen_inspeksi')->nullable();
            $table->string('abdomen_auskultasi')->nullable();
            $table->string('abdomen_palpasi')->nullable();
            $table->string('abdomen_perkusi')->nullable();

            $table->string('reflex_fisiologis_atas')->nullable();
            $table->string('reflex_fisiologis_bawah')->nullable();
            $table->string('reflex_phatologis_atas')->nullable();
            $table->string('reflex_phatologis_bawah')->nullable();

            $table->boolean('ekg_tidak_diperiksa')->nullable();
            $table->boolean('ekg_bdn')->nullable();
            $table->text('ekg_text')->nullable();

            $table->boolean('neurologis_tidak_diperiksa')->nullable();
            $table->boolean('neurologis_bdn')->nullable();
            $table->text('neurologis_text')->nullable();

            $table->boolean('fisik_diperiksa')->nullable();
            $table->boolean('selesai')->nullable();

            $table->boolean('visus_diperiksa')->nullable();
            $table->boolean('selesai_visus')->nullable();
            $table->boolean('lab_diperiksa')->nullable();
            $table->boolean('radiologi_diperiksa')->nullable();
            $table->boolean('audiometri_diperiksa')->nullable();
            $table->boolean('ekg_diperiksa')->nullable();
            $table->boolean('spiro_diperiksa')->nullable();
            $table->boolean('rectal_diperiksa')->nullable();
            $table->string('kesimpulan')->nullable();
            $table->text('saran')->nullable();

            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by', 'pemeriksaan_fisiks_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'pemeriksaan_fisiks_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('participant_id', 'pemeriksaan_fisiks_participant_id_fk')->references('id')->on('participants')->nullOnDelete();
            $table->foreign('employee_id', 'pemeriksaan_fisiks_employee_id_fk')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_fisiks');
    }
};
