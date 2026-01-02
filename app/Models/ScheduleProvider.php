<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleProvider extends Model
{
    use SoftDeletes;

    protected $table = 'schedule_provider';
    protected $fillable = [
        'betting_providers_id',
        'slot_time_id',
        'result',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function slotTimes()
    {
        return $this->hasMany(ScheduleProviderSlotTime::class, 'schedule_provider_id');
    }

}
