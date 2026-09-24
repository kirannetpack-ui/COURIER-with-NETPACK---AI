<!-- Domestic Admin & Operations Staff Sidebar -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-800 flex flex-col gap-2">
        <x-logo variant="white" size="sm" :href="route('domestic.dashboard')" />
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 w-fit tracking-wider uppercase">
            <i class="fas fa-truck-fast text-[9px] text-teal-400"></i> Domestic Operations
        </span>
    </div>
    
    <!-- User Info Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-teal-600 to-emerald-500 rounded-xl flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                <i class="fas fa-route text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ auth()->user()->name ?? 'Domestic Admin' }}</p>
                <p class="text-[11px] text-teal-300/80 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        <div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">7 Provinces Network</span>
            <span class="font-bold text-teal-300">Nepal-Wide</span>
        </div>
    </div>

    <!-- Quick Scan Desk Action -->
    <div class="px-3 py-1.5 space-y-1.5">
        <a href="{{ route('domestic.manifests.scan') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white font-bold text-xs shadow-md shadow-teal-900/30 transition transform hover:-translate-y-0.5">
            <i class="fas fa-barcode text-sm"></i>
            <span>Nepal Scan Desk (Barcode/QR)</span>
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- Dashboard Overview -->
        <a href="{{ route('domestic.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('domestic.dashboard') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-4 text-center"></i>
            <span>Operations Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('ai.assistant*') ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-robot w-4 text-center text-emerald-400"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                Voice AI
            </span>
        </a>

        <!-- ============================================== -->
        <!-- MANIFESTS & SORTATION -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-teal-400 font-extrabold uppercase tracking-widest px-3 mb-1">Manifests & Sortation</p>
            
            <!-- All Manifests -->
            <a href="{{ route('domestic.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.manifests.index') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-teal-400"></i>
                <span>Regional Manifests</span>
                <span class="ml-auto bg-teal-500/20 text-teal-300 border border-teal-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\Manifest::countDomestic() }}
                </span>
            </a>

            <!-- Create Manifest -->
            <a href="{{ route('domestic.manifests.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.manifests.create') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-plus-circle w-4 text-center text-emerald-400"></i>
                <span>Create New Manifest</span>
            </a>

            <!-- Scan Desk -->
            <a href="{{ route('domestic.manifests.scan') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.manifests.scan') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-qrcode w-4 text-center text-amber-400"></i>
                <span>Bag & Consignment Scanner</span>
            </a>

            <!-- Proof of Delivery (POD) -->
            <a href="{{ route('domestic.manifests.pods') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.manifests.pods*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-file-signature w-4 text-center text-purple-400"></i>
                <span>Proof of Delivery (POD)</span>
                <span class="ml-auto bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\ProofOfDelivery::count() }}
                </span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- SHIPMENTS & PICKUPS -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-teal-400 font-extrabold uppercase tracking-widest px-3 mb-1">Domestic Freight</p>
            
            <!-- Shipments -->
            <a href="{{ route('domestic.shipments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.shipments*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-truck-ramp-box w-4 text-center text-blue-400"></i>
                <span>Domestic Shipments</span>
                <span class="ml-auto bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\DomesticShipment::count() }}
                </span>
            </a>

            <!-- Pickup Requests -->
            <a href="{{ route('domestic.pickups') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.pickups*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-dolly w-4 text-center text-amber-400"></i>
                <span>Pickup Requests</span>
                @php
                    $pendingPickupsCount = \App\Models\PickupRequest::where('status', 'pending')->count();
                @endphp
                @if($pendingPickupsCount > 0)
                    <span class="ml-auto bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                        {{ $pendingPickupsCount }} Pending
                    </span>
                @endif
            </a>

            <!-- Live Radar Tracking Link -->
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-teal-300 group">
                <i class="fas fa-satellite-dish w-4 text-center text-teal-400 group-hover:scale-110 transition"></i>
                <span class="font-medium">Live Radar Map</span>
                <i class="fas fa-external-link-alt ml-auto text-[10px] opacity-60"></i>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- PARTNERS & COVERAGE -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-teal-400 font-extrabold uppercase tracking-widest px-3 mb-1">Partners & Network</p>
            
            <!-- Partners -->
            <a href="{{ route('domestic.partners') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.partners*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-handshake w-4 text-center text-indigo-400"></i>
                <span>Domestic Partners</span>
                <span class="ml-auto bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\User::where('user_type', 'partner')->count() }}
                </span>
            </a>

            <!-- Delivery Zones -->
            <a href="{{ route('domestic.zones') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.zones*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-map-location-dot w-4 text-center text-sky-400"></i>
                <span>Delivery Zones</span>
            </a>

            <!-- Domestic Rates -->
            <a href="{{ route('domestic.rates') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.rates*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-money-bill-wave w-4 text-center text-emerald-400"></i>
                <span>Domestic Rates</span>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- E-COMMERCE & FLEET HUB -->
        <!-- ============================================== -->
        <div class="pt-2">
            <p class="text-[10px] text-teal-400 font-extrabold uppercase tracking-widest px-3 mb-1">E-Commerce Management</p>
            
            <!-- Sellers -->
            <a href="{{ route('domestic.sellers') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.sellers*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-store w-4 text-center text-pink-400"></i>
                <span>Merchant Sellers</span>
                <span class="ml-auto bg-pink-500/20 text-pink-300 border border-pink-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\User::where('user_type', 'seller')->count() }}
                </span>
            </a>

            <!-- E-Commerce Direct Rider Network -->
            <a href="{{ route('domestic.ecommerce.riders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.ecommerce.riders*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-motorcycle w-4 text-center text-teal-400"></i>
                <span>Direct Rider Network</span>
                @php
                    $pendingRidersCount = \App\Models\RiderProfile::where('verification_status', 'pending')->count();
                @endphp
                @if($pendingRidersCount > 0)
                    <span class="ml-auto bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded animate-pulse">
                        {{ $pendingRidersCount }} KYC
                    </span>
                @else
                    <span class="ml-auto bg-teal-500/20 text-teal-300 text-[10px] font-mono px-1.5 py-0.5 rounded">
                        {{ \App\Models\RiderProfile::count() }}
                    </span>
                @endif
            </a>

            <!-- Rider COD Ledgers & Deposits -->
            <a href="{{ route('domestic.ecommerce.riders.cod') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.ecommerce.riders.cod*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-hand-holding-dollar w-4 text-center text-emerald-400"></i>
                <span>Rider COD Ledgers</span>
            </a>

            <!-- Orders -->
            <a href="{{ route('domestic.orders') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.orders*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-shopping-cart w-4 text-center text-amber-400"></i>
                <span>E-Commerce Orders</span>
                <span class="ml-auto bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\Order::count() }}
                </span>
            </a>

            <!-- Reports -->
            <a href="{{ route('domestic.reports') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.reports*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-file-chart-column w-4 text-center text-cyan-400"></i>
                <span>Domestic Reports</span>
            </a>

            <!-- Operations Staff -->
            <a href="{{ route('domestic.staff.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('domestic.staff*') ? 'bg-teal-600/30 text-teal-200 border border-teal-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-users-gear w-4 text-center text-teal-400"></i>
                <span>Operations Staff</span>
                <span class="ml-auto bg-teal-500/20 text-teal-300 border border-teal-500/30 text-[10px] font-mono px-1.5 py-0.5 rounded">
                    {{ \App\Models\User::where('user_type', 'staff')->whereIn('service_scope', ['domestic', 'ecommerce'])->count() }}
                </span>
            </a>
        </div>

        <!-- System & Logout -->
        <div class="pt-3 border-t border-slate-800 mt-3">
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('profile*') ? 'bg-teal-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
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