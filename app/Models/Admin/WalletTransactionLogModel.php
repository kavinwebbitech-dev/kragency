<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class WalletTransactionLogModel extends Model
{
    protected $table = 'users_wallet_transactions';

    protected $fillable = [
        'user_id',
        'user_wallet_id',
        'type',
        'amount',
        'description'
    ];
}
