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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by','contracts_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by','contracts_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('client_id','contracts_client_id_fk')->references('id')->on('clients')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
