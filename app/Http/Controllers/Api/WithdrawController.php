<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\WalletModel;
use App\Models\WithdrawRequest;
use Carbon\Carbon;

class WithdrawController extends Controller
{
    public function walletInfo()
    {
        $wallet = WalletModel::where('user_id', Auth::id())->first();

        return response()->json([
            'success' => true,
            'wallet_balance' => $wallet?->balance ?? 0,
            'bonus_balance' => $wallet?->bonus_amount ?? 0,
            'min_withdraw' => 500
        ]);
    }

    public function submitRequest(Request $request)
    {
        $userId = Auth::id();

        $wallet = WalletModel::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found'
            ], 404);
        }

        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:500',
                'max:' . $wallet->balance,
            ],
        ]);

        $lastWithdraw = WithdrawRequest::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->exists();

        if ($lastWithdraw) {
            return response()->json([
                'success' => false,
                'message' => 'Only one withdrawal allowed every 24 hours'
            ], 422);
        }

        WithdrawRequest::create([
            'user_id' => $userId,
            'amount'  => $request->amount,
            'status'  => 'pending',
        ]);

        $wallet->decrement('balance', $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Withdraw request submitted successfully'
        ]);
    }

    public function withdrawHistory()
    {
        $history = WithdrawRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'amount' => $row->amount,
                    'status' => $row->status,
                    'date' => $row->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}
