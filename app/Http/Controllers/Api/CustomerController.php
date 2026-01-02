<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CloseTime;
use App\Models\CustomerOrdersModel;
use App\Models\CustomerOrderItemModel;
use App\Models\ScheduleProviderSlotTime;
use App\Services\WalletValidationService;

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

    public function paymentHistory()
    {
        return response()->json([
            'success' => true,
            'data' => DB::table('wallet_transaction_logs')
                ->where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->paginate(10)
        ]);
    }

    public function orderDetails()
    {
        return response()->json([
            'success' => true,
            'data' => DB::table('customer_orders')
                ->where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->get()
        ]);
    }

    public function rules()
    {
        return response()->json([
            'success' => true,
            'rules' => 'Rules content here'
        ]);
    }
}
