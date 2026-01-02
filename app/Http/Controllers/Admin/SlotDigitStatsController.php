<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SlotDigitStatsService;

class SlotDigitStatsController extends Controller
{
    public function index(Request $request, $schedule_provider_id = null)
    {
        $stats = null;
        if ($schedule_provider_id) {
            $stats = (new SlotDigitStatsService())->getSlotDigitStats($schedule_provider_id);
        }
        return view('admin.stats.slot_digit_stats', [
            'stats' => $stats,
            'scheduleProviderId' => $schedule_provider_id
        ]);
    }
}
