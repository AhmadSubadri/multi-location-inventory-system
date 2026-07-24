<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLogin(): Response
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $remember = $credentials['remember'] ?? false;
        unset($credentials['remember']);

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif. Silakan hubungi Administrator.',
            ]);
        }

        $request->session()->regenerate();

        // Set default active location to user's first location
        $user->load('locations');
        if ($user->locations->isNotEmpty()) {
            session(['active_location_id' => $user->locations->first()->id]);
        }

        ActivityLog::log(
            action: 'LOGIN',
            description: "User {$user->name} ({$user->email}) berhasil login"
        );

        return redirect()->intended(route('dashboard'))->with('success', "Selamat datang kembali, {$user->name}!");
    }

    /**
     * Switch user's active location context.
     */
    public function switchLocation(Request $request)
    {
        $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        $user = Auth::user();
        $locationId = (int) $request->location_id;

        // Verify user has access to this location (unless Super Admin/Owner)
        if (!$user->canAccessLocation($locationId)) {
            return back()->with('error', 'Anda tidak memiliki akses ke lokasi tersebut.');
        }

        session(['active_location_id' => $locationId]);

        return back()->with('info', 'Lokasi aktif berhasil diubah.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::log(
                action: 'LOGOUT',
                description: "User {$user->name} logout"
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah keluar dari sistem.');
    }
}
