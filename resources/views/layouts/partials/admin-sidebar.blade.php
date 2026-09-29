<!-- Super Admin Sidebar (Nordic Light Theme) -->
<aside class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] lg:w-64 lg:static lg:inset-auto h-screen bg-white text-slate-800 border-r border-slate-200/90 flex-shrink-0 overflow-y-auto custom-scrollbar select-none shadow-2xl lg:shadow-none transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0 lg:ml-0' : '-translate-x-full lg:-ml-64'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-2 bg-slate-50/50">
        <div class="flex flex-col gap-1.5">
            <x-logo variant="dark" size="sm" :href="route('admin.dashboard')" />
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/70 w-fit tracking-wider uppercase">
                <i class="fas fa-crown text-[10px] text-amber-500"></i> Super Administrator
            </span>
        </div>
        <button @click="sidebarOpen = false" 
                type="button" 
                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg lg:hidden"
                title="Close Sidebar">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    
    <!-- Super Admin Identity Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-50 border border-slate-200/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-slate-900 truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                <p class="text-[11px] text-purple-700 font-medium truncate">{{ auth()->user()->email ?? 'admin@netpack.com' }}</p>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
            <span class="text-slate-500 font-medium">System Role</span>
            <span class="font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200/80">Executive Monitor</span>
        </div>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- 1. Operations Overview Dashboard -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('admin.dashboard') ? 'text-purple-600' : 'text-purple-500' }}"></i>
            <span>Operations Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice Hub -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ai.assistant*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-robot w-4 text-center text-teal-600"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200">
                Voice AI
            </span>
        </a>

        <!-- ============================================== -->
        <!-- MONITORING & TRACKING RADAR (Executive Check)  -->
        <!-- ============================================== -->
        <div class="pt-3">
            <div class="flex items-center justify-between px-3 mb-1">
                <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest">Network Monitoring</p>
                <span class="px-1.5 py-0.2 bg-purple-50 text-purple-700 border border-purple-200 text-[9px] rounded font-mono font-bold">RADAR</span>
            </div>

            <!-- Global Consignment Registry & Audit -->
            <a href="{{ route('admin.shipments.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.shipments.index') || request()->routeIs('admin.shipments.show') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-blue-500"></i>
                <span>Master Consignments</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ \App\Models\Shipment::count() }}</span>
            </a>

            <!-- Tracking & MAWB Dispatch Console -->
            <a href="{{ route('admin.tracking.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.tracking*') || request()->routeIs('admin.shipments.tracking*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-satellite-dish w-4 text-center {{ request()->routeIs('admin.tracking*') ? 'text-purple-600' : 'text-indigo-500' }}"></i>
                <span>Tracking & MAWB Console</span>
                @php
                    $pendingMawbCount = \App\Models\Shipment::whereNull('mawb_number')->whereNotIn('status', ['delivered', 'cancelled'])->count();
                @endphp
                @if($pendingMawbCount > 0)
                    <span class="ml-auto bg-amber-50 text-amber-800 border border-amber-300 text-[9px] font-mono font-bold px-1.5 py-0.5 rounded" title="{{ $pendingMawbCount }} shipments need MAWB">
                        {{ $pendingMawbCount }}
                    </span>
                @endif
            </a>

            <!-- Rider GPS Fleet Radar -->
            <a href="{{ route('admin.riders.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.riders*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-motorcycle w-4 text-center text-amber-500"></i>
                <span>Rider GPS Fleet</span>
                @php
                    $onlineRiders = \App\Models\User::where('user_type', 'rider')->where('is_online', true)->count();
                @endphp
                <span class="ml-auto bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] font-mono px-1.5 py-0.5 rounded flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $onlineRiders }} Live
                </span>
            </a>

            <!-- SLA & Delay Exceptions Monitor -->
            <a href="{{ route('admin.communications') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.communications*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-triangle-exclamation w-4 text-center text-rose-500"></i>
                <span>Delay & SLA Alerts</span>
                @php
                    $pendingReminders = \App\Models\DeliveryReminder::where('is_sent', false)->count();
                @endphp
                @if($pendingReminders > 0)
                    <span class="ml-auto bg-rose-50 text-rose-800 border border-rose-300 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ $pendingReminders }}</span>
                @endif
            </a>

            <!-- Public Radar Portal Link -->
            <a href="{{ route('tracking.page') }}" target="_blank"
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium group">
                <i class="fas fa-satellite-dish w-4 text-center text-teal-600 group-hover:scale-110 transition"></i>
                <span>Master Radar Map</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] text-slate-400"></i>
            </a>
        </div>

        <div class="pt-3">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">Domestic Network</p>
            <a href="{{ route('admin.domestic.rates') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.domestic.rates*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"><i class="fas fa-money-bill-wave w-4 text-center text-emerald-500"></i><span>Domestic Rates</span></a>
            <a href="{{ route('admin.domestic.zones') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.domestic.zones*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"><i class="fas fa-map-location-dot w-4 text-center text-blue-500"></i><span>Delivery Territories</span></a>
            <a href="{{ route('admin.domestic.assignments') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.domestic.assignments*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"><i class="fas fa-route w-4 text-center text-amber-500"></i><span>Partner Routing</span></a>
            <a href="{{ route('admin.domestic.shipments') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.domestic.shipments*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"><i class="fas fa-truck-ramp-box w-4 text-center text-teal-600"></i><span>Domestic Shipments</span></a>
        </div>

        <!-- ============================================== -->
        <!-- INTERNATIONAL HUBS & GATEWAYS (Super Admin)    -->
        <!-- ============================================== -->
        <div class="pt-3">
            <div class="flex items-center justify-between px-3 mb-1">
                <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest">Gateway Hubs & Partners</p>
                <span class="px-1.5 py-0.2 bg-indigo-50 text-indigo-700 border border-indigo-200 text-[9px] rounded font-mono font-bold">GLOBAL</span>
            </div>
            
            <!-- International Gateway Hubs -->
            <a href="{{ route('international.hubs.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.hubs.index') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-network-wired w-4 text-center text-indigo-500"></i>
                <span>International Hubs</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ \App\Models\OverseasHub::count() }}</span>
            </a>

            <!-- Create International Hub -->
            <a href="{{ route('international.hubs.create') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.hubs.create') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-plus-circle w-4 text-center text-emerald-500"></i>
                <span>Create New Hub</span>
                <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">+Add</span>
            </a>

            <!-- Partner Agencies -->
            <a href="{{ route('international.agencies.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.agencies*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-handshake w-4 text-center text-cyan-600"></i>
                <span>Partner Agencies</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ \App\Models\Agency::count() }}</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- INTERNATIONAL RATES FEEDING (Super Admin Task) -->
        <!-- ============================================== -->
        <div class="pt-3">
            <div class="flex items-center justify-between px-3 mb-1">
                <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest">International Tariffs</p>
                <span class="px-1.5 py-0.2 bg-teal-50 text-teal-700 border border-teal-200 text-[9px] rounded font-mono font-bold">FEED</span>
            </div>
            
            <!-- Tariff Matrices (0.5kg Slabs & Tiers) -->
            <a href="{{ route('admin.international-rates.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.international-rates.index') || request()->routeIs('admin.international-rates.create') || request()->routeIs('admin.international-rates.edit') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-table-cells w-4 text-center text-teal-600"></i>
                <span>Feed Tariff Matrices</span>
            </a>

            <!-- Dynamic Tariff & Packaging Rules -->
            <a href="{{ route('admin.international-rates.settings') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.international-rates.settings*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-sliders w-4 text-center text-amber-500"></i>
                <span>Tariff & Packaging Rules</span>
            </a>

            <!-- Rate Inquiry & Verifier Desk -->
            <a href="{{ route('rates.inquiry') }}" target="_blank" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('rates.inquiry*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-calculator w-4 text-center text-sky-500"></i>
                <span>Rate Verification Desk</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- USER & STAKEHOLDER OVERSIGHT                   -->
        <!-- ============================================== -->
        <div class="pt-3">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">Users & Partners</p>
            
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.users*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-users-gear w-4 text-center text-indigo-500"></i>
                <span>All Users & Approvals</span>
                @php
                    $pendingUsers = \App\Models\User::where('verification_status', 'pending')->count();
                @endphp
                @if($pendingUsers > 0)
                    <span class="ml-auto bg-amber-50 text-amber-800 border border-amber-200 text-[10px] px-2 py-0.5 rounded-full font-mono">{{ $pendingUsers }} Pending</span>
                @else
                    <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] px-2 py-0.5 rounded-full font-mono">{{ \App\Models\User::count() }}</span>
                @endif
            </a>

            <a href="{{ route('admin.partners.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.partners*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-handshake w-4 text-center text-teal-600"></i>
                <span>Domestic Partners</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] px-2 py-0.5 rounded-full font-mono">{{ \App\Models\User::where('user_type', 'partner')->count() }}</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- FINANCIAL SETTLEMENTS (Audit & Check)          -->
        <!-- ============================================== -->
        <div class="pt-3">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">Settlement Audits</p>
            
            <a href="{{ route('admin.cod-settlements.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.cod-settlements*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-money-bill-transfer w-4 text-center text-emerald-500"></i>
                <span>COD Settlements</span>
                @php
                    $pendingCod = \App\Models\CODSettlement::where('settlement_status', 'pending')->count();
                @endphp
                @if($pendingCod > 0)
                    <span class="ml-auto bg-rose-50 text-rose-800 border border-rose-200 text-[10px] font-mono px-1.5 py-0.5 rounded">{{ $pendingCod }}</span>
                @endif
            </a>
            
            <a href="{{ route('admin.partner-charges.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.partner-charges*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-hand-holding-dollar w-4 text-center text-amber-500"></i>
                <span>Partner Charges</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- SYSTEM & REPORTS                               -->
        <!-- ============================================== -->
        <div class="pt-3">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">System & Reports</p>
            
            <a href="{{ route('admin.reports') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.reports*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-chart-line w-4 text-center text-sky-500"></i>
                <span>Analytics & Reports</span>
            </a>

            <a href="{{ route('admin.services.index') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.services*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-clock w-4 text-center text-teal-500"></i>
                <span>Services & Transit SLAs</span>
            </a>

            <a href="{{ route('admin.settings') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.settings*') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-sliders w-4 text-center text-slate-500"></i>
                <span>System Settings</span>
            </a>

            <a href="{{ route('profile') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('profile') ? 'bg-purple-50 text-purple-900 font-bold border border-purple-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-id-badge w-4 text-center text-purple-500"></i>
                <span>My Profile</span>
            </a>
            
            <div class="pt-3 border-t border-slate-100 mt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-red-50 transition text-slate-500 hover:text-red-600 font-medium text-xs">
                        <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>
</aside>
