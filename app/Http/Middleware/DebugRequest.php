<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    { // Debug CSRF token matching
        Log::info('=== CSRF DEBUG ===');
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->url());
        Log::info('Request All Data: ', $request->all());
        Log::info('Request Headers: ', $request->headers->all());
        Log::info('Session Token: ' . $request->session()->token());
        Log::info('X-CSRF-TOKEN Header: ' . $request->header('X-CSRF-TOKEN'));
        Log::info('X-XSRF-TOKEN Header: ' . $request->header('X-XSRF-TOKEN'));
        Log::info('_token Input: ' . $request->input('_token'));
        Log::info('Session ID: ' . $request->session()->getId());

        // Check if tokens would match
        $sessionToken = $request->session()->token();
        $requestToken = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        Log::info('Session Token: ' . $sessionToken);
        Log::info('Request Token: ' . $requestToken);
        Log::info('Tokens Match: ' . ($sessionToken === $requestToken ? 'YES' : 'NO'));

        Log::info('=== END CSRF DEBUG ===');
        return $next($request);
    }
}
