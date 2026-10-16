<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ValidateDoctor extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Peserta pemilik baris validasi ini (dicocokkan lewat kode MCU).
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'no_mcu', 'code');
    }

    /**
     * Filter berdasarkan kontrak pemeriksaan. Kosong berarti tanpa filter.
     */
    public function scopeContract(Builder $query, $contractId)
    {
        if (empty($contractId)) {
            return $query;
        }

        return $query->where('contract_id', $contractId);
    }

    /**
     * Filter berdasarkan tanggal MCU (kolom TGL_MCU pada template).
     * Rentang kosong berarti tanpa filter, sehingga seluruh data tetap tampil.
     */
    public function scopeDateRange(Builder $query, $date)
    {
        if (empty($date)) {
            return $query;
        }

        $dates = explode(' to ', $date);
        $startDate = Carbon::parse($dates[0])->startOfDay();
        $endDate = Carbon::parse($dates[1] ?? $dates[0])->endOfDay();

        return $query->whereBetween('date_mcu', [$startDate, $endDate]);
    }
}
