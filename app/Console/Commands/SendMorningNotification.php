<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SendMorningNotification extends Command
{
    protected $signature = 'notify:morning';
    protected $description = 'Send daily morning game notification';

    public function handle()
    {
        $users = User::whereNotNull('device_token')->where('status',1)->get();

        foreach ($users as $user) {
            app()->call('App\Http\Controllers\Admin\CustomerContactController@sendPushNotification', [
                'deviceToken' => $user->device_token,
                'type' => 'morning',
                'userName' => $user->name,
                'slotName' => '',
                'slotTime' => ''
            ]);
        }

        $this->info('Morning notifications sent successfully!');
    }
}
