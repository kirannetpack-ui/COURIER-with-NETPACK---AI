<!-- Compact Rider Sidebar -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-800 flex flex-col gap-2">
        <x-logo variant="white" size="sm" :href="route('rider.dashboard')" />
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 w-fit tracking-wider uppercase">
                <i class="fas fa-motorcycle text-[9px] text-amber-400"></i> Rider Cockpit
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ auth()->user()->is_online ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-700 text-slate-300' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ auth()->user()->is_online ? 'bg-emerald-400 animate-pulse' : 'bg-slate-400' }}"></span>
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
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-amber-600 to-yellow-500 rounded-xl flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                <i class="fas fa-helmet-safety text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ auth()->user()->name ?? 'Rider Fleet' }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($riderProfile && $riderProfile->is_verified)
                        <span class="text-[9px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1 rounded font-bold">VERIFIED</span>
                    @else
                        <span class="text-[9px] bg-amber-500/20 text-amber-300 border border-amber-500/30 px-1 rounded font-bold">PENDING KYC</span>
                    @endif
                    @if($riderProfile && $riderProfile->other_platform_affiliation && $riderProfile->other_platform_affiliation !== 'none')
                        <span class="text-[9px] text-slate-400 truncate">({{ ucfirst($riderProfile->other_platform_affiliation) }})</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">COD Available</span>
            <span class="font-bold font-mono text-emerald-400">Rs. {{ number_format($availableCodLimit, 2) }}</span>
        </div>
    </div>

    <!-- Quick Scan Available Delivery Pool Action -->
    <div class="px-3 py-1.5">
        <a href="{{ route('rider.delivery.available') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-500 hover:to-yellow-500 text-white font-bold text-xs shadow-md shadow-amber-900/30 transition transform hover:-translate-y-0.5">
            <i class="fas fa-radar text-sm"></i>
            <span>Scan Available Jobs</span>
            @if($availableCount > 0)
                <span class="bg-white text-amber-800 text-[10px] font-mono px-1.5 py-0.2 rounded-full font-black">
                    {{ $availableCount }}
                </span>
            @endif
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- 1. Dashboard -->
        <a href="{{ route('rider.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('rider.dashboard') ? 'bg-amber-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-4 text-center"></i>
            <span>Rider Cockpit</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('ai.assistant*') ? 'bg-amber-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-robot w-4 text-center text-teal-400"></i>
            <span>AI Copilot (Voice & OTP)</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                Voice
            </span>
        </a>

        <!-- 2. Available Jobs Pool -->
        <a href="{{ route('rider.delivery.available') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('rider.delivery.available') || request()->routeIs('rider.orders.available') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-search-location w-4 text-center text-yellow-400"></i>
            <span>Available Jobs</span>
            @if($availableCount > 0)
                <span class="ml-auto bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                    {{ $availableCount }} New
                </span>
            @endif
        </a>

        <!-- 3. Active Deliveries & Route -->
        <a href="{{ route('rider.delivery.my') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('rider.delivery.my*') || request()->routeIs('rider.delivery.show*') || request()->routeIs('rider.orders.my*') || request()->routeIs('rider.deliveries*') || request()->routeIs('rider.history') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-route w-4 text-center text-emerald-400"></i>
            <span>My Deliveries</span>
            @if($myActiveCount > 0)
                <span class="ml-auto bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                    {{ $myActiveCount }} Active
                </span>
            @endif
        </a>

        <!-- 4. COD Collections & Earnings -->
        <a href="{{ route('rider.delivery.cod') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('rider.delivery.cod*') || request()->routeIs('rider.cod*') || request()->routeIs('rider.earnings*') || request()->routeIs('rider.wallet*') || request()->routeIs('rider.deposit*') || request()->routeIs('rider.payment-methods*') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-hand-holding-dollar w-4 text-center text-emerald-400"></i>
            <span>COD & Earnings</span>
        </a>

        <!-- 5. Service Areas & Radius -->
        <a href="{{ route('rider.service-areas.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('rider.service-areas*') ? 'bg-amber-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-map-location-dot w-4 text-center text-teal-400"></i>
            <span>Service Areas</span>
        </a>

        <!-- 6. Rider Settings & Vehicle -->
        <a href="{{ route('rider.settings') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('rider.settings*') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-gear w-4 text-center text-slate-400"></i>
            <span>Settings & Vehicle</span>
        </a>

        <!-- Live Radar Map Link -->
        <div class="pt-2 border-t border-slate-800/80 mt-2">
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-amber-300 group">
                <i class="fas fa-satellite-dish w-4 text-center text-amber-400 group-hover:scale-110 transition"></i>
                <span class="font-medium">Live Radar Map</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] opacity-60"></i>
            </a>
        </div>

        <!-- Logout -->
        <div class="pt-2 border-t border-slate-800 mt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-500/10 transition text-slate-400 hover:text-red-400 font-medium">
                    <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>
</aside>