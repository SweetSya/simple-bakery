<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticateController extends Controller
{
    /**
     * Display the login view.
     */
    public function view(): Response|RedirectResponse
    {
        if (Auth::check()) {
            // If the user is already authenticated, redirect to the dashboard
            return redirect()->route('dashboard')
                ->withCookie(Cookie::make('notyf_flash_error', 'Anda sudah autentikasi, silakan logout terlebih dahulu jika ingin masuk sebagai user lain.'));
        }
        return Inertia::render('Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function attempt(Request $request): RedirectResponse
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            // If authentication fails, redirect back with an error
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => __('auth.failed'),
                ]);
        }

        // If authentication is successful, regenerate the session
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withCookie(Cookie::make('notyf_flash_success', 'Berhasil logout.'));
    }
}
