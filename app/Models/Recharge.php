<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userDetail()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
