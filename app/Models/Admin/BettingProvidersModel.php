<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class BettingProvidersModel extends Model
{
    use SoftDeletes;

    protected $table = 'betting_providers';

    protected $fillable = [
        'name',
        'status',
        'is_default',
        'created_at',
        'image'
    ];

    protected $dates = ['deleted_at'];

    public function times()
    {
        return $this->hasMany(ProviderTimeModel::class, 'betting_providers_id', 'id');
    }

    public function providerSlot()
    {
        return $this->hasMany(BettingProviderSlotModel::class, 'betting_provider_id', 'id');
    }

}
