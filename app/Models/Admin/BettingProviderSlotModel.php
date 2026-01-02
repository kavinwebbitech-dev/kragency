<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BettingProviderSlotModel extends Model
{
    use SoftDeletes;
    protected $table = 'provider_slots';

    protected $fillable = [
        'betting_provider_id',
        'slot_id',
        'amount',
        'winning_amount'
    ];

    // Relationships
    public function provider()
    {
        return $this->belongsTo(BettingProvidersModel::class, 'betting_provider_id');
    }

    
}
