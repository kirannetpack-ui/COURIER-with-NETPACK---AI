<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <x-logo size="md" :href="route('home')" />

            <nav class="flex items-center gap-3 text-sm font-medium">
                <a href="{{ route('tracking.page') }}" class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-teal-700">Track shipment</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-white hover:bg-slate-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-white hover:bg-slate-700">Sign in</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="mt-12 border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-sm text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <p>&copy; {{ now()->year }} COURIER with NETPACK</p>
            <p>Secure shipment updates without exposing private contact details.</p>
        </div>
    </footer>

    <!-- Omnipresent AI Logistics Copilot (Voice & Text) -->
    <x-ai-copilot-widget />

    @stack('scripts')
</body>
</html>
