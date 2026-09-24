<!-- Compact Seller Sidebar -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-40 transition-transform duration-200 fixed lg:static top-0 left-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-800 flex flex-col gap-2">
        <x-logo variant="white" size="sm" :href="route('seller.dashboard')" />
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 w-fit tracking-wider uppercase">
            <i class="fas fa-store text-[9px] text-emerald-400"></i> Merchant Portal
        </span>
    </div>
    
    <!-- User / Store Info Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-emerald-600 to-teal-500 rounded-xl flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                <i class="fas fa-shop text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-white truncate">{{ auth()->user()->business_name ?? auth()->user()->name ?? 'Merchant Store' }}</p>
                <p class="text-[11px] text-emerald-300/80 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        @php
            $sellerWallet = \App\Models\Wallet::where('user_id', auth()->id())->first();
        @endphp
        <div class="mt-2 pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">Available Wallet</span>
            <span class="font-bold font-mono text-emerald-400">Rs. {{ number_format($sellerWallet->balance ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- Quick Booking Action Button -->
    <div class="px-3 py-1.5">
        <a href="{{ route('seller.orders.create') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md shadow-emerald-900/30 transition transform hover:-translate-y-0.5">
            <i class="fas fa-plus-circle text-sm"></i>
            <span>Book New Shipment</span>
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- 1. Dashboard -->
        <a href="{{ route('seller.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('seller.dashboard') ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-4 text-center"></i>
            <span>Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('ai.assistant*') ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-robot w-4 text-center text-teal-400"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                Voice
            </span>
        </a>

        <!-- 2. Rate Calculator & Inquiry -->
        <a href="{{ route('rates.inquiry') }}" target="_blank" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-emerald-300 group">
            <i class="fas fa-calculator w-4 text-center text-amber-400 group-hover:scale-110 transition"></i>
            <span>Rate Calculator</span>
            <i class="fas fa-external-link-alt ml-auto text-[10px] opacity-60"></i>
        </a>

        <!-- 3. Book Shipment (Rider, Domestic, International) -->
        <a href="{{ route('seller.orders.create') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('seller.orders.create') || request()->routeIs('seller.shipments.create') ? 'bg-emerald-600/30 text-emerald-200 border border-emerald-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-truck-fast w-4 text-center text-emerald-400"></i>
            <span>Book Shipment</span>
            <span class="ml-auto bg-emerald-500/20 text-emerald-300 text-[10px] font-bold px-1.5 py-0.5 rounded">
                3 Tiers
            </span>
        </a>

        <!-- 3b. E-Commerce Direct & Multi-Leg Dispatch -->
        <a href="{{ route('seller.ecommerce.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('seller.ecommerce*') ? 'bg-emerald-600/30 text-emerald-200 border border-emerald-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-boxes-packing w-4 text-center text-teal-400"></i>
            <span>E-Commerce Dispatch</span>
            <span class="ml-auto bg-teal-500/20 text-teal-300 text-[10px] font-mono px-1.5 py-0.5 rounded">
                Direct
            </span>
        </a>

        <!-- 4. Orders & Shipments Registry -->
        <a href="{{ route('seller.orders') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('seller.orders') && !request()->routeIs('seller.orders.create') ? 'bg-emerald-600/30 text-emerald-200 border border-emerald-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-boxes-stacked w-4 text-center text-sky-400"></i>
            <span>Orders & History</span>
            @php
                $pendingSellerOrders = \App\Models\Order::where('seller_id', auth()->id())->where('status', 'pending')->count();
            @endphp
            @if($pendingSellerOrders > 0)
                <span class="ml-auto bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                    {{ $pendingSellerOrders }}
                </span>
            @endif
        </a>

        <!-- 5. Wallet & COD Settlements -->
        <a href="{{ route('seller.wallet') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('seller.wallet*') || request()->routeIs('seller.withdraw*') || request()->routeIs('seller.earnings*') ? 'bg-emerald-600/30 text-emerald-200 border border-emerald-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-wallet w-4 text-center text-teal-400"></i>
            <span>Wallet & COD</span>
        </a>

        <!-- 6. Store Settings / Profile -->
        <a href="{{ route('seller.settings') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('seller.settings*') ? 'bg-emerald-600/30 text-emerald-200 border border-emerald-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-gear w-4 text-center text-slate-400"></i>
            <span>Store Settings & Payout</span>
        </a>

        <!-- Live GPS Radar Link -->
        <div class="pt-2 border-t border-slate-800/80 mt-2">
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-300 hover:bg-slate-800 hover:text-emerald-300 group">
                <i class="fas fa-satellite-dish w-4 text-center text-emerald-400 group-hover:scale-110 transition"></i>
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