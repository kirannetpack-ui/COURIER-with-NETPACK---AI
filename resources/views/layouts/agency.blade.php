<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Hub Portal - COURIER with NETPACK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <nav class="bg-white text-slate-800 shadow-xs border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center font-bold text-lg shadow-2xs">
                        <i class="fas fa-building"></i>
                    </span>
                    <div>
                        <span class="font-black text-lg tracking-tight text-slate-900">Agency Inbound Desk</span>
                        <span class="ml-2 text-xs bg-teal-50 text-teal-800 border border-teal-200 px-2.5 py-0.5 rounded-full font-medium">
                            {{ auth('agency')->user()?->name ?? auth()->user()?->name ?? 'Hub Staff' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 sm:space-x-4 text-xs font-semibold">
                    <a href="{{ route('agency.manifests.index') }}" class="text-slate-600 hover:text-teal-700 hover:bg-slate-50 flex items-center gap-1.5 px-3 py-2 rounded-xl transition">
                        <i class="fas fa-plane-arrival text-indigo-500"></i> Inbound Flight Manifests
                    </a>
                    <a href="{{ route('agency.scan') }}" class="text-slate-600 hover:text-emerald-700 hover:bg-slate-50 flex items-center gap-1.5 px-3 py-2 rounded-xl transition">
                        <i class="fas fa-qrcode text-emerald-500"></i> Scan Box QR
                    </a>
                    <a href="{{ route('agency.shipments.index') }}" class="text-slate-600 hover:text-teal-700 hover:bg-slate-50 flex items-center gap-1.5 px-3 py-2 rounded-xl transition">
                        <i class="fas fa-boxes text-slate-500"></i> Shipments
                    </a>
                    <a href="{{ route('international.dashboard') }}" class="text-slate-500 hover:text-slate-900 hover:bg-slate-50 px-2.5 py-2 rounded-xl transition flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Admin Portal
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="py-6">
        @yield('content')
    </main>
</body>
</html>
