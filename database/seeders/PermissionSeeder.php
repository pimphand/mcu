<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create permission
        $exceptAdmin = ['MCU IN/OUT','Master Divisi', 'Master Karyawan', 'Master Department', 'Master Data'];
        $exceptClient = ['Role', 'Client'];
        $exceptKaryawan = ['MCU IN/OUT','Administrator','Role', 'Client', 'User', 'Master Divisi', 'Master Karyawan', 'Master Department', 'Master Data'];
        $permission = [];
        $roles = \DB::table('roles')->get();
        $menus = \DB::table('menus')->get();
        foreach ($roles as $key => $value) {
            $roleID = $value->id;
            $level = $value->level;
            foreach ($menus as $key => $value) {
                if ($level == 1 && !in_array($value->name, $exceptAdmin)) {
                    $permission[] = [
                        'role_id' => $roleID,
                        'menu_id' => $value->id,
                        'is_view' => true,
                        'is_add' => true,
                        'is_edit' => true,
                        'is_delete' => true,
                    ];
                }

                if ($level == 2 && !in_array($value->name, $exceptClient)) {
                    $permission[] = [
                        'role_id' => $roleID,
                        'menu_id' => $value->id,
                        'is_view' => true,
                        'is_add' => true,
                        'is_edit' => true,
                        'is_delete' => true,
                    ];
                }

                if ($level == 3 && !in_array($value->name, $exceptKaryawan)) {
                    $permission[] = [
                        'role_id' => $roleID,
                        'menu_id' => $value->id,
                        'is_view' => true,
                        'is_add' => true,
                        'is_edit' => true,
                        'is_delete' => true,
                    ];
                }
            }
        }

        \DB::table('permissions')->insert($permission);
    }
}
