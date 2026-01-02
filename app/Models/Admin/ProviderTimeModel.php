<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin\BettingProvidersModel; 

class ProviderTimeModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provider_times';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'betting_providers_id',
        'time',
        'status',
    ];

    /**
     * Relationship with Provider (BettingProvidersModel).
     */
    public function provider()
    {
        return $this->belongsTo(BettingProvidersModel::class, 'provider_id');
    }
}
