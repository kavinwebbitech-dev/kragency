<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrdersModel extends Model
{
    protected $table = 'customer_orders';

    protected $fillable = [
        'user_id',
        'total_amount',
        'opening_balance',
        'closing_balance',
        'created_at',
        'updated_at'
    ];
}
