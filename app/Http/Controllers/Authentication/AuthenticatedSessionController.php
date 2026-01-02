<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\CustomerLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\CreateGameScheduleModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
       
        return view('auth.login');
    }

    public function createCustomer(): View
    {
        Session::forget('lotteryCart');
        $link = \App\Models\WhatsAppLink::first()->link ?? null;
        return view('frontend.login', compact('link'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = auth()->user();

    if ($user->user_type === 'subadmin') {
        return redirect()->route('admin.wallet.index'); // only wallet
    }

    return redirect()->route('admin.dashboard'); // full admin
}


    public function storeCustomer(CustomerLoginRequest $request): RedirectResponse
    {
        $request->authenticateUser();
        $request->session()->regenerate();
        $user = auth()->user();

        if ($user['user_type'] != 'normal' || !$user['status'] || $user->deleted_at !== null) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }
        return redirect()->intended(route('customer.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function landingDashboard(Request $request)
    {
        $gameModel = new CreateGameScheduleModel();
        $data['schedules'] = $gameModel->getGameSchedule();
        $data['sliders'] = \App\Models\Admin\SliderModel::where('status', true)->orderBy('order')->get();
        $data['default_provider'] = $data['schedules']->firstWhere('is_default', 1);
       
        $currentTime = Carbon::now();
        //dd($data);
        return view('frontend.landing', $data);
    }
}
