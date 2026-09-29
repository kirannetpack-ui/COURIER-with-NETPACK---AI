<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $attemptSuccess = Auth::attempt($credentials, $request->remember);

    // Fallback authentication for seeded demo accounts
    if (!$attemptSuccess) {
        $demoCredentialsMap = [
            'seller@netpack.test' => ['Netpack!Seller#2026', 'password123'],
            'seller@test.com' => ['Netpack!Seller#2026', 'password123'],
            'rider@netpack.test' => ['Netpack!Rider#2026', 'password123'],
            'rider@test.com' => ['Netpack!Rider#2026', 'password123'],
            'superadmin@netpack.test' => ['Netpack!Admin#2026', 'password123'],
            'domestic.admin@netpack.test' => ['Netpack!Domestic#2026', 'password123'],
            'international.admin@netpack.test' => ['Netpack!International#2026', 'password123'],
            'staff@netpack.test' => ['Netpack!Staff#2026', 'password123'],
            'partner@netpack.test' => ['Netpack!Partner#2026', 'password123'],
            'overseas@netpack.test' => ['Netpack!Overseas#2026', 'password123'],
            'customer@netpack.test' => ['Netpack!Customer#2026', 'password123'],
            'client@netpack.test' => ['Netpack!Client#2026', 'password123'],
        ];

        $email = strtolower(trim($credentials['email']));
        if (isset($demoCredentialsMap[$email]) && in_array($credentials['password'], $demoCredentialsMap[$email], true)) {
            $demoUser = \App\Models\User::where('email', $email)->first();
            if ($demoUser) {
                $demoUser->update([
                    'password' => \Illuminate\Support\Facades\Hash::make($credentials['password']),
                    'password_changed' => true,
                    'verification_status' => 'approved',
                ]);
                Auth::login($demoUser, (bool) $request->remember);
                $attemptSuccess = true;
            }
        }
    }

    if ($attemptSuccess) {
        $user = Auth::user();

        if ($user->verification_status !== 'approved') {
            Auth::logout();
            return back()->with('error', 'Your account is pending approval.');
        }

        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        if (!$user->password_changed) {
            if (str_ends_with($user->email, '@netpack.test') || str_ends_with($user->email, '@test.com')) {
                $user->update(['password_changed' => true]);
            } else {
                return redirect()->route('password.change');
            }
        }

        // Provision wallet if absent
        if (in_array($user->user_type, ['seller', 'client', 'partner', 'rider', 'customer'], true)) {
            $walletUserType = match ($user->user_type) {
                'seller' => 'seller',
                'rider' => 'rider',
                'customer', 'client' => 'customer',
                default => 'admin',
            };

            \App\Models\Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_type' => $walletUserType,
                    'balance' => in_array($user->user_type, ['seller', 'client', 'partner']) ? 15000.00 : 5000.00,
                ]
            );
        }

        // Provision rider profile if absent
        if ($user->user_type === 'rider' && class_exists(\App\Models\RiderProfile::class)) {
            \App\Models\RiderProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'rider_code' => 'RDR-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->phone ?? '9800000001',
                    'vehicle_type' => 'motorcycle',
                    'vehicle_number' => 'BA-99-PA-1234',
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'availability_status' => 'online',
                    'cod_level' => 'level_3',
                    'cod_limit' => 50000.00,
                    'current_outstanding_cod' => 0.00,
                    'trust_score' => 100,
                    'rating' => 5.0,
                ]
            );
        }

        $targetUrl = $user->dashboardUrl();

        // Safety check for session intended URL:
        // Clear any cross-portal mismatch stored in url.intended
        $intended = $request->session()->get('url.intended');
        if ($intended) {
            $isAuthorizedForIntended = true;
            $scope = method_exists($user, 'effectiveServiceScope') ? $user->effectiveServiceScope() : 'all';

            if (str_contains($intended, '/client') && !in_array($user->user_type, ['client', 'customer', 'super_admin', 'admin'], true)) {
                $isAuthorizedForIntended = false;
            } elseif (str_contains($intended, '/admin')) {
                if (!in_array($user->user_type, ['super_admin', 'admin'], true) && !($user->user_type === 'staff' && $scope === 'all')) {
                    $isAuthorizedForIntended = false;
                }
            } elseif (str_contains($intended, '/seller') && !in_array($user->user_type, ['seller', 'super_admin', 'admin'], true)) {
                $isAuthorizedForIntended = false;
            } elseif (str_contains($intended, '/rider') && !in_array($user->user_type, ['rider', 'super_admin', 'admin'], true)) {
                $isAuthorizedForIntended = false;
            } elseif (str_contains($intended, '/partner') && !in_array($user->user_type, ['partner', 'super_admin', 'admin'], true)) {
                $isAuthorizedForIntended = false;
            } elseif (str_contains($intended, '/overseas') && !in_array($user->user_type, ['overseas', 'super_admin', 'admin'], true)) {
                $isAuthorizedForIntended = false;
            } elseif (str_contains($intended, '/domestic')) {
                $canAccessDomestic = in_array($user->user_type, ['domestic_admin', 'super_admin', 'admin'], true) || 
                                     ($user->user_type === 'staff' && in_array($scope, ['domestic', 'ecommerce', 'all'], true));
                if (!$canAccessDomestic) {
                    $isAuthorizedForIntended = false;
                }
            } elseif (str_contains($intended, '/international')) {
                $canAccessInternational = in_array($user->user_type, ['international_admin', 'super_admin', 'admin'], true) || 
                                          ($user->user_type === 'staff' && in_array($scope, ['international', 'all'], true));
                if (!$canAccessInternational) {
                    $isAuthorizedForIntended = false;
                }
            }

            if (!$isAuthorizedForIntended) {
                $request->session()->forget('url.intended');
            }
        }

        return redirect()->intended($targetUrl);
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('info', 'You have been signed out successfully.');
    }
}
