<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Admin\WalletModel;
use App\Models\Admin\WalletTransactionLogModel;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'user_type'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wallet()
    {
        return $this->hasOne(WalletModel::class, 'user_id', 'id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransactionLogModel::class, 'user_id', 'id');
    }

    public function bankDetail()
    {
        return $this->hasOne(\App\Models\BankDetail::class, 'user_id', 'id');
    }
}
