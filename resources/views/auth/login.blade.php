<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In &middot; COURIER with NETPACK</title>

    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Vite Pipeline Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-heading { font-family: 'Outfit', 'Inter', sans-serif; }
        .hero-mesh {
            background: radial-gradient(at 0% 0%, rgba(204, 251, 241, 0.6) 0px, transparent 50%),
                        radial-gradient(at 100% 100%, rgba(224, 242, 254, 0.5) 0px, transparent 50%),
                        linear-gradient(135deg, #f8fafc 0%, #f1f5f9 55%, #e2e8f0 100%);
        }
    </style>
</head>
<body class="min-h-full hero-mesh flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-800 antialiased selection:bg-teal-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <div class="fixed top-0 left-0 right-0 z-20 px-6 py-4 flex justify-between items-center bg-white/80 backdrop-blur-md border-b border-slate-200/80">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-teal-700 text-xs font-semibold tracking-wider transition">
            <i class="fas fa-arrow-left text-teal-600"></i> Back to Homepage
        </a>
        <div class="flex items-center gap-3 text-xs">
            <span class="text-slate-500 hidden sm:inline">Need assistance?</span>
            <a href="tel:+97715970123" class="text-teal-700 font-semibold hover:text-teal-800 transition flex items-center gap-1.5">
                <i class="fas fa-phone-volume"></i> +977-1-5970123
            </a>
        </div>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4 mt-8">
        <!-- Main Login Card -->
        <div class="bg-white py-8 px-6 sm:px-10 rounded-3xl shadow-xl border border-slate-200/90 space-y-6">
            
            <!-- Brand Logo Center -->
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <x-logo size="xl" :href="route('home')" />
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight font-heading">
                    Account Portal Sign In
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Secure access for operations, merchants, partners, and riders
                </p>
            </div>

            <!-- Error Alerts -->
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-start gap-3 text-xs leading-relaxed animate-shake">
                    <i class="fas fa-circle-exclamation text-rose-600 text-base shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Authentication notice:</strong>
                        <p class="mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-start gap-3 text-xs leading-relaxed">
                    <i class="fas fa-triangle-exclamation text-rose-600 text-base shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Please correct the errors below:</strong>
                        <ul class="list-disc list-inside mt-1 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-xs">
                    <i class="fas fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Quick Demo Role Switcher -->
            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-200/80">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-bolt text-amber-500"></i> Quick Role Demo Autofill
                    </span>
                    <span class="text-[10px] text-slate-400">Click to load credentials</span>
                </div>
                <div class="grid grid-cols-3 gap-1.5 text-[11px]">
                    <button type="button" onclick="fillRole('superadmin@netpack.test', 'Netpack!Admin#2026', 'Super Admin')" 
                            class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-teal-500 hover:text-teal-700 font-semibold transition text-center shadow-2xs">
                        👑 Super Admin
                    </button>
                    <button type="button" onclick="fillRole('international.admin@netpack.test', 'Netpack!International#2026', 'International Admin')" 
                            class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-teal-500 hover:text-teal-700 font-semibold transition text-center shadow-2xs">
                        ✈️ Global Admin
                    </button>
                    <button type="button" onclick="fillRole('domestic.admin@netpack.test', 'Netpack!Domestic#2026', 'Domestic Admin')" 
                            class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-teal-500 hover:text-teal-700 font-semibold transition text-center shadow-2xs">
                        🚚 Domestic Admin
                    </button>
                    <button type="button" onclick="fillRole('seller@test.com', 'Netpack!Seller#2026', 'E-Commerce Seller')" 
                            class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-teal-500 hover:text-teal-700 font-semibold transition text-center shadow-2xs">
                        🏪 Seller Merchant
                    </button>
                    <button type="button" onclick="fillRole('rider@test.com', 'Netpack!Rider#2026', 'Delivery Rider')" 
                            class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-teal-500 hover:text-teal-700 font-semibold transition text-center shadow-2xs">
                        🛵 Delivery Rider
                    </button>
                    <button type="button" onclick="fillRole('customer@netpack.test', 'Netpack!Customer#2026', 'Client')" 
                            class="py-1.5 px-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-teal-500 hover:text-teal-700 font-semibold transition text-center shadow-2xs">
                        👤 Client
                    </button>
                </div>
            </div>

            <!-- LOGIN FORM -->
            <form method="POST" action="{{ route('login.submit') }}" id="loginForm" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span><i class="fas fa-envelope text-teal-600 mr-1.5"></i> Email Address</span>
                        <span class="text-[10px] text-rose-500 font-semibold">*Required</span>
                    </label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="your.email@netpack.test"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-500/15 transition shadow-2xs" />
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span><i class="fas fa-lock text-teal-600 mr-1.5"></i> Password</span>
                        <button type="button" onclick="togglePasswordVisibility()" class="text-[11px] text-teal-700 hover:underline">
                            <span id="pwdToggleText">Show password</span>
                        </button>
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               placeholder="••••••••••••"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-500/15 transition shadow-2xs" />
                    </div>
                </div>

                <!-- Remember Me & Public Tracking -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600 select-none">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} 
                               class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500/30" />
                        <span>Keep me signed in</span>
                    </label>
                    <a href="{{ route('tracking.page') }}" class="text-xs font-bold text-teal-700 hover:text-teal-800 transition">
                        Track a parcel instead &rarr;
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="loginBtn"
                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-teal-700 via-teal-600 to-teal-700 hover:from-teal-800 hover:to-teal-800 text-white font-bold text-sm shadow-md shadow-teal-700/25 transition transform active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fas fa-right-to-bracket text-base"></i>
                    <span>Sign In to Dashboard</span>
                </button>
            </form>

            <!-- Card Footer -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>New business account?</span>
                <a href="{{ route('register') }}" class="font-bold text-teal-700 hover:underline">
                    Register Client Account &rarr;
                </a>
            </div>
        </div>

        <!-- Security & IATA Compliance Footer -->
        <div class="text-center mt-6 text-xs text-slate-400 space-y-1">
            <p>&copy; {{ date('Y') }} COURIER with NETPACK Ltd. Registered Courier Operator &middot; Nepal</p>
            <p class="text-[11px] text-slate-400">Encrypted 256-Bit SSL Connection &middot; Role-Enforced Multi-Tenant Access</p>
        </div>
    </div>

    <!-- Script for autofill and form submission -->
    <script>
    function fillRole(email, password, roleName) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
        const btn = document.getElementById('loginBtn');
        btn.classList.add('ring-4', 'ring-teal-400');
        setTimeout(() => btn.classList.remove('ring-4', 'ring-teal-400'), 600);
    }

    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const text = document.getElementById('pwdToggleText');
        if (input.type === 'password') {
            input.type = 'text';
            text.innerText = 'Hide password';
        } else {
            input.type = 'password';
            text.innerText = 'Show password';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');

        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', function() {
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin text-base"></i> Authenticating...';
                loginBtn.classList.add('opacity-75', 'cursor-not-allowed');
            });
        }
    });
    </script>
</body>
</html>
