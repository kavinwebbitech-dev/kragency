<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycDetail extends Model
{
    protected $table = 'kyc_details';

    protected $fillable = [
        'user_id',
        'aadhar_front',
        'aadhar_back',
        'pan_front',
        'pan_back',
        'photo',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
