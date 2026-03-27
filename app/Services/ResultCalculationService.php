<?php

namespace App\Services;

use App\Models\CustomerOrderItemModel;
use App\Models\ResultCalculationLog;
use App\Models\CustomerOrder;
use App\Models\Admin\WalletModel;
use App\Models\Admin\WalletTransactionLogModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultCalculationService
{
    /**
     * Calculate and update winnings for a given provider and result.
     *
     * @param int $providerId
     * @param string $result
     * @return void
     */
    public function calculateWinnings($providerId, $providerDetails, $drawResult)
    {
        $drawResult = str_pad((string)$drawResult, 4, '0', STR_PAD_LEFT);
    
        $orderItems = CustomerOrderItemModel::whereNull('win_amount')
            ->with(['scheduleProviderSlotTime', 'customerOrders'])
            ->whereHas('scheduleProviderSlotTime', function ($q) use ($providerId, $providerDetails) {
                $q->where('schedule_provider_id', $providerId)
                  ->where('betting_providers_id', $providerDetails->betting_providers_id)
                  ->whereDate('created_at', now()->toDateString());
            })
            ->get();
    
        if ($orderItems->isEmpty()) {
            Log::info('No pending order items', [
                'provider_id' => $providerId,
                'result' => $drawResult,
            ]);
            return;
        }
    
        $bettingProviderId = $providerDetails->betting_providers_id;
    
        foreach ($orderItems as $item) {
    
            if (!$item->scheduleProviderSlotTime) {
                Log::warning('Missing scheduleProviderSlotTime', [
                    'order_item_id' => $item->id,
                ]);
                continue;
            }
    
            $digitMasterId = $item->scheduleProviderSlotTime->digit_master_id;
            $digits        = (string)$item->digits;
            $userAmount    = $item->scheduleProviderSlotTime->amount;
            $quantity      = $item->quantity;
    
            $winAmount = 0;
            $winStatus = 'lost';
    
            switch ($digitMasterId) {
    
                case 1: // A
                    if (substr($drawResult, -3, 1) === $digits) {
                        $winStatus = 'A Slot Win';
                    }
                    break;
    
                case 2: // B
                    if (substr($drawResult, -2, 1) === $digits) {
                        $winStatus = 'B Slot Win';
                    }
                    break;
    
                case 3: // C
                    if (substr($drawResult, -1, 1) === $digits) {
                        $winStatus = 'C Slot Win';
                    }
                    break;
    
                case 4: // AB
                    if (substr($drawResult, -3, 2) === $digits) {
                        $winStatus = 'AB Slot Win';
                    }
                    break;
    
                case 5: // BC
                    if (substr($drawResult, -2, 2) === $digits) {
                        $winStatus = 'BC Slot Win';
                    }
                    break;
    
                case 6: // AC
                    if ((substr($drawResult, -3, 1) . substr($drawResult, -1, 1)) === $digits) {
                        $winStatus = 'AC Slot Win';
                    }
                    break;
    
                case 7: // ABC
                    if (substr($drawResult, -3, 3) === $digits) {
                        $winStatus = 'ABC Slot Win';
                    } elseif (substr($drawResult, -2, 2) === substr($digits, -2)) {
                        $digitMasterId = 9; // ABC (BC)
                        $winStatus     = 'ABC (BC) Slot Win';
                    } elseif (substr($drawResult, -1, 1) === substr($digits, -1)) {
                        $digitMasterId = 10; // ABC (C)
                        $winStatus     = 'ABC (C) Slot Win';
                    }
                    break;
    
                case 8: // XABC
                    if (substr($drawResult, -4, 4) === $digits) {
                        $winStatus = 'XABC Slot Win';
                    } elseif (substr($drawResult, -3, 3) === substr($digits, -3)) {
                        $digitMasterId = 11;
                        $winStatus     = 'XABC (ABC) Slot Win';
                    }
                    break;
            }
    
            if ($winStatus !== 'lost') {
    
                $winSlot = DB::table('provider_slots')
                    ->where('betting_provider_id', $bettingProviderId)
                    ->where('slot_id', $digitMasterId)
                    ->where('amount', $userAmount)
                    ->whereNull('deleted_at')
                    ->first();
    
                if ($winSlot) {
                    $winAmount = $winSlot->winning_amount * $quantity;
    
                    Log::info('WIN MATCH SUCCESS', [
                        'order_item_id' => $item->id,
                        'digits'        => $digits,
                        'slot_id'       => $digitMasterId,
                        'user_amount'   => $userAmount,
                        'winning_amount'=> $winSlot->winning_amount,
                        'final_amount'  => $winAmount
                    ]);
                } else {
                    Log::warning('Slot not found for match', [
                        'order_item_id' => $item->id,
                        'slot_id'       => $digitMasterId,
                        'amount'        => $userAmount
                    ]);
                }
            }
    
            $item->update([
                'win_amount' => $winAmount,
                'win_status' => $winStatus,
            ]);
    
            $order = $item->customerOrders;
    
            if ($order && $winAmount > 0) {
    
                $wallet = WalletModel::firstOrCreate([
                    'user_id' => $order->user_id
                ]);
    
                $wallet->increment('balance', $winAmount);
    
                WalletTransactionLogModel::create([
                    'user_id'        => $order->user_id,
                    'user_wallet_id' => $wallet->id,
                    'type'           => 'credit',
                    'amount'         => $winAmount,
                    'description'    => 'Winning for order item #' . $item->id,
                ]);
            }
    
            ResultCalculationLog::create([
                'provider_id'   => $providerId,
                'order_item_id' => $item->id,
                'digits'        => $digits,
                'result'        => $drawResult,
                'win_amount'    => $winAmount,
                'win_status'    => $winStatus,
                'user_id'       => $order->user_id ?? null,
            ]);
        }
    }
}