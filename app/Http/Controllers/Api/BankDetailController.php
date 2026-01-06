<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BankDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class BankDetailController extends Controller
{
     public function show()
    {
        $bankDetail = BankDetail::where('user_id', Auth::id())->first();

        return response()->json([
            'success' => true,
            'data' => $bankDetail
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:20',
            'branch_name' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bankDetail = BankDetail::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'bank_name' => $request->bank_name,
                    'ifsc_code' => $request->ifsc_code,
                    'branch_name' => $request->branch_name,
                    'account_number' => $request->account_number,
                    'notes' => $request->notes,
                ]
            );

            $message = $bankDetail->wasRecentlyCreated ? 'Bank details added successfully.' : 'Bank details updated successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $bankDetail
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save bank details. ' . $e->getMessage()
            ], 500);
        }
    }
}