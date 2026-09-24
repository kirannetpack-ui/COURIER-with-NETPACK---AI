<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Overseas Hub Panel') - COURIER with NETPACK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
</head>
<body>
    <div x-data="{ sidebarOpen: true }" class="flex min-h-screen bg-gray-100 relative">
        <!-- International / Overseas Sidebar -->
        @include('layouts.partials.international-sidebar')

        <!-- Mobile Backdrop Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-xs lg:hidden"
             style="display: none;"></div>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 overflow-y-auto">
            <!-- Top Bar -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <div class="p-4 sm:p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-4">
                        {{ session('info') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Omnipresent AI Logistics Copilot (Voice & Text) -->
    <x-ai-copilot-widget />

    @stack('scripts')
</body>
</html>
