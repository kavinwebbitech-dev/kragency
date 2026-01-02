<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrderItemModel;
use App\Models\Admin\DigitMasterModel;
use Carbon\Carbon;

class CustomerOrderListController extends Controller
{
    /* ======================================================
       PAGE LOAD
    ====================================================== */
  public function index()
{
    $today = Carbon::today();

    $orders = CustomerOrderItemModel::with([
        'scheduleProviderSlotTime',
        'scheduleProviderSlotTime.getProvider'
    ])->whereDate('created_at', $today)->get();

    return view('admin.customer_orders.index', [
        'orders' => $orders,
        'todayTotalOrders' => $orders->count(),
        'todayTotalAmount' => $orders->sum('amount'),
        'todayWinningAmount' => $orders->sum('win_amount'),
    ]);
}


    /* ======================================================
       DATATABLE DATA
    ====================================================== */
    public function data(Request $request)
    {
        $today = Carbon::today();

        $query = CustomerOrderItemModel::with([
            'customerOrders.user',
            'scheduleProviderSlotTime',
            'scheduleProviderSlotTime.getProvider'
        ])->whereDate('created_at', $today);

        /* ---------- Filters ---------- */
        if ($request->filled('provider')) {
            $query->whereHas('scheduleProviderSlotTime.getProvider',
                fn($q) => $q->where('name', $request->provider)
            );
        }

        if ($request->filled('time')) {
            $query->whereHas('scheduleProviderSlotTime',
                fn($q) => $q->whereRaw(
                    "DATE_FORMAT(slot_time,'%h:%i %p')=?",
                    [$request->time]
                )
            );
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('customerOrders.user',
                fn($q)=>$q->where('name','like',"%{$request->customer_name}%")
            );
        }

        /* ---------- Datatable ---------- */
        $draw   = (int) $request->draw;
        $start  = (int) $request->start;
        $length = (int) $request->length;
        $search = $request->input('search.value');

        $total = $query->count();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('digits','like',"%{$search}%")
                  ->orWhereHas('customerOrders.user',
                        fn($q2)=>$q2->where('name','like',"%{$search}%")
                                  ->orWhere('mobile','like',"%{$search}%")
                  );
            });
        }

        $filtered = $query->count();

        $orders = $query->orderBy('created_at','desc')
                        ->offset($start)
                        ->limit($length)
                        ->get();

        /* ---------- Digit Master Map ---------- */
        $digitMap = DigitMasterModel::select('type','name')
            ->orderBy('id')
            ->get()
            ->groupBy('type')
            ->map(fn($row)=>
                preg_replace('/\s*\(.*?\)/','',$row->first()->name)
            )->toArray();

        /* ---------- Response ---------- */
        $data = [];

        foreach ($orders as $i => $order) {
            $digits = $order->digits ?? '';
            $type   = strlen($digits);
            $label  = $digitMap[$type] ?? '';
            $digitAdded = $digits . ($label ? " ({$label})" : '');

            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'customer_name' => $order->customerOrders->user->name ?? '-',
                'mobile' => $order->customerOrders->user->mobile ?? '-',
                'provider' => $order->scheduleProviderSlotTime->getProvider->name ?? '',
                'time' => optional($order->scheduleProviderSlotTime)->slot_time
                            ? Carbon::parse($order->scheduleProviderSlotTime->slot_time)->format('h:i A')
                            : '',
                'digit_added' => $digitAdded,
                'quantity' => $order->quantity ?? 0,
                'amount' => $order->amount ?? 0,
                'order_date' => $order->created_at->format('M d, Y h:i A'),
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    /* ======================================================
       EXPORT CSV
    ====================================================== */
    public function exportCsv(Request $request)
    {
        $today = Carbon::today();

        $query = CustomerOrderItemModel::with([
            'customerOrders.user',
            'scheduleProviderSlotTime',
            'scheduleProviderSlotTime.getProvider'
        ])->whereDate('created_at', $today);

        /* ---------- Filters ---------- */
        if ($request->filled('provider')) {
            $query->whereHas('scheduleProviderSlotTime.getProvider',
                fn($q)=>$q->where('name',$request->provider)
            );
        }

        if ($request->filled('time')) {
            $query->whereHas('scheduleProviderSlotTime',
                fn($q)=>$q->whereRaw(
                    "DATE_FORMAT(slot_time,'%h:%i %p')=?",
                    [$request->time]
                )
            );
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('customerOrders.user',
                fn($q)=>$q->where('name','like',"%{$request->customer_name}%")
            );
        }

        $orders = $query->orderBy('created_at','desc')->get();

        /* ---------- Digit Map ---------- */
        $digitMap = DigitMasterModel::select('type','name')
            ->orderBy('id')
            ->get()
            ->groupBy('type')
            ->map(fn($row)=>
                preg_replace('/\s*\(.*?\)/','',$row->first()->name)
            )->toArray();

        $fileName = 'today_customer_orders_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $columns = [
            '#','Customer Name','Mobile','Provider','Time',
            'Digit Added','Quantity','Amount','Order Date'
        ];

        $callback = function () use ($orders, $columns, $digitMap) {
            $file = fopen('php://output','w');
            fputcsv($file, $columns);

            foreach ($orders as $i => $order) {
                $digits = $order->digits ?? '';
                $type   = strlen($digits);
                $label  = $digitMap[$type] ?? '';
                $digitAdded = $digits . ($label ? " ({$label})" : '');

                fputcsv($file, [
                    $i + 1,
                    $order->customerOrders->user->name ?? '-',
                    $order->customerOrders->user->mobile ?? '-',
                    $order->scheduleProviderSlotTime->getProvider->name ?? '',
                    optional($order->scheduleProviderSlotTime)->slot_time
                        ? Carbon::parse($order->scheduleProviderSlotTime->slot_time)->format('h:i A')
                        : '',
                    '="' . $digitAdded . '"',
                    $order->quantity ?? 0,
                    $order->amount ?? 0,
                    $order->created_at->format('M d, Y h:i A'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
