<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    protected $table = 'customer_orders';
    protected $fillable = [
        'user_id',
        'total_amount',
        'opening_balance',
        'closing_balance',
        'status',
        'created_at',
        'updated_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(CustomerOrderItemModel::class, 'order_id', 'id');
    }
}
