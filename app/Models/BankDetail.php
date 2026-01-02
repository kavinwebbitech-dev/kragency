<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $table = 'bank_details';
    protected $fillable = [
        'user_id',
        'bank_name',
        'ifsc_code',
        'branch_name',
        'account_number',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
