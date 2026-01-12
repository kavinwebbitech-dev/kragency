<?php

namespace App\Services;

use App\Models\User;

class WalletValidationService
{
    /**
     * Check if the user's wallet balance is sufficient for the given total amount.
     *
     * @param int $userId
     * @param float $totalAmount
     * @return bool
     */
    public static function getWalletBalance($userId)
    {
        $user = User::find($userId);
        return ($user && $user->wallet) ? (float)$user->wallet->balance : 0;
    }

    public static function getBonusBalance($userId)
    {
        $user = User::find($userId);
        return ($user && $user->wallet) ? (float)$user->wallet->bonus_amount : 0;
    }

    public static function hasSufficientBalance($userId, $amount)
    {
        return self::getWalletBalance($userId) >= $amount;
    }

    public static function hasSufficientBonusBalance($userId, $amount)
    {
        return self::getBonusBalance($userId) >= $amount;
    }
}
