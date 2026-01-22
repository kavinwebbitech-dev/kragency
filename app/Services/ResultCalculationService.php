<?php

namespace App\Services;

use App\Models\CustomerOrderItemModel;
use App\Models\ResultCalculationLog;
use App\Models\CustomerOrder;
use App\Models\Admin\WalletModel;
use App\Models\Admin\WalletTransactionLogModel;
use Illuminate\Support\Facades\DB;

class ResultCalculationService
{
    /**
     * Calculate and update winnings for a given provider and result.
     *
     * @param int $providerId
     * @param string $result
     * @return void
     */
    public function calculateWinnings($providerId, $providerDetails, $result)
    {
        $orderItems = CustomerOrderItemModel::whereNull('win_amount')
            ->with(['scheduleProviderSlotTime' => function ($q) use ($providerId, $providerDetails) {
                $q->where('schedule_provider_id', $providerId)
                ->where('betting_providers_id', $providerDetails->betting_providers_id)
                ->whereDate('created_at', now()->toDateString())
                ->select('id','betting_providers_id','digit_master_id','slot_id');
            }])
            ->whereHas('scheduleProviderSlotTime', function ($q) use ($providerId, $providerDetails) {
                $q->where('schedule_provider_id', $providerId)
                ->where('betting_providers_id', $providerDetails->betting_providers_id)
                ->whereDate('created_at', now()->toDateString());
            })
            ->get();

        foreach ($orderItems as $item) {
            // Cache provider slots
            $providerSlots = DB::table('provider_slots')
                ->where('betting_provider_id', $item->scheduleProviderSlotTime->betting_providers_id)
                ->get()
                ->keyBy('slot_id');
            $digitMasterId = $item->scheduleProviderSlotTime->digit_master_id;
            $digits = (string) $item->digits;
            $result = (string) $result;

            $winAmount = 0;
            $winStatus = 'lost';

            // ---------------- COMBINATION RULES ----------------
            switch ($digitMasterId) {

                // A
                case 1:
                    if (substr($result, -3, 1) === $digits) {
                        $winStatus = 'A Slot Win';
                    }
                    break;

                // B
                case 2:
                    if (substr($result, -2, 1) === $digits) {
                        $winStatus = 'B Slot Win';
                    }
                    break;

                // C
                case 3:
                    if (substr($result, -1) === $digits) {
                        $winStatus = 'C Slot Win';
                    }
                    break;

                // AB
                case 4:
                    if (substr($result, -3, 2) === $digits) {
                        $winStatus = 'AB Slot Win';
                    }
                    break;

                // AC
                case 6:
                    if ((substr($result, -3, 1) . substr($result, -1)) === $digits) {
                        $winStatus = 'AC Slot Win';
                    }
                    break;

                // BC
                case 5:
                    if (substr($result, -2) === $digits) {
                        $winStatus = 'BC Slot Win';
                    }
                    break;

                // ABC
                case 7:
                    if (substr($result, -3) === $digits) {
                        $winStatus = 'ABC Slot Win';
                    } elseif (substr($result, -2) === substr($digits, -2)) {
                        $digitMasterId = 9; // ABC (BC)
                        $winStatus = 'ABC (BC) Slot Win';
                    } elseif (substr($result, -1) === substr($digits, -1)) {
                        $digitMasterId = 10; // ABC (C)
                        $winStatus = 'ABC (C) Slot Win';
                    }
                    break;

                // XABC
                case 8:
                    if (substr($result, -4) === $digits) {
                        $winStatus = 'XABC Slot Win';
                    } elseif (substr($result, -3) === substr($digits, -3)) {
                        $digitMasterId = 11; // XABC (ABC)
                        $winStatus = 'XABC (ABC) Slot Win';
                    } elseif (substr($result, -2) === substr($digits, -2)) {
                        $digitMasterId = 9;
                        $winStatus = 'ABC (BC) Slot Win';
                    } elseif (substr($result, -1) === substr($digits, -1)) {
                        $digitMasterId = 10;
                        $winStatus = 'ABC (C) Slot Win';
                    }
                    break;
            }

            // ---------------- PAYOUT ----------------
            if ($winStatus !== 'lost' && isset($providerSlots[$digitMasterId])) {
                $winAmount = $providerSlots[$digitMasterId]->winning_amount * $item->quantity;
            }

            $item->update([
                'win_amount' => $winAmount,
                'win_status' => $winStatus
            ]);

            // ---------------- WALLET ----------------
            $order = CustomerOrder::find($item->order_id);
            if ($order && $winAmount > 0) {

                $wallet = WalletModel::firstOrCreate(['user_id' => $order->user_id]);

                $wallet->increment('balance', $winAmount);

                WalletTransactionLogModel::create([
                    'user_id' => $order->user_id,
                    'user_wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'amount' => $winAmount,
                    'description' => 'Winning for order item #' . $item->id,
                ]);
            }

            // ---------------- LOG ----------------
            ResultCalculationLog::create([
                'provider_id' => $providerId,
                'order_item_id' => $item->id,
                'digits' => $digits,
                'result' => $result,
                'win_amount' => $winAmount,
                'win_status' => $winStatus,
                'user_id' => $order->user_id ?? null,
            ]);
        }
    }

}
