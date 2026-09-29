<!-- International Service & Gateway Hubs Sidebar (Nordic Light Theme) -->
<aside class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] lg:w-64 lg:static lg:inset-auto h-screen bg-white text-slate-800 border-r border-slate-200/90 flex-shrink-0 overflow-y-auto custom-scrollbar select-none shadow-2xl lg:shadow-none transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0 lg:ml-0' : '-translate-x-full lg:-ml-64'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-2 bg-slate-50/50">
        <div class="flex flex-col gap-1.5">
            <x-logo variant="dark" size="sm" :href="route('international.dashboard')" />
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/70 w-fit tracking-wider uppercase">
                <i class="fas fa-plane-departure text-[9px] text-sky-600"></i> International Air Cargo
            </span>
        </div>
        <button @click="sidebarOpen = false" 
                type="button" 
                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg lg:hidden"
                title="Close Sidebar">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    
    <!-- User Info Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-50 border border-slate-200/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-sky-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                <i class="fas fa-earth-americas text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-slate-900 truncate">{{ auth()->user()->name ?? 'International Admin' }}</p>
                <p class="text-[11px] text-sky-700 font-medium truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
            <span class="text-slate-500 font-medium">Hub Gateways</span>
            <span class="font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200/80">DXB • LHR • SYD • AKL</span>
        </div>
    </div>

    <!-- Quick Manifest & Scan Actions -->
    <div class="px-3 py-1.5 space-y-1.5">
        <a href="{{ route('international.manifests.create') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs transition">
            <i class="fas fa-plus-circle text-sm"></i>
            <span>Build Flight Manifest</span>
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- Dashboard Overview -->
        <a href="{{ route('international.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('international.dashboard') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('international.dashboard') ? 'text-sky-600' : 'text-slate-400' }}"></i>
            <span>Operations Dashboard</span>
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
        <!-- AIR CARGO & GATEWAY HUBS -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">International Hubs & Delivery</p>
            
            <!-- International Hubs -->
            <a href="{{ route('international.hubs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.hubs*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-network-wired w-4 text-center text-indigo-500"></i>
                <span>International Hubs</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\OverseasHub::count() }}
                </span>
            </a>

            <!-- Partner Agencies -->
            <a href="{{ route('international.agencies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.agencies*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-handshake w-4 text-center text-cyan-600"></i>
                <span>Partner Agencies</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\Agency::count() }}
                </span>
            </a>

            <!-- Last Mile Carriers -->
            <a href="{{ route('international.last-mile.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.last-mile*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-truck-moving w-4 text-center text-emerald-500"></i>
                <span>Last-Mile Handover</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\LastMileCarrier::count() }}
                </span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- MAWB POOL & MANIFESTS -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">Flight Operations</p>
            
            <!-- MAWB Pool -->
            <a href="{{ route('international.mawbs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.mawbs*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-barcode w-4 text-center text-sky-600"></i>
                <span>MAWB Pool Registry</span>
                <span class="ml-auto bg-sky-50 text-sky-700 border border-sky-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\MAWB::unused()->count() }} Unused
                </span>
            </a>

            <!-- Outbound Flight Manifests -->
            <a href="{{ route('international.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.manifests*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-file-invoice-dollar w-4 text-center text-teal-600"></i>
                <span>Flight Manifests</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\Manifest::countInternational() }}
                </span>
            </a>

            <!-- Agency Inbound Desk -->
            <a href="{{ route('agency.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('agency.manifests*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-inbox w-4 text-center text-emerald-500"></i>
                <span>Agency Inbound Desk</span>
            </a>

            <!-- Box QR Scan Desk -->
            <a href="{{ route('agency.scan') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('agency.scan*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-qrcode w-4 text-center text-amber-500"></i>
                <span>Box QR Telemetry Scan</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- SHIPMENTS & RADAR -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">Air Freight Consignments</p>
            
            <!-- All Shipments -->
            <a href="{{ route('international.shipments') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.shipments') && !request()->routeIs('international.shipments.create') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-indigo-500"></i>
                <span>Air Freight Shipments</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\Shipment::whereNotNull('overseas_partner_id')->count() }}
                </span>
            </a>

            <!-- New Consignment -->
            <a href="{{ route('international.shipments.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.shipments.create') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-plus w-4 text-center text-sky-600"></i>
                <span>Create Air Consignment</span>
            </a>

            <!-- Live Radar Tracking Link -->
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium group">
                <i class="fas fa-satellite-dish w-4 text-center text-sky-600 group-hover:scale-110 transition"></i>
                <span>Global Radar Search</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] text-slate-400"></i>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- PARTNERS & RATES -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest px-3 mb-1">Partners & Tariffs</p>

            <!-- Rate Inquiry Calculator Desk -->
            <a href="{{ route('rates.inquiry') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('rates.inquiry*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-calculator w-4 text-center text-amber-500"></i>
                <span>Rate Inquiry Desk</span>
            </a>

            <!-- International Sector Tariff Matrices (0.5kg Slabs & Tiers) -->
            <a href="{{ route('admin.international-rates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ (request()->routeIs('admin.international-rates*') && !request()->routeIs('admin.international-rates.settings*')) || request()->routeIs('international.rates-matrix*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-table-cells w-4 text-center text-sky-600"></i>
                <span>Tariff Matrices (0.5kg Slabs)</span>
            </a>

            <!-- Dynamic Tariff Settings & Packaging -->
            <a href="{{ route('admin.international-rates.settings') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.international-rates.settings*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-sliders w-4 text-center text-amber-500"></i>
                <span>Dynamic Tariff & Packaging</span>
            </a>

            <!-- International Rates -->
            <a href="{{ route('international.rates') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.rates*') && !request()->routeIs('international.rates-matrix*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-money-bill-wave w-4 text-center text-emerald-500"></i>
                <span>Legacy Sector Rates</span>
            </a>

            <!-- Remote Surcharges -->
            <a href="{{ route('international.surcharges') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.surcharges*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-receipt w-4 text-center text-rose-500"></i>
                <span>Remote Surcharges</span>
            </a>

            <!-- Operations Staff -->
            <a href="{{ route('international.staff.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('international.staff*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-users-gear w-4 text-center text-indigo-500"></i>
                <span>Operations Staff</span>
                <span class="ml-auto bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\User::where('user_type', 'staff')->where('service_scope', 'international')->count() }}
                </span>
            </a>
        </div>

        <!-- System & Logout -->
        <div class="pt-3 border-t border-slate-100 mt-3">
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('profile*') ? 'bg-sky-50 text-sky-900 font-bold border border-sky-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                <i class="fas fa-id-badge w-4 text-center text-sky-600"></i>
                <span>Admin Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="block pt-1">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-red-50 transition text-slate-500 hover:text-red-600 font-medium text-xs">
                    <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
