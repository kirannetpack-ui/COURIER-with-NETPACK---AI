<!-- Compact Seller Sidebar (Nordic Light Theme) -->
<aside class="bg-white text-slate-800 border-r border-slate-200/90 flex-shrink-0 h-screen overflow-y-auto sticky top-0 custom-scrollbar select-none z-50 fixed lg:static top-0 left-0 shadow-lg lg:shadow-none transition-all duration-300 ease-in-out w-72 max-w-[85vw] lg:w-64"
       :class="sidebarOpen ? 'translate-x-0 lg:ml-0' : '-translate-x-full lg:-ml-64'">
    <!-- Brand Header -->
    <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-2 bg-slate-50/50">
        <div class="flex flex-col gap-1.5">
            <x-logo variant="dark" size="sm" :href="route('seller.dashboard')" />
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80 w-fit tracking-wider uppercase">
                <i class="fas fa-store text-[9px] text-emerald-600"></i> Merchant Portal
            </span>
        </div>
        <button @click="sidebarOpen = false" 
                type="button" 
                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg lg:hidden"
                title="Close Sidebar">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    
    <!-- User / Store Info Card -->
    <div class="p-3 mx-3 my-2 rounded-xl bg-slate-50 border border-slate-200/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                <i class="fas fa-shop text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-xs text-slate-900 truncate">{{ auth()->user()->business_name ?? auth()->user()->name ?? 'Merchant Store' }}</p>
                <p class="text-[11px] text-emerald-700 font-medium truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        @php
            $sellerWallet = \App\Models\Wallet::where('user_id', auth()->id())->first();
        @endphp
        <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
            <span class="text-slate-500 font-medium">Available Wallet</span>
            <span class="font-bold font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Rs. {{ number_format($sellerWallet->balance ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- Quick Booking Action Button -->
    <div class="px-3 py-1.5">
        <a href="{{ route('seller.orders.create') }}" 
           class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
            <i class="fas fa-plus-circle text-sm"></i>
            <span>Book New Shipment</span>
        </a>
    </div>
    
    <nav class="p-3 space-y-1 text-xs">
        <!-- 1. Dashboard -->
        <a href="{{ route('seller.dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('seller.dashboard') ? 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('seller.dashboard') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
            <span>Dashboard</span>
        </a>

        <!-- AI Logistics Copilot & Voice -->
        <a href="{{ route('ai.assistant') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ai.assistant*') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-robot w-4 text-center text-teal-600"></i>
            <span>AI Logistics Copilot</span>
            <span class="ml-auto text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-100/70 text-teal-800 border border-teal-200">
                Voice
            </span>
        </a>

        <!-- 2. Rate Calculator & Inquiry -->
        <a href="{{ route('rates.inquiry') }}" target="_blank" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-slate-900 group font-medium">
            <i class="fas fa-calculator w-4 text-center text-amber-500"></i>
            <span>Rate Calculator</span>
            <i class="fas fa-external-link-alt ml-auto text-[10px] text-slate-400"></i>
        </a>

        <!-- 3. Book Shipment (Rider, Domestic, International) -->
        <a href="{{ route('seller.orders.create') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('seller.orders.create') || request()->routeIs('seller.shipments.create') ? 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-truck-fast w-4 text-center text-emerald-600"></i>
            <span>Book Shipment</span>
            <span class="ml-auto bg-emerald-100/80 text-emerald-800 text-[10px] font-bold px-1.5 py-0.5 rounded border border-emerald-200">
                3 Tiers
            </span>
        </a>

        <!-- 3b. E-Commerce Direct & Multi-Leg Dispatch -->
        <a href="{{ route('seller.ecommerce.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('seller.ecommerce*') ? 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-boxes-packing w-4 text-center text-teal-600"></i>
            <span>E-Commerce Dispatch</span>
            <span class="ml-auto bg-teal-100/80 text-teal-800 text-[10px] font-mono px-1.5 py-0.5 rounded border border-teal-200">
                Direct
            </span>
        </a>

        <!-- 4. Orders & Shipments Registry -->
        <a href="{{ route('seller.orders') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('seller.orders') && !request()->routeIs('seller.orders.create') ? 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-boxes-stacked w-4 text-center text-sky-600"></i>
            <span>Orders & History</span>
            @php
                $pendingSellerOrders = \App\Models\Order::where('seller_id', auth()->id())->where('status', 'pending')->count();
            @endphp
            @if($pendingSellerOrders > 0)
                <span class="ml-auto bg-amber-100 text-amber-800 border border-amber-300 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">
                    {{ $pendingSellerOrders }}
                </span>
            @endif
        </a>

        <!-- 5. Wallet & COD Settlements -->
        <a href="{{ route('seller.wallet') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('seller.wallet*') || request()->routeIs('seller.withdraw*') || request()->routeIs('seller.earnings*') ? 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-wallet w-4 text-center text-teal-600"></i>
            <span>Wallet & COD</span>
        </a>

        <!-- 6. Store Settings / Profile -->
        <a href="{{ route('seller.settings') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('seller.settings*') ? 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-gear w-4 text-center text-slate-400"></i>
            <span>Store Settings & Payout</span>
        </a>

        <!-- Live GPS Radar Link -->
        <div class="pt-2 border-t border-slate-100 mt-2">
            <a href="{{ route('tracking.page') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-xl transition text-slate-600 hover:bg-slate-50 hover:text-emerald-700 group font-medium">
                <i class="fas fa-satellite-dish w-4 text-center text-emerald-600 group-hover:scale-110 transition"></i>
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