<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Models\Admin\WalletModel;
use App\Models\Admin\WalletTransactionLogModel;
use Illuminate\Support\Facades\DB;

class WithdrawAdminController extends Controller
{
    public function index()
    {
        $requests = WithdrawRequest::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.withdraw.index', compact('requests'));
    }

    public function update(Request $request, $id)
    {
        $withdraw = WithdrawRequest::findOrFail($id);
        if ($withdraw->status !== 'pending') {
            return back()->with('error', 'Request already processed.');
        }
        if ($request->action === 'approve') {
            DB::beginTransaction();
            try {
                $wallet = WalletModel::where('user_id', $withdraw->user_id)->lockForUpdate()->first();
                if (!$wallet || $wallet->balance < $withdraw->amount) {
                    return back()->with('error', 'Insufficient wallet balance.');
                }
                $wallet->balance -= $withdraw->amount;
                $wallet->save();
                $withdraw->status = 'approved';
                $withdraw->save();
                WalletTransactionLogModel::create([
                    'user_id' => $withdraw->user_id,
                    'user_wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $withdraw->amount,
                    'description' => 'Withdraw approved by admin',
                ]);
                DB::commit();
                return back()->with('success', 'Withdraw approved and wallet updated.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Error: ' . $e->getMessage());
            }
        } elseif ($request->action === 'reject') {
            $withdraw->status = 'rejected';
            $withdraw->save();
            return back()->with('success', 'Withdraw request rejected.');
        }
        return back();
    }
}
