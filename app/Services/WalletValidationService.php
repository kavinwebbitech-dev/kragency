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
    public static function hasSufficientBalance($userId, $totalAmount)
    {
        $user = User::find($userId);
        if (!$user || !$user->wallet) {
            return false;
        }
        return $user->wallet->balance >= $totalAmount;
    }
}
