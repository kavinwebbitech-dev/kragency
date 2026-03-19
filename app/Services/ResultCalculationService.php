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
        $drawResult = (string) $drawResult;

        $orderItems = CustomerOrderItemModel::whereNull('win_amount')
            ->with(['scheduleProviderSlotTime' => function ($q) use ($providerId, $providerDetails) {
                $q->where('schedule_provider_id', $providerId)
                ->where('betting_providers_id', $providerDetails->betting_providers_id)
                ->whereDate('created_at', now()->toDateString())
                ->select('id', 'betting_providers_id', 'digit_master_id', 'slot_id');
            }])
            ->whereHas('scheduleProviderSlotTime', function ($q) use ($providerId, $providerDetails) {
                $q->where('schedule_provider_id', $providerId)
                ->where('betting_providers_id', $providerDetails->betting_providers_id)
                ->whereDate('created_at', now()->toDateString());
            })
            ->get();

        if ($orderItems->isEmpty()) {
            Log::info('calculateWinnings: no pending order items', [
                'provider_id' => $providerId,
                'result'      => $drawResult,
            ]);
            return;
        }

        $bettingProviderId = $providerDetails->betting_providers_id;

        $providerSlotsById     = DB::table('provider_slots')
            ->where('betting_provider_id', $bettingProviderId)
            ->get()
            ->keyBy('id');

        $providerSlotsBySlotId = DB::table('provider_slots')
            ->where('betting_provider_id', $bettingProviderId)
            ->get()
            ->keyBy('slot_id');

        foreach ($orderItems as $item) {

            if (!$item->scheduleProviderSlotTime) {
                Log::warning('calculateWinnings: missing scheduleProviderSlotTime', [
                    'order_item_id' => $item->id,
                ]);
                continue;
            }

            $digitMasterId = $item->scheduleProviderSlotTime->digit_master_id;
            $slotId        = $item->scheduleProviderSlotTime->slot_id;
            $digits        = (string) $item->digits;

            $winAmount  = 0;
            $winStatus  = 'lost';

            switch ($digitMasterId) {

                // A
                case 1:
                    if (substr($drawResult, -3, 1) === $digits) {
                        $winStatus = 'A Slot Win';
                    }
                    break;

                // B
                case 2:
                    if (substr($drawResult, -2, 1) === $digits) {
                        $winStatus = 'B Slot Win';
                    }
                    break;

                // C
                case 3:
                    if (substr($drawResult, -1) === $digits) {
                        $winStatus = 'C Slot Win';
                    }
                    break;

                // AB
                case 4:
                    if (substr($drawResult, -3, 2) === $digits) {
                        $winStatus = 'AB Slot Win';
                    }
                    break;

                // AC
                case 6:
                    if ((substr($drawResult, -3, 1) . substr($drawResult, -1)) === $digits) {
                        $winStatus = 'AC Slot Win';
                    }
                    break;

                // BC
                case 5:
                    if (substr($drawResult, -2) === $digits) {
                        $winStatus = 'BC Slot Win';
                    }
                    break;

                // ABC
                case 7:
                    if (substr($drawResult, -3) === $digits) {
                        $winStatus = 'ABC Slot Win';
                    } elseif (substr($drawResult, -2) === substr($digits, -2)) {
                        $digitMasterId = 9;
                        $winStatus     = 'ABC (BC) Slot Win';
                    } elseif (substr($drawResult, -1) === substr($digits, -1)) {
                        $digitMasterId = 10;
                        $winStatus     = 'ABC (C) Slot Win';
                    }
                    break;

                // XABC
                case 8:
                    if (substr($drawResult, -4) === $digits) {
                        $winStatus = 'XABC Slot Win';
                    } elseif (substr($drawResult, -3) === substr($digits, -3)) {
                        $digitMasterId = 11;
                        $winStatus     = 'XABC (ABC) Slot Win';
                    }
                    break;
            }

            if ($winStatus !== 'lost') {

                if (isset($providerSlotsById[$slotId]) &&
                    $providerSlotsById[$slotId]->slot_id == $digitMasterId) {

                    $winSlot   = $providerSlotsById[$slotId];
                    $winAmount = $winSlot->winning_amount * $item->quantity;

                    Log::info('Payout: matched own slot', [
                        'order_item_id' => $item->id,
                        'slot_id'       => $slotId,
                        'winning_amount'=> $winSlot->winning_amount,
                        'quantity'      => $item->quantity,
                        'win_amount'    => $winAmount,
                    ]);

                } elseif (isset($providerSlotsBySlotId[$digitMasterId])) {

                    $winSlot   = $providerSlotsBySlotId[$digitMasterId];
                    $winAmount = $winSlot->winning_amount * $item->quantity;

                    Log::info('Payout: matched by digitMasterId', [
                        'order_item_id'  => $item->id,
                        'digit_master_id'=> $digitMasterId,
                        'winning_amount' => $winSlot->winning_amount,
                        'quantity'       => $item->quantity,
                        'win_amount'     => $winAmount,
                    ]);

                } else {
                    Log::error('calculateWinnings: no provider_slot found for digitMasterId', [
                        'order_item_id'  => $item->id,
                        'digit_master_id'=> $digitMasterId,
                        'win_status'     => $winStatus,
                        'betting_provider_id' => $bettingProviderId,
                    ]);
                }
            }

            $item->update([
                'win_amount' => $winAmount,
                'win_status' => $winStatus,
            ]);

            $order = CustomerOrder::find($item->order_id);

            if ($order && $winAmount > 0) {
                $wallet = WalletModel::firstOrCreate(['user_id' => $order->user_id]);
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
