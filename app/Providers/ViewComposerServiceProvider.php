<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share user details and WhatsApp number with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user_detail = User::with('wallet')->find(Auth::id());
                $view->with('user_detail', $user_detail);
            } else {
                $view->with('user_detail', null);
            }
            // Share WhatsApp number from CloseTime
            $whatsapp_number = optional(\App\Models\CloseTime::first())->whatsapp_number;
            $view->with('whatsapp_number', $whatsapp_number);
        });
        // You can add more composers for other data
        View::composer(['partials.header', 'partials.footer'], function ($view) {
            $view->with('currentYear', date('Y'));
        });
    }
}
