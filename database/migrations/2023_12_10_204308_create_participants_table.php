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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('nik');
            $table->string('name');
            $table->string('gender');
            $table->date('birthday');
            $table->string('phone')->nullable();
            $table->string('status')->nullable();

            $table->string('packet_name')->nullable();
            $table->boolean('packet_a')->default(false);
            $table->boolean('packet_b')->default(false);
            $table->boolean('packet_c')->default(false);
            $table->boolean('packet_d')->default(false);
            $table->boolean('packet_e')->default(false);
            $table->boolean('packet_f')->default(false);

            $table->string('plan_name')->nullable();
            $table->boolean('plan_u')->default(false);
            $table->boolean('plan_a')->default(false);
            $table->boolean('plan_e')->default(false);
            $table->boolean('plan_s')->default(false);
            $table->boolean('plan_r')->default(false);
            $table->boolean('lab_special')->default(false);

            $table->string('register_number')->nullable();
            $table->timestamp('register_date')->nullable();

            $table->unsignedBigInteger('divisi_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by', 'participants_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'participants_updated_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('divisi_id', 'participants_divisi_id_fk')->references('id')->on('divisis')->nullOnDelete();
            $table->foreign('department_id', 'department_id_fk')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('user_id', 'participants_user_id_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('client_id', 'participants_client_id_fk')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('contract_id', 'participants_contract_id_fk')->references('id')->on('contracts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
