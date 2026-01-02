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
//
        $orderItems = CustomerOrderItemModel::whereNull('win_amount')->with(['scheduleProviderSlotTime' => function($q) use ($providerId, $providerDetails) {
            $q->where('schedule_provider_id', $providerId);
            $q->where('betting_providers_id', $providerDetails->betting_providers_id);
            $q->whereDate('created_at', now()->toDateString());
            // select only needed columns
            $q->select('id', 'digit_master_id');
        }])->whereHas('scheduleProviderSlotTime', function($q) use ($providerId, $providerDetails) {
            $q->where('schedule_provider_id', $providerId);
            $q->where('betting_providers_id', $providerDetails->betting_providers_id);
            $q->whereDate('created_at', now()->toDateString());
        })->get();

        foreach ($orderItems as $item) {
            $provider_slots = DB::table('provider_slots')->where([
                'betting_provider_id'=>$providerDetails->betting_providers_id,
                'slot_id' =>$item->scheduleProviderSlotTime->digit_master_id
                ])->first();

            $winAmount = 0;
            $winStatus = 'lost';
            $digits = $item->digits;
            $len = strlen($result);

            $rules = [
                1 => ['len' => 3, 'substr' => [-3, 1], 'label' => 'A Slot Win'],
                2 => ['len' => 2, 'substr' => [-2, 1], 'label' => 'B Slot Win'],
                3 => ['len' => 1, 'substr' => [-1, null], 'label' => 'C Slot Win'],
                4 => ['len' => 3, 'substr' => [-3, 2], 'label' => 'AB Slot Win'],
                5 => ['len' => 3, 'substr' => [-2, null], 'label' => 'BC Slot Win'],
                6 => ['len' => 3, 'firstLast' => true, 'label' => 'AC Slot Win'],
                7 => ['len' => 4, 'substr' => [-3, null], 'label' => 'ABC Slot Win'],
                8 => ['len' => 4, 'substr' => [-4, null], 'label' => 'XABC Slot Win'],
                9 => ['len' => 3, 'substr' => [-2, null], 'label' => 'ABC (BC) Slot Win'],
                10 => ['len' => 1, 'substr' => [-1, null], 'label' => 'ABC (C) Slot Win'],
                11 => ['len' => 3, 'substr' => [-3, null], 'label' => 'XABC (ABC) Slot Win'], // last 3 digits (NEW)
            ];

            $digitMasterId = $item->scheduleProviderSlotTime->digit_master_id;
            if (isset($rules[$digitMasterId])) {
                $rule = $rules[$digitMasterId];
                
                if ($digits >= $rule['len']) {
                    if (!empty($rule['firstLast'])) {
                        $check = substr($result, 0, 1) . substr($result, -1);
                    } else {
                        $start = $rule['substr'][0];
                        $length = $rule['substr'][1] ?? null;
                        $check = substr($result, $start, $length);
                    }

                    if ($digitMasterId == 7) {
                        if ($check === $digits) {
                            $winStatus = $rule['label'];
                            $winAmount = $provider_slots->winning_amount * $item->quantity;
                        } else if (substr($result, 1) === substr($digits, 1)) {
                            $provider_slots2 = DB::table('provider_slots')->where([
                                'betting_provider_id'=>$providerDetails->betting_providers_id,
                                'slot_id' => 9
                                ])->first();
                            $winStatus = $rules[9]['label'];
                            $winAmount = $provider_slots2->winning_amount * $item->quantity;
                        } else if (substr($result, offset: 2) === substr($digits, 2)) {
                            $provider_slots3 = DB::table('provider_slots')->where([
                                'betting_provider_id'=>$providerDetails->betting_providers_id,
                                'slot_id' => 10
                                ])->first();
                            $winStatus = $rules[10]['label'];
                            $winAmount = $provider_slots3->winning_amount * $item->quantity;
                        }
                    }elseif ($digitMasterId == 8) {
                        if ($check === $digits) {
                            $winStatus = $rule['label'];
                            $winAmount = $provider_slots->winning_amount * $item->quantity;
                        } else if (substr($result, 1) === substr($digits, 1)) {
                            $provider_slots2 = DB::table('provider_slots')->where([
                                'betting_provider_id'=>$providerDetails->betting_providers_id,
                                'slot_id' => 11
                                ])->first();
                            $winStatus = $rules[11]['label'];
                            $winAmount = $provider_slots2->winning_amount * $item->quantity;
                        } else if (substr($result, 2) === substr($digits, 2)) {
                            $provider_slots2 = DB::table('provider_slots')->where([
                                'betting_provider_id'=>$providerDetails->betting_providers_id,
                                'slot_id' => 9
                                ])->first();
                            $winStatus = $rules[9]['label'];
                            $winAmount = $provider_slots2->winning_amount * $item->quantity;
                        } else if (substr($result, 3) === substr($digits, 3)) {
                            $provider_slots3 = DB::table('provider_slots')->where([
                                'betting_provider_id'=>$providerDetails->betting_providers_id,
                                'slot_id' => 10
                                ])->first();
                            $winStatus = $rules[10]['label'];
                            $winAmount = $provider_slots3->winning_amount * $item->quantity;
                        }
                    } else {
                        if ($check === $digits) {
                            $winStatus = $rule['label'];
                            $winAmount = $provider_slots->winning_amount * $item->quantity;
                        }
                    }
                }
            }

            $item->win_amount = $winAmount;
            $item->win_status = $winStatus;
            $item->save();

            // Get user_id from order
            $order = CustomerOrder::find($item->order_id);
            $userId = $order ? $order->user_id : null;

            // Log the result calculation
            ResultCalculationLog::create([
                'provider_id' => $providerId,
                'order_item_id' => $item->id,
                'digits' => $digits,
                'result' => $result,
                'win_amount' => $winAmount,
                'win_status' => $winStatus,
                'user_id' => $userId,
            ]);

            
            if ($winAmount > 0 && $userId) {
                $wallet = WalletModel::where('user_id', $userId)->first();
                if ($wallet) {
                    $wallet->balance += $winAmount;
                    $wallet->save();

                    WalletTransactionLogModel::create([
                        'user_id' => $userId,
                        'user_wallet_id' => $wallet->id,
                        'type' => 'credit',
                        'amount' => $winAmount,
                        'description' => 'Winning for order item #' . $item->id,
                    ]);
                }
            }
        }
    }
}
