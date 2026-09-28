<!-- Client Portal Sidebar (Light Nordic Logistics Theme) -->
<aside class="bg-white text-slate-800 border-r border-slate-200/90 flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-50 fixed lg:static top-0 left-0 shadow-lg lg:shadow-none transition-all duration-300 ease-in-out w-72 max-w-[85vw] lg:w-64"
       :class="sidebarOpen ? 'translate-x-0 lg:ml-0' : '-translate-x-full lg:-ml-64'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-2 bg-slate-50/50">
        <div class="flex flex-col gap-1.5">
            <x-logo variant="dark" size="sm" :href="route('client.dashboard')" />
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200/70 w-fit tracking-wider uppercase">
                <i class="fas fa-user-shield text-[9px] text-teal-600"></i> Client Portal
            </span>
        </div>
        <button @click="sidebarOpen = false" 
                type="button" 
                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg lg:hidden"
                title="Close Sidebar">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    <!-- Client Identity Card -->
    <div class="p-3 mx-3 my-3 rounded-xl bg-slate-50 border border-slate-200/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-teal-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                {{ strtoupper(substr(auth()->user()?->name ?? 'C', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-slate-900 truncate">{{ auth()->user()?->name ?? 'Valued Client' }}</p>
                <p class="text-[11px] text-teal-700 font-medium truncate">{{ auth()->user()?->email ?? 'Guest Access' }}</p>
            </div>
        </div>
    </div>

    <!-- Compact Core Client Navigation -->
    <nav class="p-3 space-y-1.5 text-xs">
        <!-- 1. Client Dashboard -->
        <a href="{{ route('client.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('client.dashboard') || request()->routeIs('dashboard') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('client.dashboard') ? 'text-teal-600' : 'text-slate-400' }}"></i>
            <span>Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ai.assistant*') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-robot w-4 text-center {{ request()->routeIs('ai.assistant*') ? 'text-teal-600' : 'text-teal-500' }}"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-100/70 text-teal-800 border border-teal-200">
                Voice AI
            </span>
        </a>

        <!-- 2. Unified Ship & Pickup Operating Console -->
        <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-2 space-y-1 my-1">
            <div class="px-2 py-1 flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-teal-800 flex items-center gap-1.5">
                    <i class="fas fa-boxes-packing text-teal-600"></i> Ship & Pickup Console
                </span>
                @php
                    $pendingInq = auth()->check() ? \App\Models\PickupRequest::where('seller_id', auth()->id())->whereIn('status', ['pending', 'assigned'])->count() : 0;
                @endphp
                @if($pendingInq > 0)
                    <span class="bg-amber-100 text-amber-800 border border-amber-300 text-[9px] font-mono font-bold px-1.5 py-0.5 rounded">
                        {{ $pendingInq }} Active
                    </span>
                @endif
            </div>

            <!-- Create Shipment (Main Consignment Creation) -->
            <a href="{{ route('shipments.create') }}" 
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ request()->routeIs('shipments.create') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-white hover:text-teal-800 hover:shadow-2xs' }}">
                <i class="fas fa-box-archive w-4 text-center {{ request()->routeIs('shipments.create') ? 'text-white' : 'text-teal-600' }}"></i>
                <span class="font-semibold">Create Shipment</span>
                <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded {{ request()->routeIs('shipments.create') ? 'bg-white/20 text-white' : 'bg-teal-100/80 text-teal-800 border border-teal-200' }}">
                    Console
                </span>
            </a>
        </div>

        <!-- 3. Rate Calculator & Tariff Inquiry -->
        <a href="{{ route('rates.inquiry') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rates.inquiry*') || request()->routeIs('client.rates*') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-calculator w-4 text-center {{ request()->routeIs('rates.inquiry*') ? 'text-teal-600' : 'text-amber-500' }}"></i>
            <span>Rate Calculator</span>
        </a>

        <!-- 4. Shipment History & Scoped Tracking -->
        <a href="{{ route('client.history') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('client.history*') || request()->routeIs('shipments.index*') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-clock-rotate-left w-4 text-center {{ request()->routeIs('client.history*') ? 'text-teal-600' : 'text-emerald-500' }}"></i>
            <span>History & Tracking</span>
            @php
                $activeShipmentsCount = auth()->check() ? \App\Models\Shipment::where('customer_id', auth()->id())
                    ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
                    ->count() : 0;
            @endphp
            @if($activeShipmentsCount > 0)
                <span class="ml-auto bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $activeShipmentsCount }} Live
                </span>
            @endif
        </a>

        <!-- 5. Profile & Settings -->
        <a href="{{ route('profile') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('profile*') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-id-badge w-4 text-center {{ request()->routeIs('profile*') ? 'text-teal-600' : 'text-purple-500' }}"></i>
            <span>My Profile</span>
        </a>

        <!-- 6. Mobile App Simulator -->
        <a href="{{ route('mobile.simulator') }}" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-teal-700 font-medium group">
            <i class="fas fa-mobile-screen w-4 text-center text-teal-600 group-hover:scale-110 transition"></i>
            <span>App Simulator</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200">
                Studio
            </span>
        </a>

        <!-- Quick Track Input Box -->
        <div class="pt-4 px-1">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-2 mb-1.5">Track Consignment</p>
            <form action="{{ route('tracking.search') }}" method="GET" class="relative">
                <input type="text" name="tracking" placeholder="Enter AWB / HAWB..." required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-2 py-2 text-[11px] text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 font-mono transition">
                <i class="fas fa-search absolute left-2.5 top-2.5 text-slate-400 text-[10px]"></i>
            </form>
        </div>

        <!-- Authentication Action (Sign Out / Sign In) -->
        <div class="pt-4 border-t border-slate-100 mt-4">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-red-50 transition text-slate-500 hover:text-red-600 font-medium text-xs">
                        <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                        <span>Sign Out</span>
                    </button>
                </form>
            @else
                <div class="space-y-2">
                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-xs">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </a>
                    <a href="{{ route('register') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-medium text-xs border border-slate-200 transition">
                        <i class="fas fa-user-plus text-teal-600"></i>
                        <span>Create Account</span>
                    </a>
                </div>
            @endauth
        </div>
    </nav>
</aside>
