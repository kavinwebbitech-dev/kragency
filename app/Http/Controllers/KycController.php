<?php

namespace App\Http\Controllers;

use App\Models\KycDetail;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function index()
    {
        $kyc = KycDetail::where('user_id', auth()->id())->first();
        return view('frontend.kyc.index', compact('kyc'));
    }

    public function store(Request $request)
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

        $files = ['aadhar_front', 'aadhar_back', 'pan_front', 'pan_back', 'photo'];
        $data = ['status' => 'pending'];

        foreach ($files as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $fileName = time() . '_' . $fileKey . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $fileName);
                $data[$fileKey] = 'uploads/kyc/' . $fileName;
            }
        }

        KycDetail::updateOrCreate(['user_id' => $userId], $data);

        return redirect()->back()->with('success', 'KYC documents uploaded successfully and are pending approval.');
    }
}