<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\WalletValidationService;
use Illuminate\Support\Facades\Auth;
use App\Models\CreateGameScheduleModel;
use App\Models\CustomerOrdersModel;
use App\Models\CloseTime;
use App\Models\User;
use App\Models\CustomerOrderItemModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{

    public function index(Request $request): View
    {
    
        $gameModel = new CreateGameScheduleModel();
        
        $data['schedules'] = $gameModel->getGameSchedule()
            ->sortByDesc('is_default')
            ->values();
        $data['sliders'] = \App\Models\Admin\SliderModel::where('status', true)->orderBy('order')->get();
        $data['default_provider'] = $data['schedules']->firstWhere('is_default', 1);

        $currentTime = Carbon::now();
        return view('frontend.dashboard', $data);
    }

    public function playGame($id, $time_id = null): View
    {
        $currentTime = Carbon::now();

        $gameModel = new CreateGameScheduleModel();
        $data['schedules'] = $gameModel->prepareGameData($id);
        $data['slot_time_id'] = $time_id;

        $closeMinutes = (int) CloseTime::pluck('minutes')->first();
        $data['close_time'] = $closeMinutes;
        $slots = CreateGameScheduleModel::with('digitMaster', 'providerSlot')
            ->where('betting_providers_id', $id)
            ->whereDate('created_at', today())
            ->when(!is_null($time_id), function ($query) use ($time_id) {
                $query->where('slot_time_id', $time_id);
            })
            ->get();
        $data['gameSlots'] = $slots->groupBy(function ($item) {
            $digitType = $item->digitMaster?->type ?? 'unknown';
            $winningAmount = $item->providerSlot?->winning_amount ?? 0;
            $amount = $item->amount ?? 0;

            return implode('_', [
                $digitType,
                $amount,
                $winningAmount
            ]);
        });


        $show_slot = 0;

        foreach ($data['schedules'] as $schedule) {
            if ($schedule->betting_providers_id == $id && $time_id == $schedule->id) {

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

        $data['show_slot'] = $show_slot;

        return view('frontend.play_now', $data);
    }


    public function placeOrder(Request $request)
    {
        try {
            $userId = Auth::id();
            $cart = Session::get("lotteryCart.$userId", []);

            if (empty($cart)) {
                return response()->json(['success' => false, 'message' => 'Cart is empty']);
            }

            /** -----------------------------
             *  FILTER CLOSED SLOTS
             *  -----------------------------
             */
            $closeMinutes = \App\Models\CloseTime::pluck('minutes')->first();
            $now = now();
            $validCart = [];

            foreach ($cart as $item) {
                $game = \App\Models\ScheduleProviderSlotTime::with('getProvider')
                    ->find($item['game_id']);

                if (!$game) continue;

                $closeTime = \Carbon\Carbon::parse($game->slot_time)
                    ->subMinutes($closeMinutes);

                if ($now->lt($closeTime)) {
                    $validCart[] = $item;
                }
            }

            if (empty($validCart)) {
                return response()->json(['success' => false, 'message' => 'All slots closed']);
            }

            /** -----------------------------
             *  SPLIT CART
             *  -----------------------------
             */
            $defaultItems = [];
            $nonDefaultItems = [];

            $defaultTotal = 0;
            $nonDefaultTotal = 0;

            foreach ($validCart as $item) {
                $game = \App\Models\ScheduleProviderSlotTime::with('getProvider')
                    ->find($item['game_id']);

                if (!$game || !$game->getProvider) continue;

                $total = $item['quantity'] * $item['amount'];

                if ($game->getProvider->is_default == 1) {
                    $defaultItems[] = $item;
                    $defaultTotal += $total;
                } else {
                    $nonDefaultItems[] = $item;
                    $nonDefaultTotal += $total;
                }
            }

            $user = \App\Models\User::find($userId);
            $wallet = $user->wallet;

            /** -----------------------------
             *  PAYMENT LOGIC
             *  -----------------------------
             */
            $paidItems = [];

            // 1️⃣ MAIN WALLET FIRST
            if ($wallet->balance >= ($defaultTotal + $nonDefaultTotal)) {

                $wallet->balance -= ($defaultTotal + $nonDefaultTotal);
                $paidItems = $validCart;
            }
            else {
                // MAIN wallet pays what it can
                $remainingMain = $wallet->balance;
                $wallet->balance = 0;

                // MAIN wallet pays NON-DEFAULT first
                if ($remainingMain >= $nonDefaultTotal) {
                    $remainingMain -= $nonDefaultTotal;
                    $paidItems = $nonDefaultItems;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance'
                    ]);
                }

                // BONUS for DEFAULT
                if ($wallet->bonus_amount >= $defaultTotal) {
                    $wallet->bonus_amount -= $defaultTotal;
                    $paidItems = array_merge($paidItems, $defaultItems);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient bonus balance'
                    ]);
                }
            }

            /** -----------------------------
             *  CREATE ORDER
             *  -----------------------------
            */
            
            $mainClosingBalance  = $wallet->balance;       // already deducted above
            $bonusClosingBalance = $wallet->bonus_amount;  // already deducted above

            $order = CustomerOrdersModel::create([
                'user_id'                => $userId,
                'total_amount'           => $defaultTotal + $nonDefaultTotal,
                'status'                 => 'pending',
                'opening_balance'        => $wallet->balance + $nonDefaultTotal,  // add back what was deducted for clarity
                'closing_balance'        => $mainClosingBalance,
                'bonus_opening_balance'  => $wallet->bonus_amount + $defaultTotal, // add back what was deducted
                'bonus_closing_balance'  => $bonusClosingBalance,
            ]);

            foreach ($paidItems as $item) {
                CustomerOrderItemModel::create([
                    'order_id' => $order->id,
                    'game_id'  => $item['game_id'],
                    'digits'   => $item['digits'],
                    'quantity' => $item['quantity'],
                    'amount'   => $item['quantity'] * $item['amount']
                ]);
            }

            $wallet->save();

            /** -----------------------------
             *  REMOVE ONLY PAID ITEMS
             *  -----------------------------
             */
            $remainingCart = [];

            foreach ($validCart as $item) {
                $paid = collect($paidItems)->contains(
                    fn ($p) => $p['game_id'] == $item['game_id']
                );

                if (!$paid) {
                    $remainingCart[] = $item;
                }
            }

            Session::put("lotteryCart.$userId", $remainingCart);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function paymentHistory(Request $request)
    {

        try {
            $userId = Auth::id();

            $user = User::with(['walletTransactions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($userId);

            $perPage = $request->get('per_page', 10);
            $currentPage = $request->get('page', 1);

            $transactions = $user->walletTransactions()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $currentPage);

            return view('frontend.payment-history', [
                'user' => $user,
                'transactions' => $transactions
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'User not found');
        } catch (\Exception $e) {
            Log::error('Payment history error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to retrieve payment history');
        }
    }

    public function viewCart(Request $request): View
    {
        $userId = Auth::id();
        $cart = Session::get("lotteryCart.$userId", []);
        // dd($cart);
        // Remove expired items based on slot_time and close_time
        $closeMinutes = \App\Models\CloseTime::pluck('minutes')->first();
        $now = now();
        $filteredCart = [];
        foreach ($cart as $item) {
            if (!isset($item['game_id'])) continue;
            $slot = \App\Models\ScheduleProviderSlotTime::find($item['game_id']);
            if (!$slot) continue;
            $slotTime = $slot->slot_time;
            if ($slotTime) {
                $slotDateTime = \Carbon\Carbon::parse($slotTime);
                $closeDateTime = $slotDateTime->subMinutes($closeMinutes);
                if ($now->lessThan($closeDateTime)) {
                    $filteredCart[] = $item;
                }
            }
        }

        Session::put("lotteryCart.$userId", $filteredCart);
        return view('frontend.cart', ['cart' => $filteredCart]);
    }

    public function getCart()
    {
        $userId = Auth::id();
        $cart = Session::get("lotteryCart.$userId", []);
        return response()->json(['cart' => $cart]);
    }

    public function addToCart(Request $request)
    {
        $userId = Auth::id();
        $item = $request->item;

        $cart = Session::get("lotteryCart.$userId", []);
        $cart[] = $item;

        Session::put("lotteryCart.$userId", $cart);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    public function removeFromCart($index)
    {
        $userId = Auth::id();
        $cart = Session::get("lotteryCart.$userId", []);

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart); // reindex array
            Session::put("lotteryCart.$userId", $cart);

            return redirect()->back()->with('success', 'Item removed from cart.');
        }

        return redirect()->back()->with('error', 'Item not found in cart.');
    }

    public function rules(): View
    {
        return view('frontend.rules');
    }

    public function results()
    {
        return view('frontend.results');
    }

    // public function getTableData(Request $request)
    // {
    //     if ($request->ajax()) {
    //         // Get data from schedule_provider joined with betting_providers and provider_slots
    //         $results = DB::table('schedule_provider')
    //             ->leftJoin('betting_providers', 'schedule_provider.betting_providers_id', '=', 'betting_providers.id')
    //             ->leftJoin('provider_times', 'provider_times.id', '=', 'schedule_provider.slot_time_id')
    //             ->select(
    //                 'schedule_provider.*',
    //                 'betting_providers.name as provider_name',
    //                 'provider_times.time as slot_time'
    //             )
    //             ->whereDate('schedule_provider.created_at', '=', now()->toDateString())
    //             ->get();

    //         return DataTables::of($results)->make(true);
    //     }
    // }
    // public function getTableData(Request $request)
    // {
    //     if ($request->ajax()) {

    //         $results = DB::table('schedule_provider')
    //             ->leftJoin('betting_providers', 'schedule_provider.betting_providers_id', '=', 'betting_providers.id')
    //             ->leftJoin('provider_times', 'provider_times.id', '=', 'schedule_provider.slot_time_id')
    //             ->select(
    //                 'schedule_provider.id',
    //                 'betting_providers.name as provider_name',
    //                 'provider_times.time as slot_time',
    //                 'schedule_provider.result',
    //                 'schedule_provider.created_at'
    //             )
    //             ->whereDate('schedule_provider.created_at', now()->toDateString())
    //             ->orderBy('schedule_provider.created_at', 'desc'); 

    //         return DataTables::of($results)->make(true);
    //     }
    // }
    public function getTableData(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $results = DB::table('schedule_provider')
            ->leftJoin('betting_providers', 'schedule_provider.betting_providers_id', '=', 'betting_providers.id')
            ->leftJoin('provider_times', 'provider_times.id', '=', 'schedule_provider.slot_time_id')
            ->select(
                'schedule_provider.id',
                'betting_providers.name as provider_name',
                'provider_times.time as slot_time',
                'schedule_provider.result',
                'schedule_provider.created_at'
            )
            ->whereDate('schedule_provider.created_at', now()->toDateString());

        return DataTables::of($results)
            ->addIndexColumn()
            ->rawColumns([])
            ->make(true);
    }


    public function customerOrderDetails()
    {
        //get customer orders and pass to view
        $data['results'] = DB::table('customer_orders')
            ->leftJoin('customer_order_items', 'customer_orders.id', '=', 'customer_order_items.order_id')
            ->leftJoin('schedule_providers_slot_time', 'schedule_providers_slot_time.id', '=', 'customer_order_items.game_id')
            ->leftJoin('digit_master', 'digit_master.id', '=', 'schedule_providers_slot_time.digit_master_id')
            ->leftJoin('betting_providers', 'betting_providers.id', '=', 'schedule_providers_slot_time.betting_providers_id')
            ->select(
                // ORDER
                'customer_orders.id as order_id',
                'customer_orders.total_amount',
                'customer_orders.opening_balance',
                'customer_orders.closing_balance',
                'customer_orders.bonus_opening_balance',
                'customer_orders.bonus_closing_balance',
                'customer_orders.created_at as order_created_at',

                // ORDER ITEMS
                'customer_order_items.id as order_item_id',
                'customer_order_items.game_id',
                'customer_order_items.digits',
                'customer_order_items.quantity',
                'customer_order_items.amount as particular_slot_amount',
                'customer_order_items.win_amount',              // ✅ ADD THIS
                'customer_order_items.win_status',               // (optional)
                'customer_order_items.created_at as order_item_created_at',

                // SLOT
                'schedule_providers_slot_time.slot_time',

                // PROVIDER & GAME
                'betting_providers.name as provider_name',
                'digit_master.name as game_digits'
            )
            ->where('customer_orders.user_id', Auth::id())
            ->orderBy('customer_orders.created_at', 'desc')
            ->get();
            
        return view('frontend.customer-order-details', $data);
    }
}
