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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('nik')->nullable();
            $table->string('nama')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->text('alamat')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->string('golongan_darah')->nullable();
            $table->string('rhesus')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('telp')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->string('status_karyawan')->nullable();
            $table->string('unit')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('profesi')->nullable();
            $table->string('profesi_detail')->nullable();
            $table->string('warga_negara')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('ttd')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('client_id', 'employees_client_id_fk')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('created_by', 'employees_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'employees_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
