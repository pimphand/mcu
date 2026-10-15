<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Radiologi extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Filter berdasarkan tanggal MCU (kolom TGL_MCU pada template radiologi).
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
