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
        $game = \App\Models\ScheduleProviderSlotTime::find($request->data['game_id']);
        $totalAmount = 0;
        $totalAmount += $request->input('total');
       /* foreach ($cart as $item) {
            $qty = isset($item['quantity']) ? $item['quantity'] : 1;
            $amt = isset($item['amount']) ? $item['amount'] : 0;
            $totalAmount += $qty * $amt;
        }*/
        // Add the new item amount if provided
        //$addAmount = $request->input('add_amount', 0);
       // $addQty = $request->input('add_quantity', 1);
        //$totalAmount += $request->input('total');
        $hasBalance = WalletValidationService::hasSufficientBalance($userId, $totalAmount);
        if($hasBalance == false){
            if($game->getProvider->is_default == 0){
                $hasBalance = WalletValidationService::hasSufficientBonusBalance($userId, $totalAmount);
            }
        }
        return response()->json([
            'success' => $hasBalance,
            'message' => $hasBalance ? 'Sufficient balance' : 'Insufficient balance',
        ]);
    }
}
