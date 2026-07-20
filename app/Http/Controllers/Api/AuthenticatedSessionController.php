<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\WhatsAppLink;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits_between:8,15',
            'otp'    => 'required|digits:4',
        ]);

        $user = User::where('mobile', $request->mobile)
            ->where('otp', $request->otp)
            ->where('otp_created_at', '>', now()->subMinutes(5))
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired OTP.'
            ], 401);
        }

        if (
            $user->user_type !== 'normal' ||
            !$user->status ||
            $user->deleted_at !== null
        ) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized user.'
            ], 403);
        }

        // Clear OTP after successful login
        $user->update([
            'otp' => null,
            'otp_expired_at' => null,
        ]);

        // Login user (optional for API)
        Auth::login($user);

        // Create Sanctum token
        $token = $user->createToken('customer_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'mobile' => $user->mobile,
            ]
        ], 200);
    }
    public function register(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'mobile' => 'required|digits_between:8,15',
            'otp'    => 'required|digits:4',
        ]);

        $user = User::where('mobile', $request->mobile)
            ->where('otp', $request->otp)
            ->where('otp_created_at', '>', now()->subMinutes(5))
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP.'
            ], 401);
        }

        $user->update([
            'name'            => $request->name,
            'user_type'       => 'normal',
            'status'          => 1,
            'otp'             => null,
            'otp_created_at'  => null,
        ]);

        $token = $user->createToken('customer_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Registration successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
            ]
        ], 201);
    }
    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits_between:8,15',
        ]);
        $otp = rand(1000, 9999);
        $user = User::where('mobile', $request->mobile)->first();
        if ($user) {
            $user->otp = $otp;
            $user->otp_created_at = now();
            $user->save();
        }else{
           $user = User::create([
                'name'=> $request->name ?? '-',
                'mobile' => $request->mobile,
                'otp' => $otp,
                'otp_created_at' => now(),
           ]);
        }

        $message = "Welcome to Gason India! Your OTP for registration is: {$otp} This OTP is valid for 5 minutes. Please do not share this code with anyone.Team Gason";

        $this->sendSms($request->mobile, $message);

        return response()->json([   'message' => 'OTP sent successfully.']);
    }
    private function sendSms($number, $message)
    {
        $url = "http://pay4sms.in/";
        $token = "06e8ed1c1f10e2f05140416260699412";
        $sender = "GASON";
        $credit = "2";

        $message = urlencode($message);

        $smsUrl = $url . "sendsms/?token=" . $token .
            "&sender=" . $sender .
            "&number=" . $number .
            "&credit=" . $credit .
            "&message=" . $message;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $smsUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $result = curl_exec($curl);

        curl_close($curl);
        return $result;
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function whatsappLink()
    {
        $data = WhatsAppLink::first();
        $contact = WhatsAppLink::where('id',2)->first();

        return response()->json([
            'status' => true,
            'link'   => $data->link,
            'emergency_contact' => $contact->link ?? ''
        ], 200);
    }

    public function saveDeviceToken(Request $request)
    {
        try {
            $request->validate([
                'fcm_token' => 'sometimes|nullable|string'
            ]);

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized user'
                ], 401);
            }

            if ($request->has('fcm_token')) {
                $user->device_token = $request->fcm_token;
                $user->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Device token saved successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Save Device Token Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while saving device token'
            ], 500);
        }
    }


    public function contactStore(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array',
        ]);

        foreach ($request->contacts as $contact) {

            // Normalize and deduplicate phone numbers within this contact
            $mobileNumbers = collect($contact['phoneNumbers'] ?? [])
                ->map(function ($phone) {
                    $number = preg_replace('/[^0-9+]/', '', $phone['number'] ?? '');
                    if (strlen($number) === 10) {
                        $number = '+91' . $number;
                    }
                    return [
                        'label'  => $phone['label'] ?? 'Mobile',
                        'number' => $number,
                    ];
                })
                ->filter(fn ($p) => !empty($p['number']))
                ->unique('number')
                ->values()
                ->toArray();

            // Remove numbers that already exist in DB
            $existingNumbers = CustomerContact::pluck('mobile_numbers')
                ->flatten(1) // flatten JSON arrays
                ->pluck('number') // get all numbers
                ->toArray();

            $mobileNumbers = collect($mobileNumbers)
                ->reject(fn ($p) => in_array($p['number'], $existingNumbers))
                ->values()
                ->toArray();

            // Emails - normalize and deduplicate within contact
            $emails = collect($contact['emails'] ?? [])
                ->map(fn ($e) => strtolower(trim($e['email'] ?? '')))
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Remove emails that already exist in DB
            $existingEmails = CustomerContact::pluck('emails')
                ->flatten()
                ->toArray();

            $emails = collect($emails)
                ->reject(fn ($e) => in_array($e, $existingEmails))
                ->values()
                ->toArray();

            // Skip if no new mobile numbers and emails
            if (empty($mobileNumbers) && empty($emails)) {
                continue;
            }

            // Save contact
            CustomerContact::create([
                'customer_id'    => Auth::id(),
                'name'           => $contact['displayName'] ?? null,
                'mobile_numbers' => $mobileNumbers,
                'emails'         => $emails,
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Contacts stored successfully',
        ]);
    }

}