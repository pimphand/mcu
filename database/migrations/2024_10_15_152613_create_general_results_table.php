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
        Schema::create('general_results', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->index();
            $table->string('name')->index();
            $table->string('mcu_id')->index();
            $table->foreignId('participant_id')->constrained();
            $table->text('result')->nullable();
            $table->dateTime('mcu_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_results');
    }
};
