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
        return $this->hasOne(TandaVital::class, 'participant_id', 'id')->with('employee');
    }

    public function pemeriksaanFisik()
    {
        return $this->hasOne(PemeriksaanFisik::class, 'participant_id', 'id')->with('employee');
    }

    public function laboratorium()
    {
        return $this->hasOne(Laboratorium::class, 'participant_id', 'id')->with('employee');
    }

    public function radiologi()
    {
        return $this->hasOne(Radiologi::class, 'participant_id', 'id')->with('employee');
    }

    public function audiometri()
    {
        return $this->hasOne(Audiometri::class, 'participant_id', 'id')->with('employee');
    }

    public function spirometri()
    {
        return $this->hasOne(Spirometri::class, 'participant_id', 'id')->with('employee');
    }

    public function rectal()
    {
        return $this->hasOne(Rectal::class, 'participant_id', 'id')->with('employee');
    }

    public function ekg()
    {
        return $this->hasOne(Ekg::class, 'participant_id', 'id')->with('employee');
    }

    public function validateDoctor()
    {
        return $this->hasOne(ValidateDoctor::class, 'no_mcu', 'code');
    }

    public function scopeDateRange(Builder $query, $date)
    {
        $date = explode(' to ', $date);
        // dd(count($date));
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
}
