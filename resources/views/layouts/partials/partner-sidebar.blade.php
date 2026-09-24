<!-- Partner Sidebar -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-800 flex flex-col gap-2">
        <x-logo variant="white" size="sm" :href="route('partner.dashboard')" />
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 w-fit tracking-wider uppercase">
            <i class="fas fa-handshake text-[9px] text-amber-400"></i> Partner Network
        </span>
    </div>
    
    <!-- Partner Info Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-amber-600 to-orange-500 rounded-xl flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                <i class="fas fa-building-user text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ auth()->user()->company_name ?? auth()->user()->name ?? 'Domestic Partner' }}</p>
                <p class="text-[11px] text-amber-300/80 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">Hub Service Status</span>
            <span class="font-bold text-emerald-400">Active</span>
        </div>
    </div>

    <!-- Quick Scan & Inbound Action -->
    <div class="px-3 py-1.5 space-y-1.5">
        <a href="{{ route('partner.scan') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs shadow-md shadow-amber-900/30 transition transform hover:-translate-y-0.5">
            <i class="fas fa-qrcode text-sm"></i>
            <span>Scan Consignment QR</span>
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- Dashboard Overview -->
        <a href="{{ route('partner.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('partner.dashboard') ? 'bg-amber-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-4 text-center"></i>
            <span>Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('ai.assistant*') ? 'bg-amber-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-robot w-4 text-center text-teal-300"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                Voice AI
            </span>
        </a>

        <!-- ============================================== -->
        <!-- DELIVERIES & ATTENTION -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-amber-400 font-extrabold uppercase tracking-widest px-3 mb-1">Deliveries & SLAs</p>
            
            <!-- All Deliveries -->
            <a href="{{ route('partner.deliveries.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('partner.deliveries.index') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-truck-fast w-4 text-center text-sky-400"></i>
                <span>Assigned Deliveries</span>
                @php
                    $pendingDeliveriesCount = \App\Models\PickupRequest::where('partner_id', auth()->id())->where('status', 'pending')->count();
                @endphp
                @if($pendingDeliveriesCount > 0)
                    <span class="ml-auto bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                        {{ $pendingDeliveriesCount }}
                    </span>
                @endif
            </a>

            <!-- Attention Needed (SLA Reminders) -->
            <a href="{{ route('partner.deliveries.attention') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('partner.deliveries.attention*') ? 'bg-rose-600/30 text-rose-200 border border-rose-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-triangle-exclamation w-4 text-center text-rose-400"></i>
                <span>Attention Needed</span>
                @php
                    $attentionCount = \App\Models\PickupRequest::where('partner_id', auth()->id())->where('is_delayed', true)->where('status', '!=', 'delivered')->count();
                @endphp
                @if($attentionCount > 0)
                    <span class="ml-auto bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded animate-pulse">
                        {{ $attentionCount }} Alert
                    </span>
                @endif
            </a>

            <!-- QR Scanner -->
            <a href="{{ route('partner.scan') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('partner.scan') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-barcode w-4 text-center text-emerald-400"></i>
                <span>Barcode & QR Scan Desk</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- MANIFESTS & PROOF OF DELIVERY -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-amber-400 font-extrabold uppercase tracking-widest px-3 mb-1">Manifests & PODs</p>
            
            <!-- Inbound Manifests -->
            <a href="{{ route('domestic.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.manifests.index') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-teal-400"></i>
                <span>Regional Manifests</span>
            </a>

            <!-- Proof of Delivery (POD) -->
            <a href="{{ route('domestic.manifests.pods') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.manifests.pods*') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-file-signature w-4 text-center text-purple-400"></i>
                <span>Proof of Delivery (POD)</span>
                <span class="ml-auto bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\ProofOfDelivery::count() }}
                </span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- COVERAGE & RATES -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-amber-400 font-extrabold uppercase tracking-widest px-3 mb-1">Coverage & Rates</p>

            <a href="{{ route('partner.shipment-legs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('partner.shipment-legs*') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><i class="fas fa-route w-4 text-center text-amber-300"></i><span>Assigned Route Legs</span></a>
            
            <!-- Delivery Zones -->
            <a href="{{ route('partner.zones.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('partner.zones*') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-map-location-dot w-4 text-center text-blue-400"></i>
                <span>Delivery Zones</span>
                @php
                    $zoneCount = \App\Models\DeliveryZone::where('partner_user_id', auth()->id())->count();
                @endphp
                @if($zoneCount > 0)
                    <span class="ml-auto bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                        {{ $zoneCount }}
                    </span>
                @endif
            </a>

            <!-- Service Rates -->
            <a href="{{ route('partner.rates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('partner.rates*') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-money-bill-wave w-4 text-center text-emerald-400"></i>
                <span>Partner Rates</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- STAFF & REPORTS -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-amber-400 font-extrabold uppercase tracking-widest px-3 mb-1">Staff & Analytics</p>
            
            <!-- Staff Members -->
            <a href="{{ route('partner.staff.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('partner.staff*') ? 'bg-amber-600/30 text-amber-200 border border-amber-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-users w-4 text-center text-indigo-400"></i>
                <span>Staff Members</span>
            </a>

            <!-- Export Report -->
            <a href="{{ route('partner.deliveries.export') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-white">
                <i class="fas fa-file-export w-4 text-center text-sky-400"></i>
                <span>Export Deliveries (CSV)</span>
            </a>

            <!-- Live Radar Tracking Link -->
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-amber-300 group">
                <i class="fas fa-satellite-dish w-4 text-center text-amber-400 group-hover:scale-110 transition"></i>
                <span class="font-medium">Live Radar Map</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] opacity-60"></i>
            </a>
        </div>

        <!-- System & Logout -->
        <div class="pt-3 border-t border-slate-800 mt-3">
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('profile*') ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-id-badge w-4 text-center"></i>
                <span>Partner Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="block pt-1">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-500/10 transition text-slate-400 hover:text-red-400 font-medium">
                    <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
