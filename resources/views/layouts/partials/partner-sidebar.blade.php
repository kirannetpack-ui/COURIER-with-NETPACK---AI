<!-- Partner Sidebar (Nordic Light Theme) -->
<aside class="w-64 bg-white text-slate-800 border-r border-slate-200/90 flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0 shadow-xs"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-100 flex flex-col gap-2 bg-slate-50/50">
        <x-logo variant="dark" size="sm" :href="route('partner.dashboard')" />
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80 w-fit tracking-wider uppercase">
            <i class="fas fa-handshake text-[9px] text-amber-600"></i> Partner Network
        </span>
    </div>
    
    <!-- Partner Info Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-50 border border-slate-200/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                <i class="fas fa-building-user text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-slate-900 truncate">{{ auth()->user()->company_name ?? auth()->user()->name ?? 'Domestic Partner' }}</p>
                <p class="text-[11px] text-amber-800 font-medium truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
            <span class="text-slate-500 font-medium">Hub Service Status</span>
            <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 text-[10px]">Active</span>
        </div>
    </div>

    <!-- Quick Scan & Inbound Action -->
    <div class="px-3 py-1.5 space-y-1.5">
        <a href="{{ route('partner.scan') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
            <i class="fas fa-qrcode text-sm"></i>
            <span>Scan Consignment QR</span>
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- Dashboard Overview -->
        <a href="{{ route('partner.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('partner.dashboard') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('partner.dashboard') ? 'text-amber-600' : 'text-slate-400' }}"></i>
            <span>Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ai.assistant*') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-robot w-4 text-center text-teal-600"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-100/70 text-teal-800 border border-teal-200">
                Voice AI
            </span>
        </a>

        <!-- ============================================== -->
        <!-- DELIVERIES & ATTENTION -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">Deliveries & SLAs</p>
            
            <!-- All Deliveries -->
            <a href="{{ route('partner.deliveries.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('partner.deliveries.index') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-truck-fast w-4 text-center text-sky-600"></i>
                <span>Assigned Deliveries</span>
                @php
                    $pendingDeliveriesCount = \App\Models\PickupRequest::where('partner_id', auth()->id())->where('status', 'pending')->count();
                @endphp
                @if($pendingDeliveriesCount > 0)
                    <span class="ml-auto bg-amber-100 text-amber-800 border border-amber-300 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                        {{ $pendingDeliveriesCount }}
                    </span>
                @endif
            </a>

            <!-- Attention Needed (SLA Reminders) -->
            <a href="{{ route('partner.deliveries.attention') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('partner.deliveries.attention*') ? 'bg-rose-50 text-rose-900 font-bold border border-rose-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-triangle-exclamation w-4 text-center text-rose-500"></i>
                <span>Attention Needed</span>
                @php
                    $attentionCount = \App\Models\PickupRequest::where('partner_id', auth()->id())->where('is_delayed', true)->where('status', '!=', 'delivered')->count();
                @endphp
                @if($attentionCount > 0)
                    <span class="ml-auto bg-rose-100 text-rose-800 border border-rose-300 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded animate-pulse">
                        {{ $attentionCount }} Alert
                    </span>
                @endif
            </a>

            <!-- QR Scanner -->
            <a href="{{ route('partner.scan') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('partner.scan') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-barcode w-4 text-center text-emerald-600"></i>
                <span>Barcode & QR Scan Desk</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- MANIFESTS & PROOF OF DELIVERY -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">Manifests & PODs</p>
            
            <!-- Inbound Manifests -->
            <a href="{{ route('domestic.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('domestic.manifests.index') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-teal-600"></i>
                <span>Regional Manifests</span>
            </a>

            <!-- Proof of Delivery (POD) -->
            <a href="{{ route('domestic.manifests.pods') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('domestic.manifests.pods*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-file-signature w-4 text-center text-purple-600"></i>
                <span>Proof of Delivery (POD)</span>
                <span class="ml-auto bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\ProofOfDelivery::count() }}
                </span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- COVERAGE & RATES -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">Coverage & Rates</p>

            <a href="{{ route('partner.shipment-legs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('partner.shipment-legs*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-route w-4 text-center text-amber-600"></i>
                <span>Assigned Route Legs</span>
            </a>
            
            <!-- Delivery Zones -->
            <a href="{{ route('partner.zones.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('partner.zones*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-map-location-dot w-4 text-center text-blue-600"></i>
                <span>Delivery Zones</span>
                @php
                    $zoneCount = \App\Models\DeliveryZone::where('partner_user_id', auth()->id())->count();
                @endphp
                @if($zoneCount > 0)
                    <span class="ml-auto bg-blue-50 text-blue-800 border border-blue-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                        {{ $zoneCount }}
                    </span>
                @endif
            </a>

            <!-- Service Rates -->
            <a href="{{ route('partner.rates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('partner.rates*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-money-bill-wave w-4 text-center text-emerald-600"></i>
                <span>Partner Rates</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- STAFF & REPORTS -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest px-3 mb-1">Staff & Analytics</p>
            
            <!-- Staff Members -->
            <a href="{{ route('partner.staff.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('partner.staff*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-users w-4 text-center text-indigo-600"></i>
                <span>Staff Members</span>
            </a>

            <!-- Export Report -->
            <a href="{{ route('partner.deliveries.export') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium">
                <i class="fas fa-file-export w-4 text-center text-sky-600"></i>
                <span>Export Deliveries (CSV)</span>
            </a>

            <!-- Live Radar Tracking Link -->
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-amber-700 group font-medium">
                <i class="fas fa-satellite-dish w-4 text-center text-amber-600 group-hover:scale-110 transition"></i>
                <span>Live Radar Map</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] text-slate-400"></i>
            </a>
        </div>

        <!-- System & Logout -->
        <div class="pt-3 border-t border-slate-100 mt-3">
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('profile*') ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200/80' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-id-badge w-4 text-center text-slate-500"></i>
                <span>Partner Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="block pt-1">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-red-50 transition text-slate-500 hover:text-red-600 font-medium">
                    <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
