<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Participant extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function tandaVital()
    {
        return $this->belongsTo(TandaVital::class, 'id', 'participant_id')->with('employee');
    }

    public function pemeriksaanFisik()
    {
        return $this->belongsTo(PemeriksaanFisik::class, 'id', 'participant_id')->with('employee');
    }

    public function laboratorium()
    {
        return $this->belongsTo(Laboratorium::class, 'id', 'participant_id')->with('employee');
    }

    public function radiologi()
    {
        return $this->belongsTo(Radiologi::class, 'id', 'participant_id')->with('employee');
    }

    public function audiometri()
    {
        return $this->belongsTo(Audiometri::class, 'id', 'participant_id')->with('employee');
    }

    public function spirometri()
    {
        return $this->belongsTo(Spirometri::class, 'id', 'participant_id')->with('employee');
    }

    public function rectal()
    {
        return $this->belongsTo(Rectal::class, 'id', 'participant_id')->with('employee');
    }

    public function ekg()
    {
        return $this->belongsTo(Ekg::class, 'id', 'participant_id')->with('employee');
    }

    public function validateDoctor()
    {
        return $this->hasOne(ValidateDoctor::class, 'no_mcu', 'code');
    }

    public function scopeDateRange(Builder $query, $date)
    {
        if (empty($date)) {
            return $query;
        }

        $date = explode(' to ', $date);

        // Jika hanya ada satu tanggal
        if (count($date) == 1) {
            $startDate = Carbon::parse($date[0])->startOfDay();
            $endDate = Carbon::parse($date[0])->endOfDay();
        } else {
            // Jika ada dua tanggal
            $startDate = Carbon::parse($date[0])->startOfDay();
            $endDate = Carbon::parse($date[1])->endOfDay();
        }

        return $query->whereBetween('register_date', [$startDate, $endDate]);
    }
    public function scopeFormRange(Builder $query, $numberno)
    {
        // Split the range by '-' and ensure both numbers are integers
        $number = explode('-', $numberno);

        return $query->whereBetween('no_form', [(int)$number[0], (int)$number[1]]);
    }
}
