<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankDetail;
use Illuminate\Support\Facades\Auth;

class BankDetailController extends Controller
{
    public function create()
    {
        $userId = Auth::id();
        $bankDetail = BankDetail::where('user_id', $userId)->first();
        return view('frontend.bank_details.create', compact('bankDetail'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:20',
            'branch_name' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $userId = Auth::id();

        try {
            // Create or update existing bank detail for the user
            $bank = BankDetail::updateOrCreate(
                ['user_id' => $userId],
                [
                    'bank_name' => $request->bank_name,
                    'ifsc_code' => $request->ifsc_code,
                    'branch_name' => $request->branch_name,
                    'account_number' => $request->account_number,
                    'notes' => $request->notes,
                ]
            );

            $message = $bank->wasRecentlyCreated ? 'Bank details added successfully.' : 'Bank details updated successfully.';

            return redirect()->route('bank-details.create')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('bank-details.create')->with('error', 'Failed to save bank details. ' . $e->getMessage());
        }
    }
}
