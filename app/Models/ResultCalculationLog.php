<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultCalculationLog extends Model
{
    protected $table = 'result_calculation_logs';

    protected $fillable = [
        'provider_id',
        'order_item_id',
        'digits',
        'result',
        'win_amount',
        'win_status',
        'user_id',
    ];
}
