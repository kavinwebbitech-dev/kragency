<?php

namespace App\Console\Commands;

use App\Models\CloseTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\CreateGameScheduleModel;

class SendSlotCompletedNotification extends Command
{
    protected $signature = 'notify:slot-completed';
    protected $description = 'Send slot completed notification';

    public function handle()
    {
        $gameModel = new CreateGameScheduleModel();

        $schedules = $gameModel->getGameSchedule()
            ->sortByDesc('is_default')
            ->values();

        $now = Carbon::now();

        foreach ($schedules as $schedule) {

            $closeTime = Carbon::parse($schedule->close_time);

            if (isset($schedule->date)) {
                $closeTime = Carbon::parse($schedule->date . ' ' . $schedule->close_time);
            } else {
                $closeTime = Carbon::parse(today()->toDateString() . ' ' . $schedule->close_time);
            }

            $notifyTime = $closeTime->copy()->addHour();

            if ($now->between($notifyTime->subMinutes(2), $notifyTime->addMinutes(2))) {

                $slotName = $schedule->name ?? 'Game Slot';
                $slotTime = $schedule->close_time;

                $users = User::whereNotNull('device_token')
                    ->where('status', 1)
                    ->select('id', 'name', 'device_token')
                    ->get();
    
                foreach ($users as $user) {
                    app()->call('App\Http\Controllers\CustomerContactController@sendPushNotification', [
                        'deviceToken' => $user->device_token,
                        'type'        => 'slot_completed',
                        'userName'    => $user->name,
                        'slotName'    => $slotName,
                        'slotTime'    => $slotTime,
                    ]);
                }

                $this->info("Slot completed notification sent for: {$slotName}");
            }
        }
    }
}
