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

        $walletTotal = 0; // non-default
        $bonusTotal  = 0; // default

        // 1️⃣ Cart items
        foreach ($cart as $item) {

            $game = \App\Models\ScheduleProviderSlotTime::with('getProvider')
                ->find($item['game_id']);

            if (!$game || !$game->getProvider) continue;

            $qty = (int) ($item['quantity'] ?? 1);
            $amt = (float) ($item['amount'] ?? 0);
            $total = $qty * $amt;

            if ($game->getProvider->is_default == 1) {
                $bonusTotal += $total;
            } else {
                $walletTotal += $total;
            }
        }

        // 2️⃣ Current item
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
            $total = $qty * $amt;

            if ($game->getProvider->is_default == 1) {
                $bonusTotal += $total;
            } else {
                $walletTotal += $total;
            }
        }

        // 3️⃣ Fetch balances
        $bonusBalance  = WalletValidationService::getBonusBalance($userId);
        $walletBalance = WalletValidationService::getWalletBalance($userId);

        // 4️⃣ Use bonus first
        $bonusUsed = min($bonusBalance, $bonusTotal);
        $remainingBonusAmount = $bonusTotal - $bonusUsed;

        // 5️⃣ Wallet pays:
        // - non-default items
        // - remaining default items (if bonus insufficient)
        $walletRequired = $walletTotal + $remainingBonusAmount;

        if ($walletBalance < $walletRequired) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sufficient balance'
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
