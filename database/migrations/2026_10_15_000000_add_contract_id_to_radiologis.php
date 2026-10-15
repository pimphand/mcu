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
        Schema::table('radiologis', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->nullable()->after('participant_id');
            $table->foreign('contract_id', 'radiologis_contract_id_fk')->references('id')->on('contracts')->nullOnDelete();
        });

        // Isi data lama dari kontrak peserta agar filter kontrak langsung berlaku.
        DB::table('radiologis')
            ->join('participants', 'participants.id', '=', 'radiologis.participant_id')
            ->whereNull('radiologis.contract_id')
            ->update(['radiologis.contract_id' => DB::raw('participants.contract_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radiologis', function (Blueprint $table) {
            $table->dropForeign('radiologis_contract_id_fk');
            $table->dropColumn('contract_id');
        });
    }
};
