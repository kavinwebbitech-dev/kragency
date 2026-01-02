<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\WalletModel;
use App\Models\WithdrawRequest;
use Carbon\Carbon;

class WithdrawController extends Controller
{
    public function showForm()
    {
        $wallet = WalletModel::where('user_id', Auth::id())->first();
        return view('frontend.withdraw.form', compact('wallet'));
    }

    public function submitRequest(Request $request)
    {
        $userId = Auth::id();

        
        $wallet = WalletModel::where('user_id', $userId)->first();

        if (!$wallet) {
            return back()->withErrors([
                'amount' => 'Wallet not found.',
            ]);
        }


        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:500',
                'max:' . $wallet->balance,
            ],
        ], [
            'amount.min' => 'Minimum withdrawal amount is ₹500.',
            'amount.max' => 'Withdrawal amount cannot exceed wallet balance.',
        ]);

        
        $lastWithdraw = WithdrawRequest::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->first();

        if ($lastWithdraw) {
            return back()->withErrors([
                'amount' => 'Only one withdrawal allowed every 24 hours.',
            ]);
        }

        
        WithdrawRequest::create([
            'user_id' => $userId,
            'amount'  => $request->amount,
            'status'  => 'pending',
        ]);


        $wallet->decrement('balance', $request->amount);

        return redirect()
            ->route('customer.withdraw')
            ->with('success', 'Withdraw request submitted successfully.');
    }
}
