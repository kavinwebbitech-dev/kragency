<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\Admin\WalletTransactionLogModel; 
use Yajra\DataTables\DataTables;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;


class UserController extends Controller
{
    public function index()
    {
        //$users = User::all();
        return view('admin.users.list');
    }

    public function dashboard() {
        Artisan::call('optimize:clear');
        // Active users (status = 1)
        $activeUsers = \App\Models\User::where('status', 1)->count();

        // Total ordered amount
        $totalOrderedAmount = \App\Models\CustomerOrder::sum('total_amount');

        // Total winning amount (sum of win_amount in CustomerOrderItemModel)
        $totalWinningAmount = \App\Models\CustomerOrderItemModel::whereNotNull('win_amount')->sum('win_amount');

        return view('admin.dashboard', compact('activeUsers', 'totalOrderedAmount', 'totalWinningAmount'));
    }

    public function getTableData(Request $request)
    {
        if ($request->ajax()) {
            $status = $request->get('status', 'active');
            $query = User::query();

            if ($status === 'active') {
                $query->where('status', 1);
            } elseif ($status === 'inactive') {
                $query->where('status', 0);
            } elseif ($status === 'deleted') {
                $query->onlyTrashed();
            }

            $query->where('user_type', 'normal')->orderBy('id', 'desc');
            // eager load bank details existence
            $query->with(['bankDetail' => function($q) { $q->select('id','user_id'); }]);

            return DataTables::of($query)
                ->editColumn('status', function($user) {
                    if ($user->status == 1) return 'Active';
                    if ($user->status == 0) return 'Inactive';
                    return 'Deleted';
                })
                ->addColumn('has_bank_details', function($user) {
                    return $user->bankDetail ? true : false;
                })
                ->make(true);
        }
    }

    public function create(Request $request) {
        if ($request->isMethod('post')) {
            //do insert operation here
             $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'mobile' => [
                    'required',
                    'string',
                    'regex:/^[0-9]{10}$/',
                    'unique:'.User::class,
                ],
                'password' => ['required', 'confirmed'],
                'status' => ['required', 'in:0,1']
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));
            return redirect(route('admin.users.index', absolute: false));
        }


        return view('admin.users.create');
    }

    public function edit($id, Request $request) {
        $user = User::find($id);

        if($user == null) {
            return redirect(route('admin.users.index', absolute: false));
        }   

        // Update Method
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['string', 'email', 'max:255', 'unique:users,email,'.$user->id],
                'mobile' => [
                    'required',
                    'string',
                    'regex:/^[0-9]{10}$/',
                    'unique:users,mobile,'.$user->id,
                ],
                'status' => ['required', 'in:0,1'],
                'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            ]);


            if($request->password!= null) {
                $user->password = Hash::make($request->password);
            }
            $user->name = $request->name;
            $user->email = $request->email;
            $user->status = $request->status;
            $user->mobile = $request->mobile;
            $user->save();
            return redirect(route('admin.users.index', absolute: false));
        }

        return view('admin.users.edit', compact('user'));
    }

    public function viewTransaction($id) {
        $user = User::find($id);

        if($user == null) {
            return redirect(route('admin.users.index', absolute: false));
        }

        return view('admin.users.view-transaction', compact('user'));

    }

    public function getTransactionData($id, Request $request)
    {
        if ($request->ajax()) {
            $transactions = WalletTransactionLogModel::where('user_id', $id)->orderBy('created_at', 'desc');

            return DataTables::of($transactions)
                ->addColumn('formatted_date', function($transaction) {
                    return $transaction->created_at->format('d-m-Y');
                })
                ->rawColumns(['formatted_date'])
                ->make(true);
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }
    
    /**
     * Return bank details for a specific user (admin AJAX endpoint).
     */
    public function bankDetails($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $bankDetail = \App\Models\BankDetail::where('user_id', $id)->first();

        if (!$bankDetail) {
            return response()->json(['has' => false, 'bank' => null]);
        }

        return response()->json(['has' => true, 'bank' => $bankDetail]);
    }

}