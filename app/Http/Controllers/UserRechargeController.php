<?php

namespace App\Http\Controllers;

use App\Models\Recharge;
use App\Models\Setting;
use Illuminate\Http\Request;

class UserRechargeController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        // Fetch User's Recharge History
        $recharges = Recharge::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        
        // Fetch QR Code Setting
        $setting = Setting::where('key', 'qr_code')->first();
        $qr = $setting ? ($setting->value ? asset($setting->value) : null) : null;

        return view('frontend.recharge.index', compact('recharges', 'qr'));
    }

    public function store(Request $request)
    {
        // For web, it's best to handle the amount and screenshot in a single request
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'image'  => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $recharge = new Recharge();
        $recharge->user_id = auth()->id();
        $recharge->amount = $request->amount;
        $recharge->status = 'pending';

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            
            // Ensure directory exists
            $uploadPath = public_path('uploads/recharges');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image->move($uploadPath, $imageName);
            $recharge->image = 'uploads/recharges/' . $imageName;
        }

        $recharge->save();

        return redirect()->back()->with('success', 'Recharge request and payment screenshot submitted successfully.');
    }
}