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
use App\Models\Admin\BettingProvidersModel;
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

    // public function playGameApi($id, $time_id = null)
    // {
    //     $gameModel = new CreateGameScheduleModel();

    //     $schedules = $gameModel->prepareGameData($id);

    //     $closeMinutes = (int) CloseTime::pluck('minutes')->first();

    //     $slots = CreateGameScheduleModel::with(['digitMaster', 'providerSlot'])
    //         ->where('betting_providers_id', $id)
    //         ->whereDate('created_at', today())
    //         ->when(!is_null($time_id), function ($query) use ($time_id) {
    //             $query->where('slot_time_id', $time_id);
    //         })
    //         ->get();

    //     $gameSlots = $slots->groupBy(function ($item) {
    //         $digitType     = $item->digitMaster?->type ?? 'unknown';
    //         $winningAmount = $item->providerSlot?->winning_amount ?? 0;
    //         $amount        = $item->amount ?? 0;

    //         return implode('_', [
    //             $digitType,
    //             $amount,
    //             $winningAmount
    //         ]);
    //     });

    //     $show_slot = 0;

    //     foreach ($schedules as $schedule) {
    //         if (
    //             $schedule->betting_providers_id == $id &&
    //             $time_id == $schedule->id
    //         ) {
    //             $scheduleTime = $schedule->time ?? $schedule->start_time ?? null;

    //             if ($scheduleTime) {
    //                 $scheduleDateTime = Carbon::parse($scheduleTime);
    //                 $closeDateTime = $scheduleDateTime->copy()->subMinutes($closeMinutes);

    //                 if (now()->lessThan($closeDateTime)) {
    //                     $show_slot = 1;
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'success'        => true,
    //         'close_minutes'  => $closeMinutes,
    //         'slot_time_id'   => $time_id,
    //         'show_slot'      => $show_slot,
    //         'gameSlots'      => $gameSlots,
    //     ]);
    // }

    public function playGameApi($id, $time_id = null)
    {
        $gameModel = new CreateGameScheduleModel();

        $schedules = $gameModel->prepareGameData($id);

        $closeMinutes = (int) CloseTime::pluck('minutes')->first();

        // ✅ Get betting provider name
        $bettingProvider = BettingProvidersModel::select('id', 'name')
            ->where('id', $id)
            ->first();

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
            'success'               => true,
            'betting_provider_id'   => $id,
            'betting_provider_name' => $bettingProvider?->name, // ✅ added
            'close_minutes'         => $closeMinutes,
            'slot_time_id'          => $time_id,
            'show_slot'             => $show_slot,
            'gameSlots'             => $gameSlots,
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
        $perPage = $request->get('per_page', 10);

        $rows = DB::table('customer_orders')
            ->leftJoin('customer_order_items', 'customer_orders.id', '=', 'customer_order_items.order_id')
            ->leftJoin('schedule_providers_slot_time', 'schedule_providers_slot_time.id', '=', 'customer_order_items.game_id')
            ->leftJoin('digit_master', 'digit_master.id', '=', 'schedule_providers_slot_time.digit_master_id')
            ->leftJoin('betting_providers', 'betting_providers.id', '=', 'schedule_providers_slot_time.betting_providers_id')
            ->where('customer_orders.user_id', Auth::id())
            ->orderByDesc('customer_orders.created_at')
            ->select([
                'customer_orders.id as order_id',
                'customer_orders.total_amount',
                'customer_orders.opening_balance',
                'customer_orders.closing_balance',
                'customer_orders.bonus_opening_balance',
                'customer_orders.bonus_closing_balance',
                'customer_orders.created_at as order_date',

                'customer_order_items.id as order_item_id',
                'customer_order_items.digits',
                'customer_order_items.quantity',
                'customer_order_items.amount',
                'customer_order_items.win_amount',
                'customer_order_items.win_status',

                'schedule_providers_slot_time.slot_time',
                'betting_providers.name as provider_name',
                'digit_master.name as game_digits'
            ])
            ->paginate($perPage);

        // ✅ GROUP BY ORDER ID
        $grouped = collect($rows->items())
            ->groupBy('order_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'order_id' => $first->order_id,
                    'order_date' => $first->order_date,
                    'total_amount' => $first->total_amount,
                    'opening_balance' => $first->opening_balance,
                    'closing_balance' => $first->closing_balance,
                    'bonus_opening_balance' => $first->bonus_opening_balance,
                    'bonus_closing_balance' => $first->bonus_closing_balance,

                    'items' => $items->map(function ($item) {
                        return [
                            'provider' => $item->provider_name,
                            'slot_time' => $item->slot_time,
                            'digit_type' => $item->game_digits,
                            'entered_digit' => $item->digits,
                            'quantity' => $item->quantity,
                            'amount' => $item->amount,
                            'win_amount' => $item->win_amount,
                            'win_status' => $item->win_status
                        ];
                    })->values()
                ];
            })->values();

        return response()->json([
            'success' => true,
            'pagination' => [
                'current_page' => $rows->currentPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
                'last_page' => $rows->lastPage(),
            ],
            'data' => $grouped
        ]);
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
                'bonus_balance' => $user->wallet?->bonus_amount ?? 0,
                'order_count'  => CustomerOrder::where('user_id', $user->id)->count(),
            ]
        ]);
    }
    public function walletBonusBalance()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'wallet_balance' => $user->wallet?->balance ?? 0,
                'bonus_balance'  => $user->wallet?->bonus_amount ?? 0
            ]
        ]);
    }

    public function checkWallet(Request $request)
    {
        $userId = Auth::id();
        $cart = $request->input('cart', []);

        $walletTotal = 0; // all games
        $bonusTotal  = 0; // only is_default games

        // 1️⃣ Calculate totals from cart
        foreach ($cart as $item) {

            $game = \App\Models\ScheduleProviderSlotTime::with('getProvider')
                ->find($item['game_id']);

            if (!$game || !$game->getProvider) {
                continue;
            }

            $qty = (int) ($item['quantity'] ?? 1);
            $amt = (float) ($item['amount'] ?? 0);
            $itemTotal = $qty * $amt;

            // bonus ONLY for default games
            if ($game->getProvider->is_default == 1) {
                $bonusTotal += $itemTotal;
            }else{
                $walletTotal += $itemTotal;
            }
        }

        // 2️⃣ Add current request item
        if (!empty($request->newItem)) {

            $game = \App\Models\ScheduleProviderSlotTime::with('getProvider')
                ->find($request->newItem['game_id']);

            if (!$game || !$game->getProvider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid game'
                ]);
            }

            $qty = (int) ($request->newItem['quantity'] ?? 1);
            $amt = (float) ($request->newItem['amount'] ?? 0);
            $itemTotal = $qty * $amt;

            // bonus only if default game
            if ($game->getProvider->is_default == 1) {
                $bonusTotal += $itemTotal;
            }else{
                // always added to wallet requirement
                $walletTotal += $itemTotal;
            }
        }
        $walletTotal += $bonusTotal;
        // 3️⃣ MAIN wallet check (FULL amount)
        if (WalletValidationService::hasSufficientBalance($userId, $walletTotal)) {
            return response()->json([
                'success' => true,
                'message' => 'Sufficient wallet balance'
            ]);
        }

        // 4️⃣ BONUS check (ONLY default game amount)
        if (
            $bonusTotal > 0 && $game->getProvider->is_default == 1 &&
            WalletValidationService::hasSufficientBonusBalance($userId, $bonusTotal)
        ) {
            return response()->json([
                'success' => true,
                'message' => 'Using bonus for default games'
            ]);
        }

        // ❌ Not enough
        return response()->json([
            'success' => false,
            'message' => 'Insufficient balance'
        ]);
    }

    public function placeOrder(Request $request)
    {
        $userId = Auth::id();
        $cart = $request->input('cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty']);
        }

        $user = \App\Models\User::findOrFail($userId);
        $wallet = $user->wallet;

        $walletDeduct = 0;
        $bonusDeduct = 0;

        $orderTotal = 0;
        $paidItems = [];

        foreach ($cart as $item) {
            $game = \App\Models\ScheduleProviderSlotTime::with('getProvider')->find($item['game_id']);
            if (!$game || !$game->getProvider) continue;

            $itemTotal = $item['quantity'] * $item['amount'];
            $orderTotal += $itemTotal;

            if ($game->getProvider->is_default == 1) {
                // Default game: use wallet first, then bonus
                if ($wallet->balance >= $itemTotal) {
                    $walletDeduct += $itemTotal;
                    $wallet->balance -= $itemTotal;
                } else {
                    $walletDeduct += $wallet->balance;
                    $bonusPart = $itemTotal - $wallet->balance;
                    $wallet->balance = 0;
                    if ($wallet->bonus_amount < $bonusPart) {
                        return response()->json(['success' => false, 'message' => 'Insufficient bonus for default games']);
                    }
                    $bonusDeduct += $bonusPart;
                    $wallet->bonus_amount -= $bonusPart;
                }
            } else {
                // Non-default: wallet only
                if ($wallet->balance < $itemTotal) {
                    return response()->json(['success' => false, 'message' => 'Insufficient wallet for non-default games']);
                }
                $walletDeduct += $itemTotal;
                $wallet->balance -= $itemTotal;
            }

            $paidItems[] = $item;
        }

        // Create order
        $order = \App\Models\CustomerOrdersModel::create([
            'user_id' => $userId,
            'total_amount' => $orderTotal,
            'status' => 'pending',
            'opening_balance' => $wallet->balance + $walletDeduct,
            'closing_balance' => $wallet->balance,
            'bonus_opening_balance' => $wallet->bonus_amount + $bonusDeduct,
            'bonus_closing_balance' => $wallet->bonus_amount
        ]);

        // Order items
        foreach ($paidItems as $item) {
            \App\Models\CustomerOrderItemModel::create([
                'order_id' => $order->id,
                'game_id' => $item['game_id'],
                'digits' => $item['digits'],
                'quantity' => $item['quantity'],
                'amount' => $item['quantity'] * $item['amount']
            ]);
        }

        $wallet->save();

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $order->id
        ]);
    }


    // public function checkWallet(Request $request)
    // {
    //     $userId = Auth::id();

    //     $defaultTotal    = 0; // bonus eligible
    //     $nonDefaultTotal = 0; // wallet only

    //     /*
    //     |--------------------------------------------------------------------------
    //     | NORMALIZE CART ITEMS
    //     |--------------------------------------------------------------------------
    //     */
    //     $items = [];

    //     // Case 1: full cart array sent
    //     if (is_array($request->cart)) {
    //         $items = $request->cart;

    //         // dd($items);
    //     }

    //     // Case 2: single item sent as "data"
    //     if ($request->filled('data')) {
    //         $items[] = $request->data;
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | CALCULATE TOTALS
    //     |--------------------------------------------------------------------------
    //     */
    //     foreach ($items as $item) {
    //         // dd($item);
    //         if (!isset($item['game_id'])) {
    //             continue;
    //         }

    //         $game = ScheduleProviderSlotTime::with('getProvider')
    //             ->find($item['game_id']);

    //         if (!$game || !$game->getProvider) {
    //             continue;
    //         }

    //         $qty   = (int) ($item['quantity'] ?? 1);
    //         $amt   = (float) ($item['amount'] ?? 0);
    //         $total = $qty * $amt;

    //         if ($game->getProvider->is_default == 1) {
    //             $defaultTotal += $total;
    //         } else {
    //             $nonDefaultTotal += $total;
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | WALLET CHECK
    //     |--------------------------------------------------------------------------
    //     */
    //     $user   = User::findOrFail($userId);
    //     $wallet = $user->wallet;

    //     $grandTotal = $defaultTotal + $nonDefaultTotal;

    //     // 1️⃣ Wallet covers everything
    //     if ($wallet->balance >= $grandTotal) {
    //         return response()->json([
    //             'success' => true,
    //             'type'    => 'wallet',
    //             'message' => 'Sufficient wallet balance',
    //             'required' => [
    //                 'wallet' => $grandTotal,
    //                 'bonus'  => 0
    //             ]
    //         ]);
    //     }

    //     // 2️⃣ Wallet + Bonus split
    //     if (
    //         $wallet->balance >= $nonDefaultTotal &&
    //         $wallet->bonus_amount >= $defaultTotal
    //     ) {
    //         return response()->json([
    //             'success' => true,
    //             'type'    => 'wallet+bonus',
    //             'message' => 'Wallet and bonus sufficient',
    //             'required' => [
    //                 'wallet' => $nonDefaultTotal,
    //                 'bonus'  => $defaultTotal
    //             ]
    //         ]);
    //     }

    //     // ❌ Insufficient
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Insufficient wallet or bonus balance',
    //         'required' => [
    //             'wallet' => $nonDefaultTotal,
    //             'bonus'  => $defaultTotal
    //         ],
    //         'available' => [
    //             'wallet' => $wallet->balance,
    //             'bonus'  => $wallet->bonus_amount
    //         ]
    //     ], 400);
    // }

}
