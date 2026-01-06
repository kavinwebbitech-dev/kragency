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
use App\Models\CreateGameScheduleModel;
use App\Models\Admin\SliderModel;

class CustomerController extends Controller
{
    /* ---------------- PUBLIC ---------------- */
    public function index(Request $request)
    {
        $gameModel = new CreateGameScheduleModel();

        $schedules = $gameModel->getGameSchedule()
            ->sortByDesc('is_default')
            ->values();

        return response()->json([
            'success' => true,
            'current_time' => Carbon::now()->toDateTimeString(),
            'default_provider' => $schedules->firstWhere('is_default', 1),
            'schedules' => $schedules,
            'sliders' => SliderModel::where('status', true)
                ->orderBy('order')
                ->get()
        ]);
    }
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

    public function playGameApi($id, $time_id = null)
    {
        $gameModel = new CreateGameScheduleModel();

        $schedules = $gameModel->prepareGameData($id);

        $closeMinutes = (int) CloseTime::pluck('minutes')->first();

        $slots = CreateGameScheduleModel::with(['digitMaster', 'providerSlot'])
            ->where('betting_providers_id', $id)
            ->whereDate('created_at', today())
            ->when(!is_null($time_id), function ($query) use ($time_id) {
                $query->where('slot_time_id', $time_id);
            })
            ->get();

        $gameSlots = $slots->groupBy(function ($item) {
            $digitType     = $item->digitMaster?->type ?? 'unknown';
            $winningAmount = $item->providerSlot?->winning_amount ?? 0;
            $amount        = $item->amount ?? 0;

            return implode('_', [
                $digitType,
                $amount,
                $winningAmount
            ]);
        });

        $show_slot = 0;

        foreach ($schedules as $schedule) {
            if (
                $schedule->betting_providers_id == $id &&
                $time_id == $schedule->id
            ) {
                $scheduleTime = $schedule->time ?? $schedule->start_time ?? null;

                if ($scheduleTime) {
                    $scheduleDateTime = Carbon::parse($scheduleTime);
                    $closeDateTime = $scheduleDateTime->copy()->subMinutes($closeMinutes);

                    if (now()->lessThan($closeDateTime)) {
                        $show_slot = 1;
                    }
                }
            }
        }

        return response()->json([
            'success'        => true,
            'close_minutes'  => $closeMinutes,
            'slot_time_id'   => $time_id,
            'show_slot'      => $show_slot,
            'gameSlots'      => $gameSlots,
        ]);
    }




    /* ---------------- AUTH REQUIRED ---------------- */

    public function wallet()
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
    public function getCart()
    {
        $userId = Auth::id();

        $cart = Session::get("lotteryCart.$userId", []);

        return response()->json([
            'success' => true,
            'data' => $cart
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

    // public function paymentHistory(Request $request)
    // {
    //     try {
    //         $userId = Auth::id();

    //         $perPage = $request->get('per_page', 10);
    //         $currentPage = $request->get('page', 1);

    //         $transactions = User::where('id', $userId)
    //             ->firstOrFail()
    //             ->walletTransactions()
    //             ->orderByDesc('created_at')
    //             ->paginate($perPage, ['*'], 'page', $currentPage);

    //         return response()->json([
    //             'success' => true,
    //             'pagination' => [
    //                 'current_page' => $transactions->currentPage(),
    //                 'per_page' => $transactions->perPage(),
    //                 'total' => $transactions->total(),
    //                 'last_page' => $transactions->lastPage()
    //             ],
    //             'data' => $transactions->map(function ($txn) {
    //                 return [
    //                     // 'transaction_id' => $txn->id,
    //                     'date' => $txn->created_at->format('Y-m-d H:i:s'),
    //                     'type' => $txn->type,
    //                     // 'balance' => $txn->balance,
    //                     'description' => $txn->description,
    //                     'amount' => $txn->amount,

    //                 ];
    //             })
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error($e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve payment history'
    //         ], 500);
    //     }
    // }

    public function paymentHistory(Request $request)
    {
        try {
            $userId = Auth::id();

            $perPage = $request->get('per_page', 10);
            $currentPage = $request->get('page', 1);

            $user = User::findOrFail($userId);

            $transactions = $user->walletTransactions()
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $currentPage);

            // totals
            $totalAmount = $user->walletTransactions()->sum('amount');

            $bonusAmount = $user->walletTransactions()->sum('bonus_amount');

            return response()->json([
                'success' => true,

                'summary' => [
                    'total_amount' => $totalAmount,
                    'bonus_amount' => $bonusAmount
                ],

                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page'     => $transactions->perPage(),
                    'total'        => $transactions->total(),
                    'last_page'    => $transactions->lastPage()
                ],

                'data' => $transactions->map(function ($txn) {
                    return [
                        'date'        => $txn->created_at->format('Y-m-d H:i:s'),
                        'type'        => $txn->type,
                        'description' => $txn->description,
                        'amount'      => $txn->amount
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
    public function customerOrderDetails(Request $request)
    {
        try {
            $userId = Auth::id();

            $perPage = $request->get('per_page', 10);
            $currentPage = $request->get('page', 1);

            $orders = DB::table('customer_orders')
                ->leftJoin('customer_order_items', 'customer_orders.id', '=', 'customer_order_items.order_id')
                ->leftJoin('schedule_providers_slot_time', 'schedule_providers_slot_time.id', '=', 'customer_order_items.game_id')
                ->leftJoin('digit_master', 'digit_master.id', '=', 'schedule_providers_slot_time.digit_master_id')
                ->leftJoin('betting_providers', 'betting_providers.id', '=', 'schedule_providers_slot_time.betting_providers_id')
                ->where('customer_orders.user_id', $userId)
                ->orderByDesc('customer_orders.created_at')
                ->select([
                    'customer_orders.id as id',
                    'customer_orders.created_at as order_date',
                    'betting_providers.name as provider',
                    'schedule_providers_slot_time.slot_time as time',
                    'digit_master.name as digit',
                    'customer_order_items.digits as entered_digit',
                    'customer_order_items.quantity as quantity',
                    'customer_order_items.amount as price',
                    'customer_order_items.win_amount as winning_status',
                ])
                ->paginate($perPage, ['*'], 'page', $currentPage);

            return response()->json([
                'success' => true,

                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total(),
                    'last_page'    => $orders->lastPage()
                ],

                'data' => $orders->items()
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order history'
            ], 500);
        }
    }
    public function addToCart(Request $request)
    {
        $request->validate([
            'item.provider_id'    => 'required|integer',
            'item.slot_time_id'   => 'required|integer',
            'item.game_type'      => 'required|string',
            'item.digit_type'     => 'required|string',
            'item.digits'         => 'required|string',
            'item.quantity'       => 'required|integer|min:1',
            'item.amount'         => 'required|numeric',
            'item.winning_amount' => 'required|numeric',
        ]);

        $userId = Auth::id();
        $item = $request->item;

        $cart = Session::get("lotteryCart.$userId", []);
        $cart[] = $item;

        Session::put("lotteryCart.$userId", $cart);

        return response()->json([
            'success' => true,
            'cart_count' => count($cart),
            'cart' => $cart
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
    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'data' => [
                'name'         => $user->name,
                'mobile'       => $user->mobile,
                'wallet_balance' => $user->wallet?->balance ?? 0,
                'bonus_amount' => $user->wallet?->bonus_amount ?? 0,
                'order_count'  => CustomerOrder::where('user_id', $user->id)->count(),
            ]
        ]);
    }
}
