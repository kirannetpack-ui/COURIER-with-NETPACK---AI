<!-- Compact Rider Sidebar (Nordic Light Theme) -->
<aside class="bg-white text-slate-800 border-r border-slate-200/90 flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-50 fixed lg:static top-0 left-0 shadow-lg lg:shadow-none transition-all duration-300 ease-in-out w-72 max-w-[85vw] lg:w-64"
       :class="sidebarOpen ? 'translate-x-0 lg:ml-0' : '-translate-x-full lg:-ml-64'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-100 flex flex-col gap-2 bg-slate-50/50">
        <div class="flex items-center justify-between">
            <x-logo variant="dark" size="sm" :href="route('rider.dashboard')" />
            <button @click="sidebarOpen = false" 
                    type="button" 
                    class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg lg:hidden"
                    title="Close Sidebar">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80 w-fit tracking-wider uppercase">
                <i class="fas fa-motorcycle text-[9px] text-amber-600"></i> Rider Cockpit
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ auth()->user()->is_online ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ auth()->user()->is_online ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                {{ auth()->user()->is_online ? 'ONLINE' : 'OFFLINE' }}
            </span>
        </div>
    </div>
    
    <!-- Rider Info & Deposit / COD Limit Card -->
    @php
        $riderProfile = auth()->user()->riderProfile;
        $availableCodLimit = $riderProfile ? max(0, $riderProfile->cod_limit - $riderProfile->current_outstanding_cod) : (auth()->user()->rider_deposit_balance ?? 0);
        $availableCount = \App\Models\ShipmentAssignment::where('status', 'assigned')->whereNull('rider_profile_id')->count();
        $myActiveCount = $riderProfile ? \App\Models\ShipmentAssignment::where('rider_profile_id', $riderProfile->id)->whereIn('status', ['accepted', 'arrived_pickup', 'picked_up', 'in_transit', 'out_for_delivery'])->count() : 0;
    @endphp
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-50 border border-slate-200/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                <i class="fas fa-helmet-safety text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-slate-900 truncate">{{ auth()->user()->name ?? 'Rider Fleet' }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($riderProfile && $riderProfile->is_verified)
                        <span class="text-[9px] bg-emerald-50 text-emerald-800 border border-emerald-200 px-1 rounded font-bold">VERIFIED</span>
                    @else
                        <span class="text-[9px] bg-amber-50 text-amber-800 border border-amber-200 px-1 rounded font-bold">PENDING KYC</span>
                    @endif
                    @if($riderProfile && $riderProfile->other_platform_affiliation && $riderProfile->other_platform_affiliation !== 'none')
                        <span class="text-[9px] text-slate-500 truncate">({{ ucfirst($riderProfile->other_platform_affiliation) }})</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
            <span class="text-slate-500 font-medium">COD Available</span>
            <span class="font-bold font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Rs. {{ number_format($availableCodLimit, 2) }}</span>
        </div>
    </div>

    <!-- Quick Scan Available Delivery Pool Action -->
    <div class="px-3 py-1.5">
        <a href="{{ route('rider.delivery.available') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
            <i class="fas fa-radar text-sm"></i>
            <span>Scan Available Jobs</span>
            @if($availableCount > 0)
                <span class="bg-white text-amber-900 text-[10px] font-mono px-1.5 py-0.5 rounded-full font-black">
                    {{ $availableCount }}
                </span>
            @endif
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- 1. Dashboard -->
        <a href="{{ route('rider.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rider.dashboard') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('rider.dashboard') ? 'text-amber-600' : 'text-slate-400' }}"></i>
            <span>Rider Cockpit</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ai.assistant*') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-robot w-4 text-center text-teal-600"></i>
            <span>AI Copilot (Voice & OTP)</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-100/70 text-teal-800 border border-teal-200">
                Voice
            </span>
        </a>

        <!-- 2. Available Jobs Pool -->
        <a href="{{ route('rider.delivery.available') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rider.delivery.available') || request()->routeIs('rider.orders.available') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-search-location w-4 text-center text-amber-500"></i>
            <span>Available Jobs</span>
            @if($availableCount > 0)
                <span class="ml-auto bg-amber-100 text-amber-800 border border-amber-300 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                    {{ $availableCount }} New
                </span>
            @endif
        </a>

        <!-- 3. Active Deliveries & Route -->
        <a href="{{ route('rider.delivery.my') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rider.delivery.my*') || request()->routeIs('rider.delivery.show*') || request()->routeIs('rider.orders.my*') || request()->routeIs('rider.deliveries*') || request()->routeIs('rider.history') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-route w-4 text-center text-emerald-600"></i>
            <span>My Deliveries</span>
            @if($myActiveCount > 0)
                <span class="ml-auto bg-emerald-100/80 text-emerald-800 border border-emerald-200 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                    {{ $myActiveCount }} Active
                </span>
            @endif
        </a>

        <!-- 4. COD Collections & Earnings -->
        <a href="{{ route('rider.delivery.cod') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rider.delivery.cod*') || request()->routeIs('rider.cod*') || request()->routeIs('rider.earnings*') || request()->routeIs('rider.wallet*') || request()->routeIs('rider.deposit*') || request()->routeIs('rider.payment-methods*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-hand-holding-dollar w-4 text-center text-emerald-600"></i>
            <span>COD & Earnings</span>
        </a>

        <!-- 5. Service Areas & Radius -->
        <a href="{{ route('rider.service-areas.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rider.service-areas*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-map-location-dot w-4 text-center text-teal-600"></i>
            <span>Service Areas</span>
        </a>

        <!-- 6. Rider Settings & Vehicle -->
        <a href="{{ route('rider.settings') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('rider.settings*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-gear w-4 text-center text-slate-400"></i>
            <span>Settings & Vehicle</span>
        </a>

        <!-- Live Radar Map Link -->
        <div class="pt-2 border-t border-slate-100 mt-2">
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-amber-700 group font-medium">
                <i class="fas fa-satellite-dish w-4 text-center text-amber-600 group-hover:scale-110 transition"></i>
                <span>Live Radar Map</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] text-slate-400"></i>
            </a>
        </div>

        <!-- Logout -->
        <div class="pt-2 border-t border-slate-100 mt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-red-50 transition text-slate-500 hover:text-red-600 font-medium">
                    <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>
</aside>