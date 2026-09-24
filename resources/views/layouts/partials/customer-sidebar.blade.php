<!-- Client Portal Sidebar (Streamlined & Compact) -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-800 flex flex-col gap-2">
        <x-logo variant="white" size="sm" :href="route('client.dashboard')" />
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 w-fit tracking-wider uppercase">
            <i class="fas fa-user-shield text-[9px] text-teal-400"></i> Client Portal
        </span>
    </div>

    <!-- Client Identity Card -->
    <div class="p-3 mx-3 my-3 rounded-xl bg-slate-800/60 border border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-teal-600 to-emerald-500 rounded-xl flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                {{ strtoupper(substr(auth()->user()?->name ?? 'C', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ auth()->user()?->name ?? 'Valued Client' }}</p>
                <p class="text-[11px] text-teal-300/80 truncate">{{ auth()->user()?->email ?? 'Guest Access' }}</p>
            </div>
        </div>
    </div>

    <!-- Compact Core Client Navigation -->
    <nav class="p-3 space-y-1.5 text-xs">
        <!-- 1. Client Dashboard -->
        <a href="{{ route('client.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('client.dashboard') || request()->routeIs('dashboard') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('client.dashboard') ? 'text-white' : 'text-teal-400' }}"></i>
            <span>Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ai.assistant*') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-robot w-4 text-center {{ request()->routeIs('ai.assistant*') ? 'text-white' : 'text-cyan-400' }}"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                Voice AI
            </span>
        </a>

        <!-- 2. Unified Ship & Pickup Operating Console -->
        <div class="rounded-xl border border-slate-700/60 bg-slate-800/40 p-2 space-y-1 my-1">
            <div class="px-2 py-1 flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-teal-300 flex items-center gap-1.5">
                    <i class="fas fa-boxes-packing text-teal-400"></i> Ship & Pickup Console
                </span>
                @php
                    $pendingInq = auth()->check() ? \App\Models\PickupRequest::where('seller_id', auth()->id())->whereIn('status', ['pending', 'assigned'])->count() : 0;
                @endphp
                @if($pendingInq > 0)
                    <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[9px] font-mono font-bold px-1.5 py-0.5 rounded">
                        {{ $pendingInq }} Active
                    </span>
                @endif
            </div>

            <!-- Create Shipment (Main Consignment Creation) -->
            <a href="{{ route('shipments.create') }}" 
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ request()->routeIs('shipments.create') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-box-archive w-4 text-center {{ request()->routeIs('shipments.create') ? 'text-white' : 'text-teal-300' }}"></i>
                <span class="font-semibold">Create Shipment</span>
                <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    Console
                </span>
            </a>
        </div>

        <!-- 4. Rate Calculator & Tariff Inquiry -->
        <a href="{{ route('rates.inquiry') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rates.inquiry*') || request()->routeIs('client.rates*') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-calculator w-4 text-center {{ request()->routeIs('rates.inquiry*') ? 'text-white' : 'text-amber-400' }}"></i>
            <span>Rate Calculator</span>
        </a>

        <!-- 4. Shipment History & Scoped Tracking -->
        <a href="{{ route('client.history') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('client.history*') || request()->routeIs('shipments.index*') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-clock-rotate-left w-4 text-center {{ request()->routeIs('client.history*') ? 'text-white' : 'text-emerald-400' }}"></i>
            <span>History & Tracking</span>
            @php
                $activeShipmentsCount = auth()->check() ? \App\Models\Shipment::where('customer_id', auth()->id())
                    ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
                    ->count() : 0;
            @endphp
            @if($activeShipmentsCount > 0)
                <span class="ml-auto bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $activeShipmentsCount }} Live
                </span>
            @endif
        </a>

        <!-- 5. Profile & Settings -->
        <a href="{{ route('profile') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('profile*') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-id-badge w-4 text-center {{ request()->routeIs('profile*') ? 'text-white' : 'text-purple-400' }}"></i>
            <span>My Profile</span>
        </a>

        <!-- Quick Track Input Box -->
        <div class="pt-4 px-1">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-2 mb-1.5">Track Consignment</p>
            <form action="{{ route('tracking.search') }}" method="GET" class="relative">
                <input type="text" name="tracking" placeholder="Enter AWB / HAWB..." required
                       class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl pl-8 pr-2 py-2 text-[11px] text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 font-mono">
                <i class="fas fa-search absolute left-2.5 top-2.5 text-slate-500 text-[10px]"></i>
            </form>
        </div>

        <!-- Authentication Action (Sign Out / Sign In) -->
        <div class="pt-4 border-t border-slate-800 mt-4">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-red-500/10 transition text-slate-400 hover:text-red-400 font-medium text-xs">
                        <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                        <span>Sign Out</span>
                    </button>
                </form>
            @else
                <div class="space-y-2">
                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-sm">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </a>
                    <a href="{{ route('register') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-teal-300 font-medium text-xs border border-slate-700 transition">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </a>
                </div>
            @endauth
        </div>
    </nav>
</aside>
