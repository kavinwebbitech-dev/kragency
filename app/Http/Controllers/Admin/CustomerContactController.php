<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Yajra\DataTables\DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerContactController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $customers = User::has('contacts')->where('user_type', 'normal')->withCount('contacts');

            return DataTables::of($customers)
                ->addIndexColumn() 
                ->make(true);
        }

        return view('admin.contacts.index');
    }

    public function getCustomerContacts(Request $request, $customerId)
    {
        if ($request->ajax()) {
            $contacts = CustomerContact::where('customer_id', $customerId);

            return DataTables::of($contacts)
                ->addIndexColumn()
                ->make(true);
        }
    }
    
    public function deleteAllContact(Request $request)
    {
        CustomerContact::truncate();
        return redirect()->back()->with('success', 'All contacts deleted successfully.');
    }

    public function deleteContact($id, Request $request)
    {
        $contact = CustomerContact::find($id);
        if (!$contact) {
            return response()->json(['message' => 'Contact not found.'], 404);
        }
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully.']);
    }

    public function export()
    {
        $fileName = 'customer_contacts_' . now()->format('Y_m_d_His') . '.csv';

        $contacts = CustomerContact::all();

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $callback = function () use ($contacts) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Name', 'Mobile Numbers', 'Emails']);

            foreach ($contacts as $contact) {

                $mobiles = collect($contact->mobile_numbers)
                    ->pluck('number')
                    ->implode(', ');

                $emails = collect($contact->emails)
                    ->implode(', ');

                fputcsv($file, [
                    $contact->name,
                    $mobiles,
                    $emails,
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function getNotificationContent($type, $userName = '', $slotName = '', $slotTime = '')
    {
        switch ($type) {
            case 'morning':
                return [
                    'title' => 'Good Morning 🎮',
                    'body'  => "Good morning {$userName}! Play your favorite games and win exciting rewards today.",
                    'screen' => 'Home',
                    'tab' => 'Home',
                ];

            case 'slot_completed':
                return [
                    'title' => '⏰ Slot Completed!',
                    'body'  => "{$slotName} slot is completed. Only 1 hour left to play. Join now!",
                    'screen' => 'Home',
                    'tab' => 'Home',
                ];

            case 'result_published':
                return [
                    'title' => '📢 Result Published!',
                    'body'  => "The {$slotName} slot result is now published. Tap to check your result.",
                    'screen' => 'Results',
                    'tab' => 'Results',
                ];

            default:
                return [
                    'title' => '⏰ Game Notification',
                    'body'  => 'Check the latest updates in your app.',
                    'screen' => 'Home',
                    'tab' => 'Home',
                ];
        }
    }

    public function sendPushNotification($deviceToken, $type = 'general', $userName = '', $slotName = '', $slotTime = '')
    {
        if (empty($deviceToken)) {
            Log::warning('FCM token is empty');
            return false;
        }

        $content = $this->getNotificationContent($type, $userName = '', $slotName = '', $slotTime = '');

        try {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.projects.app.credentials.file'));

            $messaging = $factory->createMessaging();

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                    'notification' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'title' => $content['title'],
                        'body'  => $content['body'],
                        'sound' => 'default',
                    ],
                ]))
                ->withData([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'screen' => (string) $content['screen'],
                    'tab'    => (string) $content['tab'],
                    'type'   => (string) $type,              
                    // 'slot_name' => (string) $slotName,
                    // 'slot_time' => (string) $slotTime,
                ]);

            $messaging->send($message);

            Log::info("Push sent: {$type}");
            return true;

        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function send()
    {
        $user = User::find(181);
    
        $this->sendPushNotification(
            $user->device_token,
            'New Result Published',
            'Tap to view your results',
            'results',
            '2' // tab index as STRING
        );
    }
}