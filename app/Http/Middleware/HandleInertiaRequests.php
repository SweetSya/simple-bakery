<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'current_route' => $request->route() ? $request->route()->getName() : null,
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'jobs' => $request->user() ? $request->user()->jobs()->orderBy('created_at', 'desc')->limit(5)->get() : [],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => $this->getFlashData($request)
        ];
    }

    public function getFlashData(Request $request): array
    {
        $flashData = [
            'success' => $request->hasCookie('notyf_flash_success') ? $request->cookie('notyf_flash_success') : null,
            'error' => $request->hasCookie('notyf_flash_error') ? $request->cookie('notyf_flash_error') : null,
            'warning' => $request->hasCookie('notyf_flash_warning') ? $request->cookie('notyf_flash_warning') : null,
            'info' => $request->hasCookie('notyf_flash_info') ? $request->cookie('notyf_flash_info') : null,
        ];
        // Delete cookies after retrieving their values
        foreach ($flashData as $key => $value) {
            if ($value) {
                Cookie::queue(Cookie::forget('notyf_flash_' . $key));
            }
        }
        return $flashData;
    }
}
