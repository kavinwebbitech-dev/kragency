<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\WalletModel;
use App\Models\KycDetail;
use App\Models\Recharge;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RechargeController extends Controller
{
    public function index(Request $request)
    {
        $recharges = Recharge::query();

        if ($request->has('status')) {
            $recharges->where('status', $request->input('status'));
        }
        if ($request->ajax()) {
            $recharges = Recharge::with(['user' => function($query) {
                $query->whereNull('deleted_at');
            }],'userDetail')
            ->whereHas('user', function($query) {
                $query->whereNull('deleted_at');
            })->orderBy((new Recharge)->getTable() . '.updated_at', 'desc')->get();

            return DataTables::of($recharges)
                ->addColumn('name', function($recharge) {
                    return $recharge->user->name;
                })
                ->addColumn('created_at', function($recharge) {
                    return $recharge->created_at->format('Y-m-d H:i');
                })
               ->addColumn('image', function ($recharge) {
                    if ($recharge->image) {
                        $image = asset($recharge->image);

                        return '<a href="'.$image.'" target="_blank">
                                    <img src="'.$image.'" alt="Recharge Image" width="50" style="cursor:pointer;border-radius:4px;">
                                </a>';
                    }

                    return 'No Image';
                })
                ->addColumn('action', function ($recharge) {

                    if (empty($recharge->image)) {
                        return '-';
                    }
                
                    // If already approved
                    if ($recharge->status == 'approved') {
                        return '<span class="badge bg-success">Approved</span>';
                    }
                
                    // If already rejected
                    if ($recharge->status == 'rejected') {
                        return '<span class="badge bg-danger">Rejected</span>';
                    }
                
                    $approveUrl = route('admin.recharges.approve', $recharge->id);
                    $rejectUrl  = route('admin.recharges.reject', $recharge->id);
                
                    return '
                        <span class="d-flex">
                            <a href="'.$approveUrl.'" class="btn btn-success btn-sm mr-1">Approve</a>
                            <a href="'.$rejectUrl.'" class="btn btn-danger btn-sm">Reject</a>
                        </span>
                    ';
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('admin.recharges.index', compact('recharges'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $recharge = Recharge::findOrFail($id);

            // Prevent approving twice
            if ($recharge->status === 'approved') {
                return redirect()
                    ->route('admin.recharge.index')
                    ->with('error', 'Recharge has already been approved.');
            }

            $recharge->update([
                'status' => 'approved',
            ]);

            $wallet = WalletModel::firstOrCreate(
                ['user_id' => $recharge->user_id],
                ['balance' => 0]
            );

            $wallet->increment('balance', $recharge->amount);
        });

        return redirect()
            ->route('admin.recharge.index')
            ->with('success', 'Recharge approved successfully.');
    }

    public function reject($id)
    {
        $recharge = Recharge::findOrFail($id);

        if ($recharge->status === 'rejected') {
            return redirect()
                ->route('admin.recharge.index')
                ->with('error', 'Recharge has already been rejected.');
        }

        $recharge->update([
            'status' => 'rejected',
        ]);

        return redirect()
            ->route('admin.recharge.index')
            ->with('success', 'Recharge rejected successfully.');
    }

    public function uploadQrCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $setting = Setting::firstOrNew([
            'key' => 'qr_code'
        ]);

        if ($setting->value && file_exists(public_path($setting->value))) {
            unlink(public_path($setting->value));
        }

        $image = $request->file('qr_code');

        $fileName = time() . '.' . $image->getClientOriginalExtension();

        $destination = public_path('uploads/qr_codes');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $image->move($destination, $fileName);

        $setting->value = 'uploads/qr_codes/' . $fileName;
        $setting->save();

        return back()->with('success', 'QR Code uploaded successfully.');
    }

    public function kycIndex(Request $request)
    {
      $kycDetails = KycDetail::query()
            ->with('user')
            ->latest();

        if ($request->ajax()) {
            return DataTables::of($kycDetails)
                ->addColumn('name', function ($kyc) {
                    return optional($kyc->user)->name;
                })
                ->editColumn('created_at', function ($kyc) {
                    return $kyc->created_at->format('Y-m-d H:i');
                })
                ->editColumn('updated_at', function ($kyc) {
                    return $kyc->updated_at->format('Y-m-d H:i');
                })
                ->editColumn('status', function ($kyc) {
                    return $kyc->status === 'approved' ? '<span class="badge bg-success">Approved</span>' :
                           ($kyc->status === 'rejected' ? '<span class="badge bg-danger">Rejected</span>' :
                           '<span class="badge bg-warning">Pending</span>');
                })
                ->addColumn('action', function ($kyc) {
                    return '<a href="'.route('admin.kyc.view', $kyc->id).'" class="btn btn-primary btn-sm">View</a>';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('admin.kyc.index', compact('kycDetails'));
    }

    public function kycView($id)
    {
        $kycDetail = KycDetail::with('user')->findOrFail($id);

        return view('admin.kyc.view', compact('kycDetail'));
    }

    public function statusUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $kycDetail = KycDetail::findOrFail($id);
        $kycDetail->status = $request->input('status');
        $kycDetail->save();

        return redirect()->route('admin.kyc.index')->with('success', 'KYC status updated successfully.');
    }
}
