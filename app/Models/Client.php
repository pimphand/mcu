<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'client_id', 'id');
    }

    public function divisi()
    {
        return $this->hasMany(Divisi::class, 'client_id', 'id');
    }
}
