<?php

namespace App\Services;

use App\Models\CustomerOrderItemModel;
use Illuminate\Support\Facades\DB;

class SlotDigitStatsService
{
    /**
     * Get digit counts and percentages for each slot for a given schedule_provider_id.
     *
     * @param int $scheduleProviderId
     * @return array
     */
    public function getSlotDigitStats($scheduleProviderId)
    {
        // Slot mapping: digit_master_id => slot label
        $slotMap = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'AB',
            5 => 'BC',
            6 => 'AC',
            7 => 'ABC',
            8 => 'XABC',
        ];

        $result = [];
        // Collect all digits from all slots
        $allSlotDigits = [];
        foreach ($slotMap as $digitMasterId => $slotLabel) {
            $items = CustomerOrderItemModel::whereHas('scheduleProviderSlotTime', function($q) use ($scheduleProviderId, $digitMasterId) {
                $q->where('schedule_provider_id', $scheduleProviderId)
                  ->where('digit_master_id', $digitMasterId);
            })->get();
            foreach ($items as $item) {
                $allSlotDigits[$slotLabel][] = $item->digits;
            }
        }
        // Flatten all digits
        $allDigitsFlat = array_unique(array_merge(...array_values($allSlotDigits)));

        // Find 4-digit numbers not in any slot
            // Calculate total amount placed by all customers for this service provider
            $totalAmount = CustomerOrderItemModel::whereHas('scheduleProviderSlotTime', function($q) use ($scheduleProviderId) {
                $q->where('schedule_provider_id', $scheduleProviderId);
            })->sum('amount');
        $notInAnySlot = [];
        for ($i = 0; $i < 10000 && count($notInAnySlot) < 10; $i++) {
            $num = str_pad($i, 4, '0', STR_PAD_LEFT);
            if (!in_array($num, $allDigitsFlat)) {
                $notInAnySlot[] = $num;
            }
        }

        // Now build per-slot stats as before
        foreach ($slotMap as $digitMasterId => $slotLabel) {
            // Get all digits for this slot
            $items = CustomerOrderItemModel::whereHas('scheduleProviderSlotTime', function($q) use ($scheduleProviderId, $digitMasterId) {
                $q->where('schedule_provider_id', $scheduleProviderId)
                  ->where('digit_master_id', $digitMasterId);
            })->get();

            $digitCounts = [];
            $total = 0;
            foreach ($items as $item) {
                $digit = $item->digits;
                $qty = (int)($item->quantity ?? 1);
                if (!isset($digitCounts[$digit])) {
                    $digitCounts[$digit] = 0;
                }
                $digitCounts[$digit] += $qty;
                $total += $qty;
            }

            // Calculate percentages
            $digitStats = [];
            foreach ($digitCounts as $digit => $count) {
                $percent = $total > 0 ? round(($count / $total) * 100, 2) : 0;
                $digitStats[] = [
                    'digit' => $digit,
                    'count' => $count,
                    'percent' => $percent,
                ];
            }

            // Sort by count desc
            usort($digitStats, function($a, $b) {
                return $b['count'] <=> $a['count'];
            });

            // Generate all possible numbers for this slot
            $possibleNumbers = [];
            $slotLengths = [1=>1,2=>1,3=>1,4=>2,5=>2,6=>2,7=>3];
            $len = $slotLengths[$digitMasterId] ?? 1;
            $max = pow(10, $len);
            for ($i = 0; $i < $max; $i++) {
                $num = str_pad($i, $len, '0', STR_PAD_LEFT);
                $possibleNumbers[] = $num;
            }

            // Find unmatched numbers
            $matchedDigits = array_keys($digitCounts);
            $unmatched = array_diff($possibleNumbers, $matchedDigits);

                // Group by percentage match (bands: 10-19, 20-29, ..., 90-99, 100)
                $percentBands = [
                    ['min' => 10, 'max' => 19],
                    ['min' => 20, 'max' => 29],
                    ['min' => 30, 'max' => 39],
                    ['min' => 40, 'max' => 49],
                    ['min' => 50, 'max' => 59],
                    ['min' => 60, 'max' => 69],
                    ['min' => 70, 'max' => 79],
                    ['min' => 80, 'max' => 89],
                    ['min' => 90, 'max' => 99],
                    ['min' => 100, 'max' => 100],
                ];
                $percentGroups = [];
                foreach ($percentBands as $band) {
                    $percentGroups[$band['min'].'-'.$band['max']] = [];
                }
                foreach ($digitStats as $row) {
                    foreach ($percentBands as $band) {
                        if ($row['percent'] >= $band['min'] && $row['percent'] <= $band['max']) {
                            $percentGroups[$band['min'].'-'.$band['max']][] = $row['digit'];
                            break;
                        }
                    }
                }

            // For all slots: provide top 10 unmatched, 10%, 30%, 50%, 70% matched numbers
            $extra = [];
           

            $result[$slotLabel] = [
                'total' => $total,
                'digits' => $digitStats,
                'unmatched' => array_values($unmatched),
                'percentGroups' => $percentGroups,
            ] + $extra;
        }
    // Attach the not_in_any_slot array to the result (top-level)
    $result['not_in_any_slot'] = $notInAnySlot;
    $result['total_amount'] = $totalAmount;
    return $result;
        
    }
}
