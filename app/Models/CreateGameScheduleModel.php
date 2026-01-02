<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\{DigitMasterModel, BettingProviderSlotModel};


class CreateGameScheduleModel extends Model
{
    use SoftDeletes;
    protected $table = 'schedule_providers_slot_time';

    protected $fillable = [
        'betting_provider_id',
        'slot_id',
        'amount',
        'digit_master_id',
        'slot_time',
        'slot_time_id'
    ];


    public function getGameSchedule() {
        try {
            $data_provider =  DB::table($this->table)
                ->select('betting_providers_id')
                ->selectRaw('MIN(CASE WHEN slot_time >= ? THEN slot_time ELSE NULL END) as next_slot_time', [now()])
                ->whereDate('created_at', today())
                 ->groupBy('betting_providers_id')
                 ->orderBy('next_slot_time')
                ->get();
                // dd(today(),time());
            foreach ($data_provider as $provider) {
                $slots = DB::table('betting_providers')
                    ->where('id', $provider->betting_providers_id)
                    ->first();
                $provider->name = $slots->name;
                $provider->imagepath = $slots->image;
                $provider->is_default = $slots->is_default;

                //get next slot id
                if($provider->next_slot_time) {
                    $nextSlot = DB::table($this->table)
                        ->where('betting_providers_id', $provider->betting_providers_id)
                        ->where('slot_time', $provider->next_slot_time)
                        ->whereDate('created_at', today())
                        ->first();
                    $provider->next_slot_id = $nextSlot ? $nextSlot->slot_time_id : null;
                } else {
                    $provider->next_slot_id = null;
                }

            }
            return $data_provider;

        } catch (\Exception $e) {
            //dd($e->getMessage());
        }
    }


    public function prepareGameData($id, $time_id = null) {
        try {
            $query = DB::table($this->table)
                ->where('betting_providers_id', $id)
                ->whereDate('created_at', today())
                ->whereNull('deleted_at')
                ->orderBy('slot_time_id');

            if ($time_id !== null) {
                $query->where('slot_time_id', $time_id);
            }

            $data_provider = $query->get()->groupBy('slot_time_id');

            $providerTimeIds = array_keys($data_provider->toArray());
            
            $providerTimesQuery = DB::table('provider_times')->whereIn('id', $providerTimeIds);

            if ($time_id !== null) {
                $providerTimesQuery->where('id', $time_id);
            }

            $providerTimes = $providerTimesQuery->get();
            foreach ($providerTimes as $key => $time) {
                $providerTimesQuery = DB::table('betting_providers')->where('id', $time->betting_providers_id)->first();
                $providerTimes[$key]->name = $providerTimesQuery->name;
                $providerTimes[$key]->time_id = $providerTimesQuery->id;
            }
            return $providerTimes;

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    // Eloquent relationship to get slots
    public function digitMaster()
    {
        return $this->belongsTo(DigitMasterModel::class, 'digit_master_id', 'id');
    }

    public function providerSlot()
    {
        return $this->belongsTo(BettingProviderSlotModel::class, 'slot_id', 'id');
    }

    public function prepareGameSlots($id) {

    }
}