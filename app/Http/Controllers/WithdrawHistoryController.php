<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\WithdrawRequest;

class WithdrawHistoryController extends Controller
{
    public function index()
    {
        $withdraws = WithdrawRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return view('frontend.withdraw.history', compact('withdraws'));
    }
}
