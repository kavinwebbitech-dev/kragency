<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\BettingProvidersModel;

class ScheduleProviderSlotTime extends Model
{
    protected $table = 'schedule_providers_slot_time';
    protected $fillable = [
        'schedule_provider_id',
        'betting_providers_id',
        'slot_time',
        'slot_time_id',
        'digit_master_id',
        'slot_id',
        'amount',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function getProvider()
    {
        return $this->belongsTo(BettingProvidersModel::class, 'betting_providers_id', 'id');
    }
}
