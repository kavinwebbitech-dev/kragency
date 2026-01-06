<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\WalletModel;
use App\Models\Admin\WalletTransactionLogModel;
use Yajra\DataTables\DataTables;
use App\Models\User; 
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        return view('admin.wallets.list');
    }

    public function getTableData(Request $request)
    {
        if ($request->ajax()) {
            $wallets = WalletModel::with(['user' => function($query) {
                $query->whereNull('deleted_at');
            }])
            ->whereHas('user', function($query) {
                $query->whereNull('deleted_at');
            })->orderBy((new WalletModel)->getTable() . '.updated_at', 'desc')->get();

            return DataTables::of($wallets)
                ->addColumn('name', function($wallet) {
                    return $wallet->user->name;
                })
                ->addColumn('mobile', function($wallet) {
                    return $wallet->user->mobile;
                })
                ->addColumn('created_at', function($wallet) {
                    return $wallet->created_at->format('Y-m-d H:i'); // format as needed
                })
                ->make(true);
        }
    }

    public function addAmount(Request $request) {
        if ($request->isMethod('post')) {
            // ✅ Conditional validation
            $rules = [
                'customer_id'  => 'required|exists:users,id',
                'description'  => 'nullable|string',
            ];

            if ($request->has('add_bonus')) {
                // Bonus only
                $rules['bonus_amount'] = 'required|numeric|min:1';
            } else {
                // Normal amount
                $rules['amount'] = 'required|numeric|min:1';
            }

            $request->validate($rules);

            DB::transaction(function () use ($request) {

                // Get or create wallet
                $wallet = WalletModel::firstOrCreate(
                    ['user_id' => $request->customer_id],
                    ['balance' => 0, 'bonus_amount' => 0]
                );

                if ($request->has('add_bonus')) {

                    // ✅ Add BONUS only
                    $wallet->increment('bonus_amount', $request->bonus_amount);

                    WalletTransactionLogModel::create([
                        'user_id'        => $request->customer_id,
                        'user_wallet_id' => $wallet->id,
                        'type'           => 'credit',
                        'amount'         => $request->bonus_amount,
                        'description'    => $request->description,
                    ]);

                } else {

                    // ✅ Add NORMAL amount
                    $wallet->increment('balance', $request->amount);

                    WalletTransactionLogModel::create([
                        'user_id'        => $request->customer_id,
                        'user_wallet_id' => $wallet->id,
                        'type'           => 'credit',
                        'amount'         => $request->amount,
                        'description'    => $request->description,
                    ]);
                }
            });

            return redirect()
                ->route('admin.wallet.index')
                ->with('success', 'Wallet updated successfully');
        }



        $data['customers'] = User::where('status', 1)->where('user_type', 'normal')->orderBy('id', 'desc')->get();
        return view('admin.wallets.add', $data);
    }

    public function viewTransactionLogs($user_id, Request $request) {
        $data['user_id'] = $user_id;
        if ($request->ajax()) {
            $transactionLog = WalletTransactionLogModel::where('user_id', $user_id)->latest();
            return DataTables::of($transactionLog)
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y h:i A');
                })->make(true);
        }
        return view('admin.wallets.view_log', $data);
    }


    public function edit($user_id, Request $request) {
        $data['user_id'] = $user_id;
        if ($request->ajax()) {
            $transactionLog = WalletTransactionLogModel::where('user_id', $user_id)->latest();
            return DataTables::of($transactionLog)
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y h:i A');
                })->make(true);
        }
        return view('admin.wallets.view_log', $data);
    }

    public function delete($user_id, Request $request) {
        $data['user_id'] = $user_id;
        if ($request->ajax()) {
            $transactionLog = WalletTransactionLogModel::where('user_id', $user_id)->latest();
            return DataTables::of($transactionLog)
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y h:i A');
                })->make(true);
        }
        return view('admin.wallets.view_log', $data);
    }

    public function deductAmount(Request $request)
    {
        if ($request->isMethod('post')) {

            // ✅ Validation
            $rules = [
                'customer_id' => 'required|exists:users,id',
                'description' => 'nullable|string',
            ];

            if ($request->has('add_bonus')) {
                $rules['bonus_amount'] = 'required|numeric|min:1';
            } else {
                $rules['amount'] = 'required|numeric|min:1';
            }

            $request->validate($rules);
            
            DB::transaction(function () use ($request) {

                $wallet = WalletModel::where('user_id', $request->customer_id)
                    ->lockForUpdate()
                    ->first();

                // if (!$wallet) {
                //     throw new \Exception('Wallet not found');
                // }
                  if (!$wallet) {
                        return redirect()
                            ->back()
                            ->withInput()
                            ->withErrors(['error' => 'Wallet not found']);
                    }
                if (!$request->has('add_bonus') && $wallet->balance < $request->amount) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['amount' => 'Insufficient main wallet balance']);
                }

                /** 🔵 BONUS WALLET CHECK */
                if ($request->has('add_bonus') && $wallet->bonus_amount < $request->bonus_amount) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['bonus_amount' => 'Insufficient bonus wallet balance']);
                }
                /** 🔵 BONUS WALLET DEDUCT */
                if ($request->has('add_bonus')) {

                    if ($wallet->bonus_amount < $request->bonus_amount) {
                        throw new \Exception('Insufficient bonus balance');
                    }

                    $wallet->decrement('bonus_amount', $request->bonus_amount);

                    WalletTransactionLogModel::create([
                        'user_id'        => $request->customer_id,
                        'user_wallet_id' => $wallet->id,
                        'type'           => 'debit',
                        'amount'         => 0,
                        'bonus_amount'   => $request->bonus_amount,
                        'wallet_type'    => 'bonus',
                        'description'    => $request->description,
                    ]);

                } 
                /** 🟢 MAIN WALLET DEDUCT */
                else {

                    if ($wallet->balance < $request->amount) {
                        throw new \Exception('Insufficient balance');
                    }

                    $wallet->decrement('balance', $request->amount);

                    WalletTransactionLogModel::create([
                        'user_id'        => $request->customer_id,
                        'user_wallet_id' => $wallet->id,
                        'type'           => 'debit',
                        'amount'         => $request->amount,
                        'bonus_amount'   => 0,
                        'wallet_type'    => 'main',
                        'description'    => $request->description,
                    ]);
                }
            });

            return redirect()
                ->route('admin.wallet.index')
                ->with('success', 'Amount deducted successfully');
        }

        $data['customers'] = User::where('status', 1)
            ->where('user_type', 'normal')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.wallets.deduct', $data);
    }
}
