<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() === 'local') {
            $order = 1;
            $data = [
                [
                    'parent_id' => null,
                    'name' => 'Home',
                    'icon' => 'home',
                    'url' => '/',
                    'sort_order' => $order++,
                    'is_active' => 1,
                ],
                [
                    'parent_id' => null,
                    'name' => 'MCU IN/OUT',
                    'icon' => 'log-in',
                    'url' => '/mcu-in-out',
                    'sort_order' => $order++,
                    'is_active' => 1,
                ], [
                    'parent_id' => null,
                    'name' => 'MCU',
                    'icon' => 'folder-plus',
                    'url' => '#',
                    'sort_order' => $order++,
                    'is_active' => 1,
                ], [
                    'parent_id' => null,
                    'name' => 'Laporan',
                    'icon' => 'file-text',
                    'url' => '#',
                    'sort_order' => $order++,
                    'is_active' => 1,
                ], [
                    'parent_id' => null,
                    'name' => 'Master Data',
                    'icon' => 'database',
                    'url' => '#',
                    'sort_order' => $order++,
                    'is_active' => 1,
                ],
                [
                    'parent_id' => null,
                    'name' => 'Administrator',
                    'icon' => 'monitor',
                    'url' => '#',
                    'sort_order' => $order++,
                    'is_active' => 1,
                ],
            ];
            \DB::table('menus')->insert($data);

            $parentMenu = \DB::table('menus')->where('url', '#')->get();
            foreach ($parentMenu as $key => $value) {
                if ($value->name == 'Master Data') {
                    $data = [
                        [
                            'parent_id' => $value->id,
                            'name' => 'Master Department',
                            'icon' => 'circle',
                            'url' => '/department',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ], [
                            'parent_id' => $value->id,
                            'name' => 'Master Divisi',
                            'icon' => 'circle',
                            'url' => '/divisi',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ], [
                            'parent_id' => $value->id,
                            'name' => 'Master Karyawan',
                            'icon' => 'circle',
                            'url' => '/employee',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                    ];
                    \DB::table('menus')->insert($data);
                }

                if ($value->name == 'Administrator') {
                    $data = [
                        [
                            'parent_id' => $value->id,
                            'name' => 'Role',
                            'icon' => 'circle',
                            'url' => '/role',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        // [
                        //     'parent_id' => $value->id,
                        //     'name' => 'Menu',
                        //     'icon' => 'circle',
                        //     'url' => '/menu',
                        //     'sort_order' => $order++,
                        //     'is_active' => 1,
                        // ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Client',
                            'icon' => 'circle',
                            'url' => '/client',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'User',
                            'icon' => 'circle',
                            'url' => '/user',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                    ];
                    \DB::table('menus')->insert($data);
                }

                if ($value->name == 'MCU') {
                    $data = [
                        [
                            'parent_id' => $value->id,
                            'name' => 'Data Peserta',
                            'icon' => 'circle',
                            'url' => '/participant',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Register',
                            'icon' => 'circle',
                            'url' => '/participant-register',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Print Hasil MCU',
                            'icon' => 'circle',
                            'url' => '/participant-print-mcu',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Print Identitas',
                            'icon' => 'circle',
                            'url' => '/participant-print-identitas',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Print Peserta',
                            'icon' => 'circle',
                            'url' => '/participant-print',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Kartu Peserta',
                            'icon' => 'circle',
                            'url' => '/participant-kartu',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Sticker Peserta',
                            'icon' => 'circle',
                            'url' => '/participant-sticker',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Upload Validasi Dokter',
                            'icon' => 'circle',
                            'url' => '/participant-upload-validasi-dokter',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Upload Hasil EKG',
                            'icon' => 'circle',
                            'url' => '/participant-upload-ekg',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Upload Hasil Audiometri',
                            'icon' => 'circle',
                            'url' => '/participant-upload-audiometri',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Upload Hasil Spiro',
                            'icon' => 'circle',
                            'url' => '/participant-upload-spiro',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Validasi Dokter',
                            'icon' => 'circle',
                            'url' => '/participant-validasi-dokter',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Upload Hasil Laboratorium',
                            'icon' => 'circle',
                            'url' => '/participant-upload-laboratorium',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Upload Hasil Radiologi',
                            'icon' => 'circle',
                            'url' => '/participant-upload-radilogi',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Upload Hasil Umum',
                            'icon' => 'circle',
                            'url' => '/participant-upload-umum',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                    ];
                    \DB::table('menus')->insert($data);
                }

                if ($value->name == 'Laporan') {
                    $data = [
                        [
                            'parent_id' => $value->id,
                            'name' => 'Rekap Register',
                            'icon' => 'circle',
                            'url' => '/report-recap-register',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Data Register',
                            'icon' => 'circle',
                            'url' => '/report-data-register',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Progress',
                            'icon' => 'circle',
                            'url' => '/report-progress',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Hasil',
                            'icon' => 'circle',
                            'url' => '/report-hasil',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Rekap Invoice',
                            'icon' => 'circle',
                            'url' => '/report-recap-invoice',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Double Register',
                            'icon' => 'circle',
                            'url' => '/report-double-register',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Data Invoice',
                            'icon' => 'circle',
                            'url' => '/report-data-invoice',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Data Register Kurang',
                            'icon' => 'circle',
                            'url' => '/report-data-register-kurang',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Ibu Hamil',
                            'icon' => 'circle',
                            'url' => '/report-ibu-hamil',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Pasien Sprio',
                            'icon' => 'circle',
                            'url' => '/report-pasien-spiro',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Pasien Rectal',
                            'icon' => 'circle',
                            'url' => '/report-pasien-rectal',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Pasien EKG',
                            'icon' => 'circle',
                            'url' => '/report-pasien-ekg',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Pasien Audiometri',
                            'icon' => 'circle',
                            'url' => '/report-pasien-audiometri',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Pasien Indikasi Unfit',
                            'icon' => 'circle',
                            'url' => '/report-indikasi-unfit',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Pasien Disabilitas',
                            'icon' => 'circle',
                            'url' => '/report-pasien-disabilitas',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Tensi > 140',
                            'icon' => 'circle',
                            'url' => '/report-tensi',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Grafik',
                            'icon' => 'circle',
                            'url' => '/report-grafik',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Statistik',
                            'icon' => 'circle',
                            'url' => '/report-statistik',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Cari Hasil',
                            'icon' => 'circle',
                            'url' => '/report-cari-hasil',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                        [
                            'parent_id' => $value->id,
                            'name' => 'Hasil per MCU',
                            'icon' => 'circle',
                            'url' => '/report-hasil-per-mcu',
                            'sort_order' => $order++,
                            'is_active' => 1,
                        ],
                    ];
                    \DB::table('menus')->insert($data);
                }
            }
        }
    }
}
