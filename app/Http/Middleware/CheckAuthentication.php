<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Check if the user role is in the allowed roles
            if (in_array(Auth::user()->role->name, $roles)) {
                // If authenticated and authorized, allow the request to proceed
                return $next($request);
            }
            // If authenticated but not authorized, redirect to the dashboard
            return redirect()->route('dashboard')->withCookie(Cookie::make('notyf_flash_error', 'Anda tidak memiliki akses ke halaman ini.', 1)); // 1 minute expiry
        }
        return redirect()->route('login')->withCookie(Cookie::make('notyf_flash_error', 'Harap lakukan autentikasi terlebih dahulu.', 1));
    }
}
