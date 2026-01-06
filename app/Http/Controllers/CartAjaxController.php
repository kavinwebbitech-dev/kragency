<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\WalletValidationService;

class CartAjaxController extends Controller
{
    public function checkWallet(Request $request)
    {
        $userId = Auth::id();
        $cart = Session::get("lotteryCart.$userId", []);

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

            // wallet includes everything
            $walletTotal += $itemTotal;

            // bonus ONLY for default games
            if ($game->getProvider->is_default == 1) {
                $bonusTotal += $itemTotal;
            }
        }

        // 2️⃣ Add current request item
        if (!empty($request->data)) {

            $game = \App\Models\ScheduleProviderSlotTime::with('getProvider')
                ->find($request->data['game_id']);

            if (!$game || !$game->getProvider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid game'
                ]);
            }

            $qty = (int) ($request->data['quantity'] ?? 1);
            $amt = (float) ($request->data['amount'] ?? 0);
            $itemTotal = $qty * $amt;

            // always added to wallet requirement
            $walletTotal += $itemTotal;

            // bonus only if default game
            if ($game->getProvider->is_default == 1) {
                $bonusTotal += $itemTotal;
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
        if ($bonusTotal > 0 && $game->getProvider->is_default == 1 &&
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
    
    // public function checkWallet(Request $request)
    // {
    //     $userId = Auth::id();
    //     $cart = Session::get("lotteryCart.$userId", []);
    //     $game = \App\Models\ScheduleProviderSlotTime::find($request->data['game_id']);
    //     $totalAmount = 0;
    //     $totalAmount += $request->input('total');
    //    /* foreach ($cart as $item) {
    //         $qty = isset($item['quantity']) ? $item['quantity'] : 1;
    //         $amt = isset($item['amount']) ? $item['amount'] : 0;
    //         $totalAmount += $qty * $amt;
    //     }*/
    //     // Add the new item amount if provided
    //     //$addAmount = $request->input('add_amount', 0);
    //    // $addQty = $request->input('add_quantity', 1);
    //     //$totalAmount += $request->input('total');
    //     dd($request->all(),$totalAmount);
    //     $hasBalance = WalletValidationService::hasSufficientBalance($userId, $totalAmount);
    //     if($hasBalance == false){
    //         if($game->getProvider->is_default == 1){
    //             $hasBalance = WalletValidationService::hasSufficientBonusBalance($userId, $totalAmount);
    //         }
    //     }
    //     return response()->json([
    //         'success' => $hasBalance,
    //         'message' => $hasBalance ? 'Sufficient balance' : 'Insufficient balance',
    //     ]);
    // }
}
