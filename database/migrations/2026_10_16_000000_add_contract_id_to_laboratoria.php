<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laboratoria', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->nullable()->after('participant_id');
            $table->foreign('contract_id', 'laboratoria_contract_id_fk')->references('id')->on('contracts')->nullOnDelete();
        });

        // Isi data lama dari kontrak peserta agar filter kontrak langsung berlaku.
        DB::table('laboratoria')
            ->join('participants', 'participants.id', '=', 'laboratoria.participant_id')
            ->whereNull('laboratoria.contract_id')
            ->update(['laboratoria.contract_id' => DB::raw('participants.contract_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laboratoria', function (Blueprint $table) {
            $table->dropForeign('laboratoria_contract_id_fk');
            $table->dropColumn('contract_id');
        });
    }
};
