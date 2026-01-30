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
        $results = DB::table('betting_providers')
            ->leftJoin('provider_times', function ($join) {
                $join->on('betting_providers.id', '=', 'provider_times.betting_providers_id');
            })
            ->leftJoin('provider_slots', function ($join) {
                $join->on('betting_providers.id', '=', 'provider_slots.betting_provider_id');
            })
            ->select(
                'betting_providers.id as provider_id',
                'provider_times.time',
                'provider_times.id as time_id',
                'provider_slots.slot_id as digit_master_id',
                'provider_slots.amount',
                'provider_slots.id as provider_slot_id',
                'provider_slots.deleted_at as provider_slot_deleted_at'
            )
            ->where('betting_providers.status', 1)
            ->whereNull('betting_providers.deleted_at')
            ->whereNotNull('provider_times.id')
            ->whereNotNull('provider_slots.id')
            ->get();

        DB::beginTransaction();

        try {
            foreach ($results as $result) {

                /**
                 * 🔥 RULE 1:
                 * If provider_slot is deleted,
                 * soft delete TODAY'S schedule and SKIP insert
                 */
                if (!is_null($result->provider_slot_deleted_at)) {

                    DB::table('schedule_providers_slot_time')
                        ->where('betting_providers_id', $result->provider_id)
                        ->where('slot_time_id', $result->time_id)
                        ->where('slot_id', $result->provider_slot_id)
                        ->whereDate('created_at', now()->toDateString())
                        ->whereNull('deleted_at')
                        ->update([
                            'deleted_at' => now(),
                            'updated_at' => now(),
                        ]);

                    continue; // 🚫 do not recreate schedule
                }

                /**
                 * 🔥 RULE 2:
                 * Soft delete OLD schedules (not today)
                 */
                DB::table('schedule_providers_slot_time')
                    ->where('betting_providers_id', $result->provider_id)
                    ->where('slot_time_id', $result->time_id)
                    ->whereDate('created_at', '<>', now()->toDateString())
                    ->whereNull('deleted_at')
                    ->update([
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);

                /**
                 * 🔥 RULE 3:
                 * schedule_provider (daily unique)
                 */
                $scheduleProvider = DB::table('schedule_provider')
                    ->where('betting_providers_id', $result->provider_id)
                    ->where('slot_time_id', $result->time_id)
                    ->whereDate('created_at', now()->toDateString())
                    ->first();

                if (!$scheduleProvider) {
                    $schedule_provider_id = DB::table('schedule_provider')->insertGetId([
                        'betting_providers_id' => $result->provider_id,
                        'slot_time_id'        => $result->time_id,
                        'slot_id'             => $result->provider_slot_id,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                } else {
                    $schedule_provider_id = $scheduleProvider->id;
                }

                /**
                 * 🔥 RULE 4:
                 * Prevent duplicate schedule for TODAY
                 */
                $exists = DB::table('schedule_providers_slot_time')
                    ->where('betting_providers_id', $result->provider_id)
                    ->where('digit_master_id', $result->digit_master_id)
                    ->where('slot_id', $result->provider_slot_id)
                    ->where('slot_time_id', $result->time_id)
                    ->whereDate('created_at', now()->toDateString())
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) {
                    continue;
                }

                /**
                 * 🔥 RULE 5:
                 * Insert today's schedule
                 */
                DB::table('schedule_providers_slot_time')->insert([
                    'betting_providers_id' => $result->provider_id,
                    'schedule_provider_id' => $schedule_provider_id,
                    'slot_time'            => $result->time,
                    'slot_time_id'         => $result->time_id,
                    'digit_master_id'      => $result->digit_master_id,
                    'slot_id'              => $result->provider_slot_id,
                    'amount'               => $result->amount,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }

            DB::commit();
            Log::info('ScheduleDailyGame executed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ScheduleDailyGame failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
