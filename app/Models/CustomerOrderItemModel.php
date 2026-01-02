<?php

namespace App\Models;
 use App\Models\Admin\DigitMasterModel;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderItemModel extends Model
{
    protected $table = 'customer_order_items';

    protected $fillable = [
        'order_id',
        'game_id',
        'digits',
        'quantity',
        'amount',
        'is_box',
        'win_amount',
        'win_status',
        'created_at',
        'updated_at'
    ];


    public function customerOrders()
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'id');
    }

    /**
     * Get the schedule provider slot time associated with the game_id.
     */
    public function scheduleProviderSlotTime()
    {
        return $this->belongsTo(ScheduleProviderSlotTime::class, 'game_id', 'id');
    }
   

public function digitMaster()
{
    return $this->belongsTo(DigitMasterModel::class, 'digits', 'digit');
}

}
