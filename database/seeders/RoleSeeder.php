<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() === 'local') {
            \DB::table('roles')->insert([
                [
                    'level' => 1,
                    'name' => 'Administrator',
                    'is_active' => 1
                ],
                [
                    'level' => 2,
                    'name' => 'Admin Client',
                    'is_active' => 1
                ],
                [
                    'level' => 3,
                    'name' => 'Admin Karyawan',
                    'is_active' => 1
                ],
                [
                    'level' => 4,
                    'name' => 'Peserta',
                    'is_active' => 1
                ],
            ]);
        }
    }
}
