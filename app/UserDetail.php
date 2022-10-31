<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable= [
        'user_id', 'name', 'date_birth', 'phone_number',
        'address', 'district', 'province',
        'photo', 'status',
    ];
}
