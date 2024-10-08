<?php

namespace App\Models;

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

    public function scopeDateRange(Builder $query, $date)
    {
        $date = explode(' to ', $date);
        // dd($date);
        return $query->whereBetween('created_at', [$date[0], $date[1]]);
    }
}
