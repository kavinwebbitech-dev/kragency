<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CloseTime;
use App\Models\CustomerOrdersModel;
use App\Models\CustomerOrderItemModel;
use App\Models\ScheduleProviderSlotTime;
use App\Services\WalletValidationService;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{
    /* ---------------- PUBLIC ---------------- */

    public function results()
    {
        return response()->json([
            'success' => true,
            'data' => DB::table('schedule_provider')
                ->leftJoin('betting_providers', 'betting_providers.id', '=', 'schedule_provider.betting_providers_id')
                ->leftJoin('provider_times', 'provider_times.id', '=', 'schedule_provider.slot_time_id')
                ->select(
                    'betting_providers.name as provider',
                    'provider_times.time',
                    'schedule_provider.result',
                    'schedule_provider.created_at'
                )
                ->whereDate('schedule_provider.created_at', today())
                ->orderByDesc('schedule_provider.created_at')
                ->get()
        ]);
    }

    public function getResultsTable()
    {
        return $this->results();
    }

    public function playGame($providerId, $timeId = null)
    {
        $closeMinutes = CloseTime::value('minutes');

        $slots = DB::table('schedule_providers_slot_time')
            ->leftJoin('digit_master', 'digit_master.id', '=', 'schedule_providers_slot_time.digit_master_id')
            ->leftJoin('provider_slots', 'provider_slots.id', '=', 'schedule_providers_slot_time.provider_slot_id')
            ->where('schedule_providers_slot_time.betting_providers_id', $providerId)
            ->when($timeId, fn($q) => $q->where('schedule_providers_slot_time.slot_time_id', $timeId))
            ->whereDate('schedule_providers_slot_time.created_at', today())
            ->get();

        return response()->json([
            'success' => true,
            'close_minutes' => $closeMinutes,
            'data' => $slots
        ]);
    }

    /* ---------------- AUTH REQUIRED ---------------- */

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'wallet_balance' => $user->wallet?->balance ?? 0
        ]);
    }
    public function viewcart()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $cart = Session::get("lotteryCart.$user->id", []);

        $closeMinutes = \App\Models\CloseTime::pluck('minutes')->first();
        $now = now();
        $filteredCart = [];

        foreach ($cart as $item) {
            if (!isset($item['game_id'])) continue;

            $slot = \App\Models\ScheduleProviderSlotTime::find($item['game_id']);
            if (!$slot) continue;

            $slotDateTime = \Carbon\Carbon::parse($slot->slot_time);
            $closeDateTime = $slotDateTime->subMinutes($closeMinutes);

            if ($now->lessThan($closeDateTime)) {
                $filteredCart[] = $item;
            }
        }

        Session::put("lotteryCart.$user->id", $filteredCart);

        return response()->json([
            'success' => true,
            'cart' => $filteredCart,
            'count' => count($filteredCart)
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        try {
            $user = Auth::user();
            $closeMinutes = CloseTime::value('minutes');
            $now = now();
            $total = 0;
            $validItems = [];

            foreach ($request->items as $item) {
                $slot = ScheduleProviderSlotTime::find($item['game_id']);
                if (!$slot) continue;

                $closeTime = Carbon::parse($slot->slot_time)->subMinutes($closeMinutes);
                if ($now->gte($closeTime)) continue;

                $amount = $item['quantity'] * $item['amount'];
                $total += $amount;
                $validItems[] = $item;
            }

            if (!$validItems) {
                return response()->json(['success' => false, 'message' => 'Slot closed'], 422);
            }

            if (!WalletValidationService::hasSufficientBalance($user->id, $total)) {
                return response()->json(['success' => false, 'message' => 'Insufficient balance'], 422);
            }

            $order = CustomerOrdersModel::create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'opening_balance' => $user->wallet->balance,
                'closing_balance' => $user->wallet->balance - $total,
                'status' => 'pending'
            ]);

            foreach ($validItems as $item) {
                CustomerOrderItemModel::create([
                    'order_id' => $order->id,
                    'game_id' => $item['game_id'],
                    'digits' => $item['digits'],
                    'quantity' => $item['quantity'],
                    'amount' => $item['quantity'] * $item['amount']
                ]);
            }

            $user->wallet->decrement('balance', $total);

            return response()->json([
                'success' => true,
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Order failed'], 500);
        }
    }

    public function paymentHistory(Request $request)
    {
        try {
            $userId = Auth::id();

            $perPage = $request->get('per_page', 10);
            $currentPage = $request->get('page', 1);

            $transactions = User::where('id', $userId)
                ->firstOrFail()
                ->walletTransactions()
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $currentPage);

            return response()->json([
                'success' => true,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage()
                ],
                'data' => $transactions->map(function ($txn) {
                    return [
                        // 'transaction_id' => $txn->id,
                        'date' => $txn->created_at->format('Y-m-d H:i:s'),
                        'type' => $txn->type,
                        // 'balance' => $txn->balance,
                        'description' => $txn->description,
                        'amount' => $txn->amount,

                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment history'
            ], 500);
        }
    }
    public function customerOrderDetails()
    {
        $results = DB::table('customer_orders')
            ->leftJoin('customer_order_items', 'customer_orders.id', '=', 'customer_order_items.order_id')
            ->leftJoin('schedule_providers_slot_time', 'schedule_providers_slot_time.id', '=', 'customer_order_items.game_id')
            ->leftJoin('digit_master', 'digit_master.id', '=', 'schedule_providers_slot_time.digit_master_id')
            ->leftJoin('betting_providers', 'betting_providers.id', '=', 'schedule_providers_slot_time.betting_providers_id')
            ->where('customer_orders.user_id', Auth::id())
            ->orderBy('customer_orders.created_at', 'desc')
            ->select([
                'customer_orders.id as id',
                'customer_orders.created_at as order_date',
                'betting_providers.name as provider',
                'schedule_providers_slot_time.slot_time as time',
                'digit_master.name as digit',
                'customer_order_items.digits as entered_digit',
                'customer_order_items.quantity as quantity',
                'customer_order_items.amount as price',
                'customer_order_items.win_status as winning_status'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }


    public function removeFromCart(Request $request)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false], 401);
        }

        $cart = Session::get("lotteryCart.$userId", []);

        if (!isset($cart[$request->index])) {
            return response()->json(['success' => false], 404);
        }

        unset($cart[$request->index]);
        $cart = array_values($cart);

        Session::put("lotteryCart.$userId", $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart
        ]);
    }
}
