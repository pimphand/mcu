<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() === 'local') {
            $roleID = Role::where('level', 1)->first()->id;
            \DB::table('users')->insert([
                [
                    'name' => 'Super Admin',
                    'email' => 'admin@admin.com',
                    'username' => 'admin',
                    'password' => \Hash::make('password'),
                    'role_id' => $roleID,
                    'is_active' => 1,
                ],
            ]);
        }
    }
}
