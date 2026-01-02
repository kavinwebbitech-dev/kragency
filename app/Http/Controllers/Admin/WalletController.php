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
            // Validation
            $request->validate([
                'amount'      => 'required|numeric|min:1',
                'customer_id' => 'required|exists:users,id',
                'description' => 'nullable|string',
            ]);

            DB::transaction(function () use ($request) {
                // Retrieve or create wallet
                $wallet = WalletModel::firstOrCreate(
                    ['user_id' => $request->customer_id],
                    ['balance' => 0]
                );

                // Update balance atomically
                $wallet->increment('balance', $request->amount);

                // Create transaction log
                WalletTransactionLogModel::create([
                    'user_id'        => $request->customer_id,
                    'user_wallet_id' => $wallet->id,
                    'type'           => 'credit',
                    'amount'         => $request->amount,
                    'description'    => $request->description,
                ]);
            });

            return redirect()
                ->route('admin.wallet.index')
                ->with('success', 'Amount added successfully');
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

    public function deductAmount(Request $request) {
        if ($request->isMethod('post')) {
            // Validation
            $request->validate([
                'amount'      => 'required|numeric|min:1',
                'customer_id' => 'required|exists:users,id',
                'description' => 'nullable|string',
            ]);

            DB::transaction(function () use ($request) {
                // Retrieve wallet
                $wallet = WalletModel::where('user_id', $request->customer_id)->first();

                if (!$wallet || $wallet->balance < $request->amount) {
                    throw new \Exception('Insufficient balance');
                }

                // Update balance atomically
                $wallet->decrement('balance', $request->amount);

                // Create transaction log
                WalletTransactionLogModel::create([
                    'user_id'        => $request->customer_id,
                    'user_wallet_id' => $wallet->id,
                    'type'           => 'debit',
                    'amount'         => $request->amount,
                    'description'    => $request->description,
                ]);
            });

            return redirect()
                ->route('admin.wallet.index')
                ->with('success', 'Amount deducted successfully');
        }

        $data['customers'] = User::where('status', 1)->where('user_type', 'normal')->orderBy('id', 'desc')->get();
        return view('admin.wallets.deduct', $data);
    }
}
