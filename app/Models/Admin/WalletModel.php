<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; 
class WalletModel extends Model
{
    protected $table = 'users_wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'created_at',
        'updated_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
