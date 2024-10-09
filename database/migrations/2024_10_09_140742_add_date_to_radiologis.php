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
        Schema::table('radiologis', function (Blueprint $table) {
            $table->date('date_mcu')->nullable();
            $table->string('diperiksa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radiologis', function (Blueprint $table) {
            $table->dropColumn('date_mcu');
            $table->dropColumn('diperiksa');
        });
    }
};
