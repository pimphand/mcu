<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Divisi;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() === 'local') {
            $userAdmin = User::first();
            $role = Role::where('level', 2)->first();

            // add Client
            $clients = [
                'code' => 'C-00001',
                'name' => 'Client 1',
                'address' => 'Jln U 27, Jakarta Barat',
                'created_by' => $userAdmin->id
            ];

            Client::create($clients);

            $client = Client::first();

            $contact = ['code' => 'K-00001', 'name' => 'MCU Karyawan 2024', 'client_id' => $client->id];
            Contract::create($contact);

            $divisi = ['code' => 'DV-00001', 'name' => 'Divisi 1', 'client_id' => $client->id];
            Divisi::create($divisi);

            $department = ['code' => 'DP-00001', 'name' => 'Department 1', 'client_id' => $client->id];
            Department::create($department);

            $employee = ['code' => 'KR-00001', 'nik' => '12345', 'nama' => 'Dr. Agus Salim', 'client_id' => $client->id];
            Employee::create($employee);

            $user = [
                'name' => 'Rumah Sakit Bakti',
                'username' => 'client',
                'email' => fake()->email(),
                'password' => bcrypt('Querty123!'),
                'role_id' => $role->id,
                'client_id' => $client->id,
                'is_active' => 1
            ];
            User::create($user);

        }
    }
}
