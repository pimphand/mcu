<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratorium extends Model
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
}
