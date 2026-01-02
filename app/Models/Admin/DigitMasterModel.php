<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class DigitMasterModel extends Model
{
    protected $table = 'digit_master';

    protected $fillable = [
        'name',
        'type'
    ];

    protected $casts = [
        'type' => 'integer'
    ];
}
