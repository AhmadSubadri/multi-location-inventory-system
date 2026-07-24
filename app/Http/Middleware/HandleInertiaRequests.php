<?php

namespace App\Http\Middleware;

use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Get user permissions & roles if authenticated
        $authData = null;
        if ($user) {
            $isSuperOrOwner = $user->isSuperAdmin() || $user->isOwner();

            // Super Admin and Owner have access to ALL active locations in the system
            $locations = $isSuperOrOwner
                ? Location::where('is_active', true)->get()
                : $user->locations()->where('is_active', true)->get();

            $authData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_active' => $user->is_active,
                    'is_super_admin' => $user->isSuperAdmin(),
                    'is_owner' => $user->isOwner(),
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'locations' => $locations->map(fn($loc) => [
                        'id' => $loc->id,
                        'code' => $loc->code,
                        'name' => $loc->name,
                        'type' => $loc->type,
                        'is_main_source' => $loc->is_main_source,
                    ]),
                ],
                // Active location stored in session, fallback to user's first location
                'active_location_id' => session('active_location_id', $locations->first()?->id),
            ];
        }

        return [
            ...parent::share($request),
            'app_version' => 'v1.2.0 Enterprise',
            'auth' => $authData,
            'company' => fn() => CompanyProfile::first(),
            'settings' => fn() => [
                'enable_store_to_store_transfer' => Setting::getValue('enable_store_to_store_transfer', '0') === '1',
                'doc_number_reset_mode' => Setting::getValue('doc_number_reset_mode', 'monthly'),
                'expiry_warning_days' => (int) Setting::getValue('expiry_warning_days', '30'),
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],
        ];
    }
}
