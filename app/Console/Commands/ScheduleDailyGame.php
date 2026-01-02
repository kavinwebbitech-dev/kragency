<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ScheduleDailyGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:schedule-daily-game';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch from betting_providers left join provider_times left join provider_slots
       $results = DB::table('betting_providers')
                ->leftJoin('provider_times', 'betting_providers.id', '=', 'provider_times.betting_providers_id')
                ->leftJoin('provider_slots', function($join) {
                    $join->on('betting_providers.id', '=', 'provider_slots.betting_provider_id');
                })
                ->select(
                    'betting_providers.*',
                    'provider_times.time',
                    'provider_times.id as time_id',
                    'provider_slots.slot_id',
                    'provider_slots.amount',
                    'provider_slots.id as provider_slot_id',
                )
                ->where('betting_providers.status', 1)
                ->whereNull('betting_providers.deleted_at')
                ->get();

        //insert into schedule_providers and schedule_providers_slot_time
        DB::beginTransaction();
        try {
            foreach ($results as $result) {

                //check if record already exists in schedule_providers
                $s_exists = DB::table('schedule_provider')
                    ->where('betting_providers_id', $result->id)
                    ->where('slot_time_id', $result->time_id)
                    ->whereDate('created_at', now()->toDateString())
                    ->first();

                if (!$s_exists) {
                    $schedule_provider_id = DB::table('schedule_provider')->insertGetId([
                        'betting_providers_id' => $result->id,
                        'slot_time_id' => $result->time_id,
                        'slot_id' => $result->provider_slot_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $schedule_provider_id = $s_exists->id;
                }


                //check if record already exists in schedule_providers
                $exists = DB::table('schedule_providers_slot_time')
                    ->where('betting_providers_id', $result->id)
                    ->where('digit_master_id', $result->slot_id)
                    ->where('slot_id', $result->provider_slot_id)
                    ->where('slot_time', $result->time)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('schedule_providers_slot_time')->insert([
                    'betting_providers_id' => $result->id,
                    'schedule_provider_id' => $schedule_provider_id,
                    'slot_time' => $result->time,
                    'slot_time_id' => $result->time_id,
                    'digit_master_id' => $result->slot_id,
                    'slot_id' => $result->provider_slot_id,
                    'amount' => $result->amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            Log::error('Error in ScheduleDailyGame command: ' . $e->getMessage());
        }
    }
}
