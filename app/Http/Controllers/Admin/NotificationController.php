<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;

class NotificationController extends Controller
{
    public function index(Request $request){
        return view('admin.notification.index');
    }

    public function sendNotificaion(Request $request)
    {
        $users = User::whereNotNull('device_token')
                    ->where('status', 1)
                    ->pluck('device_token')
                    ->toArray();

        if (empty($users)) {
            return back()->with('error', 'No active users with device token found');
        }

        $type = $request->type ?? 'general';

        // 🔥 Type-based icon & title
        switch ($type) {
            case 'offer':
                $icon  = 'ic_offer';
                $titlePrefix = '🔥 Special Offer!';
                break;

            case 'reminder':
                $icon  = 'ic_reminder';
                $titlePrefix = '⏰ Reminder';
                break;

            case 'alert':
                $icon  = 'ic_alert';
                $titlePrefix = '⚠ Important Alert';
                break;

            default:
                $icon  = 'ic_notification';
                $titlePrefix = '📢 Notification';
                break;
        }

        $finalTitle = $titlePrefix . ' - ' . $request->title;

        try {

            // ✅ Create Firebase instance ONCE
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.projects.app.credentials.file'));

            $messaging = $factory->createMessaging();

            foreach ($users as $deviceToken) {

                $message = CloudMessage::withTarget('token', $deviceToken)
                    ->withAndroidConfig(AndroidConfig::fromArray([
                        'priority' => 'high',
                        'notification' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'title' => $finalTitle,
                            'body'  => $request->body,
                            'sound' => 'default',
                            'icon'  => $icon,
                            'color' => '#0d6efd',
                        ],
                    ]))
                    ->withData([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'screen' => (string) $request->screen,
                        'tab'    => (string) $request->tab,
                        'type'   => (string) $type,
                    ]);

                $messaging->send($message);
            }

            Log::info("Push sent to " . count($users) . " users. Type: {$type}");

            return back()->with('success', 'Notification sent to all users successfully');

        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return back()->with('error', 'Notification sending failed');
        }
    }
}
