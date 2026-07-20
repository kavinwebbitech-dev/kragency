<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KycDetail;
use App\Models\Recharge;
use App\Models\Setting;
use Illuminate\Http\Request;

class RechargeController extends Controller
{
    public function storeRechage(Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        $userId = auth()->user()->id;
        $recharge = new Recharge();
        $recharge->user_id = $userId;
        $recharge->amount = $request->amount;

        $recharge->status = 'pending';
        $recharge->save();
        $setting = Setting::where('key', 'qr_code')->first();
        // make image URL live
        if ($recharge->image) {
            $recharge->image = asset($recharge->image);
        }
        $qr = $setting ? ($setting->value ? asset($setting->value) : null) : null;
        $recharge->qr_code = $qr;

        return response()->json([
            'message' => 'Recharge request submitted successfully.',
            'data' => $recharge,
        ], 201);    }

    public function uploadImage(Request $request, $id){
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $recharge = Recharge::findOrFail($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/recharges'), $imageName);
            $recharge->image = 'uploads/recharges/' . $imageName;
            $recharge->save();
        }

        if ($recharge->image) {
            $recharge->image = asset($recharge->image);
        }

        return response()->json(['message' => 'Image uploaded successfully.', 'data' => $recharge], 200);
    }

    public function rechargeHistory(Request $request){
        $userId = auth()->user()->id;
        $recharges = Recharge::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        $recharges->transform(function($item){
            if ($item->image) {
                $item->image = asset($item->image);
            }
            return $item;
        });
        return response()->json(['data' => $recharges], 200);
    }

    public function rechargeHistoryById(Request $request, $id){
        $userId = auth()->user()->id;
        $recharge = Recharge::where('user_id', $userId)->where('id', $id)->first();
        $setting = Setting::where('key', 'qr_code')->first();
        if (!$recharge) {
            return response()->json(['message' => 'Recharge not found.'], 404);
        }

        if ($recharge->image) {
            $recharge->image = asset($recharge->image);
        }
        $qr = $setting ? ($setting->value ? asset($setting->value) : null) : null;
        $recharge->qr_code = $qr;
        return response()->json(['data' => $recharge], 200);
    }

    public function checkkyc(Request $request){
        $userId = auth()->user()->id;
        $kyu = KycDetail::where('user_id', $userId)->where('aadhar_front', '!=', null)
            ->where('aadhar_back', '!=', null)
            ->where('pan_front', '!=', null)
            ->where('pan_back', '!=', null)
            ->where('photo', '!=', null)
            ->where('status', 'approved')
            ->first();
        if (!$kyu) {
            return response()->json(['status' => false, 'message' => 'KYC details not found.'], 404);
        }

        return response()->json(['status' => true, 'data' => $kyu], 200);
    }

    public function uploadKyc(Request $request)
    {
        $request->validate([
            'aadhar_front' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_back'  => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pan_front'    => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pan_back'     => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo'        => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $userId = auth()->id();

        $uploadPath = public_path('uploads/kyc');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $aadharFrontName = time().'_aadhar_front.'.$request->file('aadhar_front')->getClientOriginalExtension();
        $request->file('aadhar_front')->move($uploadPath, $aadharFrontName);

        $aadharBackName = time().'_aadhar_back.'.$request->file('aadhar_back')->getClientOriginalExtension();
        $request->file('aadhar_back')->move($uploadPath, $aadharBackName);

        $panFrontName = time().'_pan_front.'.$request->file('pan_front')->getClientOriginalExtension();
        $request->file('pan_front')->move($uploadPath, $panFrontName);

        $panBackName = time().'_pan_back.'.$request->file('pan_back')->getClientOriginalExtension();
        $request->file('pan_back')->move($uploadPath, $panBackName);

        $photoName = time().'_photo.'.$request->file('photo')->getClientOriginalExtension();
        $request->file('photo')->move($uploadPath, $photoName);

        $kyc = KycDetail::updateOrCreate(
            ['user_id' => $userId],
            [
                'aadhar_front' => 'uploads/kyc/'.$aadharFrontName,
                'aadhar_back'  => 'uploads/kyc/'.$aadharBackName,
                'pan_front'    => 'uploads/kyc/'.$panFrontName,
                'pan_back'     => 'uploads/kyc/'.$panBackName,
                'photo'        => 'uploads/kyc/'.$photoName,
                'status'       => 'pending',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'KYC uploaded successfully.',
            'data'    => $kyc,
        ], 200);
    }

    public function kycList(Request $request){
        $userId = auth()->user()->id;
        $kycDetails = KycDetail::where('user_id', $userId)->first();
        return response()->json(['data' => $kycDetails], 200);
    }
}
