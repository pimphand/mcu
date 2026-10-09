<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah menu "Menu" di grup Administrator + hak akses untuk role Administrator.
     * Aman dijalankan berulang (idempotent).
     */
    public function up(): void
    {
        $parentId = DB::table('menus')
            ->whereNull('parent_id')
            ->where('name', 'Administrator')
            ->value('id');

        $menuId = DB::table('menus')->where('url', '/menu')->value('id');

        if (!$menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'parent_id' => $parentId,
                'name' => 'Menu',
                'icon' => 'list',
                'url' => '/menu',
                'sort_order' => ((int) DB::table('menus')->max('sort_order')) + 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // hak akses hanya untuk role Administrator (level 1)
        foreach (DB::table('roles')->where('level', 1)->pluck('id') as $roleId) {
            $exists = DB::table('permissions')
                ->where('role_id', $roleId)
                ->where('menu_id', $menuId)
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'role_id' => $roleId,
                    'menu_id' => $menuId,
                    'is_view' => 1,
                    'is_add' => 1,
                    'is_edit' => 1,
                    'is_delete' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuIds = DB::table('menus')->where('url', '/menu')->pluck('id');

        if ($menuIds->isNotEmpty()) {
            DB::table('permissions')->whereIn('menu_id', $menuIds)->delete();
            DB::table('menus')->whereIn('id', $menuIds)->delete();
        }
    }
};
