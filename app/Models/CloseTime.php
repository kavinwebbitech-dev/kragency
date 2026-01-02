<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloseTime extends Model
{
    protected $table = 'close_times';
    protected $fillable = ['minutes', 'whatsapp_number'];
}
