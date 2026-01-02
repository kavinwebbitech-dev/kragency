<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\WhatsAppLink;



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
}
