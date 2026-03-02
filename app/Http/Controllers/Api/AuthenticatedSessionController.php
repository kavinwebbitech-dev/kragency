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
            'mobile'   => 'required|digits_between:8,15',
            'password' => 'required'
        ]);

        if (!Auth::attempt([
            'mobile'   => $request->mobile,
            'password' => $request->password,
        ])) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid mobile or password'
            ], 401);
        }

        $user = Auth::user();

        if (
            $user->user_type !== 'normal' ||
            !$user->status ||
            $user->deleted_at !== null
        ) {
            Auth::logout();

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized user'
            ], 403);
        }

        $token = $user->createToken('customer_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'mobile' => $user->mobile,
            ]
        ], 200);
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
        $link = WhatsAppLink::value('link');

        return response()->json([
            'status' => true,
            'link'   => $link
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
