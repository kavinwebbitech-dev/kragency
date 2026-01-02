<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->user_type !== 'normal' || !$user->status || $user->deleted_at !== null) {
            // Optionally, redirect or abort
            abort(403, 'Unauthorized Access.');
        }

        return $next($request);
    }
}
