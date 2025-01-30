<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'role_id', 'menu_id', 'is_view', 'is_add', 'is_edit', 'is_delete'
    ];
}
