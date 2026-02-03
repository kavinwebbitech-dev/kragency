<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $fillable = [
        'name',
        'mobile_numbers',
        'emails',
    ];

    protected $casts = [
        'mobile_numbers' => 'array',
        'emails' => 'array',
    ];
}
