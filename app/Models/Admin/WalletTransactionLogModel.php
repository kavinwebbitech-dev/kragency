<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WalletTransactionLogModel extends Model
{
    protected $table = 'users_wallet_transactions';

    protected $fillable = [
        'user_id',
        'user_wallet_id',
        'type',
        'amount',
        'bonus_amount',
        'description',
        'created_by'
    ];

    public function userDetail()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
