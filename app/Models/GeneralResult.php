<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class GeneralResult extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
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
     * Filter rentang tanggal pemeriksaan (kolom mcu_date).
     * Kosong berarti tanpa filter, format "YYYY-MM-DD to YYYY-MM-DD".
     */
    public function scopeDateRange(Builder $query, $date)
    {
        if (empty($date)) {
            return $query;
        }

        $range = explode(' to ', $date);

        $start = Carbon::parse($range[0])->startOfDay();
        $end = isset($range[1]) ? Carbon::parse($range[1])->endOfDay() : Carbon::parse($range[0])->endOfDay();

        return $query->whereBetween('mcu_date', [$start, $end]);
    }
}
