<!-- Super Admin Sidebar (Monitoring, Executive Check & International Rates) -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-800 flex flex-col gap-2">
        <x-logo variant="white" size="sm" :href="route('admin.dashboard')" />
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 w-fit tracking-wider uppercase">
            <i class="fas fa-crown text-[10px] text-amber-400"></i> Super Administrator
        </span>
    </div>
    
    <!-- Super Admin Identity Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-purple-600 to-indigo-500 rounded-xl flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                <p class="text-[11px] text-purple-300/80 truncate">{{ auth()->user()->email ?? 'admin@netpack.com' }}</p>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">System Role</span>
            <span class="font-bold text-purple-300">Executive Monitor</span>
        </div>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- 1. Operations Overview Dashboard -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-purple-400' }}"></i>
            <span>Operations Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice Hub -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ai.assistant*') ? 'bg-purple-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-robot w-4 text-center text-teal-400"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                Voice AI
            </span>
        </a>

        <!-- ============================================== -->
        <!-- MONITORING & TRACKING RADAR (Executive Check)  -->
        <!-- ============================================== -->
        <div class="pt-3">
            <div class="flex items-center justify-between px-3 mb-1">
                <p class="text-[10px] text-purple-400 font-extrabold uppercase tracking-widest">Network Monitoring</p>
                <span class="px-1.5 py-0.2 bg-purple-500/20 text-purple-300 text-[9px] rounded font-mono font-bold">RADAR</span>
            </div>

            <!-- Global Consignment Registry & Audit -->
            <a href="{{ route('admin.shipments.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.shipments.index') || request()->routeIs('admin.shipments.show') ? 'bg-purple-600/30 text-purple-200 border border-purple-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-blue-400"></i>
                <span>Master Consignments</span>
                <span class="ml-auto bg-blue-500/20 text-blue-300 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ \App\Models\Shipment::count() }}</span>
            </a>

            <!-- Tracking & MAWB Dispatch Console -->
            <a href="{{ route('admin.tracking.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.tracking*') || request()->routeIs('admin.shipments.tracking*') ? 'bg-purple-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-satellite-dish w-4 text-center {{ request()->routeIs('admin.tracking*') ? 'text-white' : 'text-indigo-400' }}"></i>
                <span>Tracking & MAWB Console</span>
                @php
                    $pendingMawbCount = \App\Models\Shipment::whereNull('mawb_number')->whereNotIn('status', ['delivered', 'cancelled'])->count();
                @endphp
                @if($pendingMawbCount > 0)
                    <span class="ml-auto bg-amber-500/30 text-amber-300 text-[9px] font-mono font-bold px-1.5 py-0.5 rounded" title="{{ $pendingMawbCount }} shipments need MAWB">
                        {{ $pendingMawbCount }}
                    </span>
                @endif
            </a>

            <!-- Rider GPS Fleet Radar -->
            <a href="{{ route('admin.riders.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.riders*') ? 'bg-purple-600/30 text-purple-200 border border-purple-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-motorcycle w-4 text-center text-amber-400"></i>
                <span>Rider GPS Fleet</span>
                @php
                    $onlineRiders = \App\Models\User::where('user_type', 'rider')->where('is_online', true)->count();
                @endphp
                <span class="ml-auto bg-emerald-500/20 text-emerald-300 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ $onlineRiders }} Live
                </span>
            </a>

            <!-- SLA & Delay Exceptions Monitor -->
            <a href="{{ route('admin.communications') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.communications*') ? 'bg-purple-600/30 text-purple-200 border border-purple-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-triangle-exclamation w-4 text-center text-rose-400"></i>
                <span>Delay & SLA Alerts</span>
                @php
                    $pendingReminders = \App\Models\DeliveryReminder::where('is_sent', false)->count();
                @endphp
                @if($pendingReminders > 0)
                    <span class="ml-auto bg-rose-500/20 text-rose-300 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ $pendingReminders }}</span>
                @endif
            </a>

            <!-- Public Radar Portal Link -->
            <a href="{{ route('tracking.page') }}" target="_blank"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-white group">
                <i class="fas fa-satellite-dish w-4 text-center text-teal-400 group-hover:scale-110 transition"></i>
                <span class="font-medium">Master Radar Map</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] opacity-60"></i>
            </a>
        </div>

        <div class="pt-3">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">Domestic Network</p>
            <a href="{{ route('admin.domestic.rates') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.domestic.rates*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><i class="fas fa-money-bill-wave w-4 text-center text-emerald-400"></i><span>Domestic Rates</span></a>
            <a href="{{ route('admin.domestic.zones') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.domestic.zones*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><i class="fas fa-map-location-dot w-4 text-center text-blue-400"></i><span>Delivery Territories</span></a>
            <a href="{{ route('admin.domestic.assignments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.domestic.assignments*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><i class="fas fa-route w-4 text-center text-amber-400"></i><span>Partner Routing</span></a>
            <a href="{{ route('admin.domestic.shipments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.domestic.shipments*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><i class="fas fa-truck-ramp-box w-4 text-center text-teal-400"></i><span>Domestic Shipments</span></a>
        </div>

        <!-- ============================================== -->
        <!-- INTERNATIONAL RATES FEEDING (Super Admin Task) -->
        <!-- ============================================== -->
        <div class="pt-3">
            <div class="flex items-center justify-between px-3 mb-1">
                <p class="text-[10px] text-purple-400 font-extrabold uppercase tracking-widest">International Tariffs</p>
                <span class="px-1.5 py-0.2 bg-teal-500/20 text-teal-300 text-[9px] rounded font-mono font-bold">FEED</span>
            </div>
            
            <!-- Tariff Matrices (0.5kg Slabs & Tiers) -->
            <a href="{{ route('admin.international-rates.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.international-rates.index') || request()->routeIs('admin.international-rates.create') || request()->routeIs('admin.international-rates.edit') ? 'bg-purple-600/30 text-purple-200 border border-purple-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-table-cells w-4 text-center text-teal-300"></i>
                <span>Feed Tariff Matrices</span>
            </a>

            <!-- Dynamic Tariff & Packaging Rules -->
            <a href="{{ route('admin.international-rates.settings') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.international-rates.settings*') ? 'bg-purple-600/30 text-purple-200 border border-purple-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-sliders w-4 text-center text-amber-300"></i>
                <span>Tariff & Packaging Rules</span>
            </a>

            <!-- Rate Inquiry & Verifier Desk -->
            <a href="{{ route('rates.inquiry') }}" target="_blank" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('rates.inquiry*') ? 'bg-purple-600/30 text-purple-200 border border-purple-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-calculator w-4 text-center text-sky-400"></i>
                <span>Rate Verification Desk</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- USER & STAKEHOLDER OVERSIGHT                   -->
        <!-- ============================================== -->
        <div class="pt-3">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">Users & Partners</p>
            
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.users*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-users-gear w-4 text-center text-indigo-400"></i>
                <span>All Users & Approvals</span>
                @php
                    $pendingUsers = \App\Models\User::where('verification_status', 'pending')->count();
                @endphp
                @if($pendingUsers > 0)
                    <span class="ml-auto bg-amber-500/20 text-amber-300 text-[10px] px-2 py-0.5 rounded-full font-mono">{{ $pendingUsers }} Pending</span>
                @else
                    <span class="ml-auto bg-slate-700 text-slate-200 text-[10px] px-2 py-0.5 rounded-full font-mono">{{ \App\Models\User::count() }}</span>
                @endif
            </a>

            <a href="{{ route('admin.partners.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.partners*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-handshake w-4 text-center text-teal-400"></i>
                <span>Domestic Partners</span>
                <span class="ml-auto bg-slate-700 text-slate-200 text-[10px] px-2 py-0.5 rounded-full font-mono">{{ \App\Models\User::where('user_type', 'partner')->count() }}</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- FINANCIAL SETTLEMENTS (Audit & Check)          -->
        <!-- ============================================== -->
        <div class="pt-3">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">Settlement Audits</p>
            
            <a href="{{ route('admin.cod-settlements.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.cod-settlements*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-money-bill-transfer w-4 text-center text-emerald-400"></i>
                <span>COD Settlements</span>
                @php
                    $pendingCod = \App\Models\CODSettlement::where('settlement_status', 'pending')->count();
                @endphp
                @if($pendingCod > 0)
                    <span class="ml-auto bg-rose-500/20 text-rose-300 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ $pendingCod }}</span>
                @endif
            </a>
            
            <a href="{{ route('admin.partner-charges.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.partner-charges*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-hand-holding-dollar w-4 text-center text-amber-400"></i>
                <span>Partner Charges</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- SYSTEM & REPORTS                               -->
        <!-- ============================================== -->
        <div class="pt-3">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">System & Reports</p>
            
            <a href="{{ route('admin.reports') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.reports*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-chart-line w-4 text-center text-sky-400"></i>
                <span>Analytics & Reports</span>
            </a>

            <a href="{{ route('admin.services.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.services*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-clock w-4 text-center text-teal-400"></i>
                <span>Services & Transit SLAs</span>
            </a>

            <a href="{{ route('admin.settings') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.settings*') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-sliders w-4 text-center text-slate-400"></i>
                <span>System Settings</span>
            </a>

            <a href="{{ route('profile') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('profile') ? 'bg-purple-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-id-badge w-4 text-center text-purple-300"></i>
                <span>My Profile</span>
            </a>
            
            <form method="POST" action="{{ route('logout') }}" class="block pt-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-500/10 transition text-slate-400 hover:text-red-400 font-medium">
                    <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
