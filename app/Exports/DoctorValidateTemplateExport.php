<?php

namespace App\Exports;

use App\Imports\DoctorValidateImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DoctorValidateTemplateExport implements FromArray, WithHeadings
{
    /**
     * Judul kolom diambil dari import supaya template dan import selalu cocok.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return DoctorValidateImport::HEADINGS;
    }

    /**
     * Baris contoh. NO_MCU sengaja memakai kode yang tidak ada supaya file
     * template yang diunggah tanpa diubah dilaporkan sebagai baris dilewati,
     * bukan menimpa data peserta sungguhan.
     *
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['07-Oct-24', 'MCU-XXXXX', 'CONTOH NAMA PESERTA', 'L', 'Contoh kesimpulan hasil MCU', 'Contoh catatan', 'dr. Contoh Pemeriksa'],
        ];
    }
}
