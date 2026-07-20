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
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
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

    public function register(): View
    {
        return view('frontend.register');
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
        $request->authenticate();   // ✅ Use OTP authentication

        $request->session()->regenerate();

        $user = auth()->user();

        if (
            $user->user_type != 'normal' ||
            !$user->status ||
            $user->deleted_at !== null
        ) {
            Auth::logout();

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
        $data['schedules'] = $gameModel->getGameSchedule()
            ->sortByDesc('is_default')
            ->values();
        $data['sliders'] = \App\Models\Admin\SliderModel::where('status', true)->orderBy('order')->get();
        $data['default_provider'] = $data['schedules']->firstWhere('is_default', 1);
       
        $currentTime = Carbon::now();
        //dd($data);
        return view('frontend.landing', $data);
    }
    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
            'type'   => 'required|in:register,login',
        ]);

        $user = User::where('mobile', $request->mobile)->first();

        $otp = rand(1000, 9999);

        if (!$user) {
            $user = new User();
            $user->name = $request->name ?? '';
            $user->mobile = $request->mobile;
        }

        $user->otp = $otp;
        $user->otp_created_at = now();
        $user->save();

        $message = "Welcome to Gason India! Your OTP for registration is: {$otp}. This OTP is valid for 5 minutes. Please do not share this code with anyone. Team Gason.";

        $this->sendSms($request->mobile, $message);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully.',
        ]);
    }
    private function sendSms($number, $message)
    {
        $url = "http://pay4sms.in/";
        $token = "06e8ed1c1f10e2f05140416260699412";
        $sender = "GASON";
        $credit = "2";

        $message = urlencode($message);

        $smsUrl = $url . "sendsms/?token=" . $token .
            "&sender=" . $sender .
            "&number=" . $number .
            "&credit=" . $credit .
            "&message=" . $message;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $smsUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $result = curl_exec($curl);

        curl_close($curl);
        return $result;
    }

    public function registerSubmit(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'mobile' => 'required|digits_between:8,15',
            'otp'    => 'required|digits:4',
        ]);

        $user = User::where('mobile', $request->mobile)
            ->where('otp', $request->otp)
            ->where('otp_created_at', '>', now()->subMinutes(5))
            ->first();

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid or expired OTP.');
        }

        $user->update([
            'name'           => $request->name,
            'user_type'      => 'normal',
            'status'         => 1,
            'otp'            => null,
            'otp_created_at' => null,
        ]);

        // Login the user
        Auth::login($user);

        return redirect()->intended(route('customer.dashboard', absolute: false));
    }
}
