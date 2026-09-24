<!-- International Service & Gateway Hubs Sidebar -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-800 flex flex-col gap-2">
        <x-logo variant="white" size="sm" :href="route('international.dashboard')" />
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30 w-fit tracking-wider uppercase">
            <i class="fas fa-plane-departure text-[9px] text-sky-400"></i> International Air Cargo
        </span>
    </div>
    
    <!-- User Info Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-sky-600 to-indigo-600 rounded-xl flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                <i class="fas fa-earth-americas text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ auth()->user()->name ?? 'International Admin' }}</p>
                <p class="text-[11px] text-sky-300/80 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">Hub Gateways</span>
            <span class="font-bold text-sky-300">DXB • LHR • SYD • AKL</span>
        </div>
    </div>

    <!-- Quick Manifest & Scan Actions -->
    <div class="px-3 py-1.5 space-y-1.5">
        <a href="{{ route('international.manifests.create') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-xs shadow-md shadow-sky-900/30 transition transform hover:-translate-y-0.5">
            <i class="fas fa-plus-circle text-sm"></i>
            <span>Build Flight Manifest</span>
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- Dashboard Overview -->
        <a href="{{ route('international.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('international.dashboard') ? 'bg-sky-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-4 text-center"></i>
            <span>Operations Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('ai.assistant*') ? 'bg-sky-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-robot w-4 text-center text-teal-300"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                Voice AI
            </span>
        </a>

        <!-- ============================================== -->
        <!-- AIR CARGO & GATEWAY HUBS -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-sky-400 font-extrabold uppercase tracking-widest px-3 mb-1">International Hubs & Delivery</p>
            
            <!-- International Hubs -->
            <a href="{{ route('international.hubs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.hubs*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-network-wired w-4 text-center text-indigo-400"></i>
                <span>International Hubs</span>
                <span class="ml-auto bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\OverseasHub::count() }}
                </span>
            </a>

            <!-- Last Mile Carriers -->
            <a href="{{ route('international.last-mile.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.last-mile*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-truck-moving w-4 text-center text-emerald-400"></i>
                <span>Last-Mile Handover</span>
                <span class="ml-auto bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\LastMileCarrier::count() }}
                </span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- MAWB POOL & MANIFESTS -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-sky-400 font-extrabold uppercase tracking-widest px-3 mb-1">Flight Operations</p>
            
            <!-- MAWB Pool -->
            <a href="{{ route('international.mawbs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.mawbs*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-barcode w-4 text-center text-sky-400"></i>
                <span>MAWB Pool Registry</span>
                <span class="ml-auto bg-sky-500/20 text-sky-300 border border-sky-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\MAWB::unused()->count() }} Unused
                </span>
            </a>

            <!-- Outbound Flight Manifests -->
            <a href="{{ route('international.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.manifests*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-file-invoice-dollar w-4 text-center text-teal-400"></i>
                <span>Flight Manifests</span>
                <span class="ml-auto bg-teal-500/20 text-teal-300 border border-teal-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\Manifest::countInternational() }}
                </span>
            </a>

            <!-- Agency Inbound Desk -->
            <a href="{{ route('agency.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('agency.manifests*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-inbox w-4 text-center text-emerald-400"></i>
                <span>Agency Inbound Desk</span>
            </a>

            <!-- Box QR Scan Desk -->
            <a href="{{ route('agency.scan') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('agency.scan*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-qrcode w-4 text-center text-amber-400"></i>
                <span>Box QR Telemetry Scan</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- SHIPMENTS & RADAR -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-sky-400 font-extrabold uppercase tracking-widest px-3 mb-1">Air Freight Consignments</p>
            
            <!-- All Shipments -->
            <a href="{{ route('international.shipments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.shipments') && !request()->routeIs('international.shipments.create') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-indigo-400"></i>
                <span>Air Freight Shipments</span>
                <span class="ml-auto bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\Shipment::whereNotNull('overseas_partner_id')->count() }}
                </span>
            </a>

            <!-- New Consignment -->
            <a href="{{ route('international.shipments.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.shipments.create') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-plus w-4 text-center text-sky-400"></i>
                <span>Create Air Consignment</span>
            </a>

            <!-- Live Radar Tracking Link -->
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-sky-300 group">
                <i class="fas fa-satellite-dish w-4 text-center text-sky-400 group-hover:scale-110 transition"></i>
                <span class="font-medium">Global Radar Search</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] opacity-60"></i>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- PARTNERS & RATES -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-sky-400 font-extrabold uppercase tracking-widest px-3 mb-1">Partners & Tariffs</p>

            <!-- Rate Inquiry Calculator Desk -->
            <a href="{{ route('rates.inquiry') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('rates.inquiry*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-calculator w-4 text-center text-amber-400"></i>
                <span>Rate Inquiry Desk</span>
            </a>

            <!-- International Sector Tariff Matrices (0.5kg Slabs & Tiers) -->
            <a href="{{ route('admin.international-rates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ (request()->routeIs('admin.international-rates*') && !request()->routeIs('admin.international-rates.settings*')) || request()->routeIs('international.rates-matrix*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-table-cells w-4 text-center text-sky-300"></i>
                <span>Tariff Matrices (0.5kg Slabs)</span>
            </a>

            <!-- Dynamic Tariff Settings & Packaging -->
            <a href="{{ route('admin.international-rates.settings') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.international-rates.settings*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-sliders w-4 text-center text-amber-300"></i>
                <span>Dynamic Tariff & Packaging</span>
            </a>

            <!-- International Rates -->
            <a href="{{ route('international.rates') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.rates*') && !request()->routeIs('international.rates-matrix*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-money-bill-wave w-4 text-center text-emerald-400"></i>
                <span>Legacy Sector Rates</span>
            </a>

            <!-- Remote Surcharges -->
            <a href="{{ route('international.surcharges') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.surcharges*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-receipt w-4 text-center text-rose-400"></i>
                <span>Remote Surcharges</span>
            </a>



            <!-- Operations Staff -->
            <a href="{{ route('international.staff.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('international.staff*') ? 'bg-sky-600/30 text-sky-200 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-users-gear w-4 text-center text-indigo-400"></i>
                <span>Operations Staff</span>
                <span class="ml-auto bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\User::where('user_type', 'staff')->where('service_scope', 'international')->count() }}
                </span>
            </a>
        </div>

        <!-- System & Logout -->
        <div class="pt-3 border-t border-slate-800 mt-3">
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('profile*') ? 'bg-sky-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-id-badge w-4 text-center"></i>
                <span>Admin Profile</span>
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
