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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("menu_id");
            $table->boolean("is_view")->default(false);
            $table->boolean("is_add")->default(false);
            $table->boolean("is_edit")->default(false);
            $table->boolean("is_delete")->default(false);
            $table->timestamps();

            $table->foreign("role_id", "role_permissions_role_id_fk")->on("roles")->references("id");
            $table->foreign("menu_id", "role_permissions_menu_id_fk")->on("menus")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
