<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ditulis idempotent: MySQL menjalankan DDL di luar transaksi, jadi kalau
     * satu langkah gagal sisa langkahnya bisa tertinggal separuh.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('validate_doctors', 'contract_id')) {
            Schema::table('validate_doctors', function (Blueprint $table) {
                $table->unsignedBigInteger('contract_id')->nullable()->after('client_id');
            });
        }

        $hasContractForeignKey = collect(Schema::getForeignKeys('validate_doctors'))
            ->contains(fn (array $foreignKey) => ($foreignKey['columns'] ?? []) === ['contract_id']);

        if (! $hasContractForeignKey) {
            Schema::table('validate_doctors', function (Blueprint $table) {
                $table->foreign('contract_id', 'validate_doctors_contract_id_fk')
                    ->references('id')->on('contracts')->nullOnDelete();
            });
        }

        // Isi data lama dari kontrak peserta yang kodenya sama dengan no_mcu,
        // supaya daftar validasi dokter bisa langsung difilter per kontrak.
        DB::table('validate_doctors')
            ->join('participants', 'participants.code', '=', 'validate_doctors.no_mcu')
            ->whereNull('validate_doctors.contract_id')
            ->update(['validate_doctors.contract_id' => DB::raw('participants.contract_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('validate_doctors', 'contract_id')) {
            return;
        }

        $hasContractForeignKey = collect(Schema::getForeignKeys('validate_doctors'))
            ->contains(fn (array $foreignKey) => ($foreignKey['columns'] ?? []) === ['contract_id']);

        Schema::table('validate_doctors', function (Blueprint $table) use ($hasContractForeignKey) {
            if ($hasContractForeignKey) {
                $table->dropForeign('validate_doctors_contract_id_fk');
            }

            $table->dropColumn('contract_id');
        });
    }
};
