<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'mobile_numbers',
        'emails',
    ];

    protected $casts = [
        'mobile_numbers' => 'array',
        'emails' => 'array',
    ];

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id','id');
    }
}