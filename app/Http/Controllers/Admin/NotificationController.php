<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Messaging\NotFound;
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

        $factory = (new Factory)
            ->withServiceAccount(config('firebase.projects.app.credentials.file'));

        $messaging = $factory->createMessaging();

        $successCount = 0;
        $failCount = 0;

        foreach ($users as $deviceToken) {

            try {

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
                $successCount++;

            } catch (NotFound $e) {

                // Remove invalid token
                User::where('device_token', $deviceToken)
                    ->update(['device_token' => null]);

                $failCount++;
                Log::warning("Invalid token removed: {$deviceToken}");

            } catch (\Exception $e) {

                // Any other error
                $failCount++;
                Log::error("FCM Error for token {$deviceToken}: " . $e->getMessage());

            }
        }

        Log::info("Push Result - Success: {$successCount}, Failed: {$failCount}");

        return back()->with('success', "Notification sent. Success: {$successCount}, Failed: {$failCount}");
    }
}
