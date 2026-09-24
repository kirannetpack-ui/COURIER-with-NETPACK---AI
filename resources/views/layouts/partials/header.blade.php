<!-- Top Sticky Header with Permanent Static Greeting & Global Quick Tracking -->
<header class="bg-white border-b border-slate-200/80 px-4 sm:px-6 py-3 sticky top-0 z-30 shadow-xs backdrop-blur-md bg-white/95">
    <div class="flex items-center justify-between gap-3">
        <!-- Left: Hamburger (Mobile Only) & Namaste Greeting -->
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <button @click="sidebarOpen = !sidebarOpen" 
                    type="button"
                    class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition focus:outline-none lg:hidden"
                    aria-label="Toggle Navigation">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Permanent Static Greeting -->
            <div class="flex items-center gap-2 sm:gap-2.5 truncate">
                <span class="text-xl sm:text-2xl select-none" role="img" aria-label="Namaste">🙏</span>
                <div class="flex flex-col min-w-0">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight truncate leading-tight">
                        Namaste, <span class="text-teal-700">{{ auth()->user()?->name ?? 'Client' }}</span>!
                    </h2>
                    <p class="text-[11px] font-medium text-slate-500 truncate hidden sm:block">
                        COURIER <span class="italic text-teal-600 font-serif">with</span> NETPACK &bull; Nepal's Premier Logistics
                    </p>
                </div>
            </div>
        </div>

        <!-- Center: Global Quick Tracking Search Form -->
        <form action="{{ route('tracking.search') }}" method="GET" class="hidden md:flex items-center relative max-w-sm w-full mx-2 flex-1">
            <i class="fas fa-search absolute left-3 text-slate-400 text-xs pointer-events-none"></i>
            <input type="text" 
                   name="tracking" 
                   placeholder="Quick HAWB / AWB / Consignment Track..." 
                   required
                   class="w-full pl-9 pr-16 py-1.5 text-xs bg-slate-50 hover:bg-slate-100/90 focus:bg-white border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition">
            <button type="submit" 
                    class="absolute right-1 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-teal-600 hover:bg-teal-700 text-white rounded transition shadow-xs">
                Scan
            </button>
        </form>

        <!-- Right: Timezone, Role Badge, Notifications & Profile -->
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            <!-- Nepal Standard Time -->
            <div class="hidden lg:flex items-center gap-1.5 text-[11px] font-medium text-slate-600 bg-slate-100/80 px-2.5 py-1 rounded-full border border-slate-200/60">
                <span class="text-xs">🇳🇵</span>
                <span>Kathmandu (UTC+5:45)</span>
            </div>

            <!-- Role Badge -->
            @auth
                @php
                    $roleLabel = auth()->user()->user_type_label ?? 'Client';
                    if (auth()->user()->isCustomer() || auth()->user()->user_type === 'customer' || auth()->user()->user_type === 'client') {
                        $roleLabel = 'Client';
                    }
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-50 text-teal-800 border border-teal-200">
                    {{ $roleLabel }}
                </span>
            @endauth

            <!-- Tracking Link (Mobile) -->
            <a href="{{ route('tracking.page') }}" 
               class="md:hidden p-2 text-slate-500 hover:text-teal-600 hover:bg-slate-100 rounded-lg transition"
               title="Track Consignment">
                <i class="fas fa-search-location text-base"></i>
            </a>

            <!-- AI Copilot Quick Launch Button -->
            <button type="button"
                    onclick="document.querySelector('#netpack-ai-copilot button[aria-label=\'Open AI Logistics Copilot\']')?.click()"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-bold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 transition shadow-2xs group focus:outline-none"
                    title="Launch AI Voice & Chat Assistant">
                <span class="w-2 h-2 rounded-full bg-teal-500 group-hover:animate-ping"></span>
                <i class="fas fa-robot text-teal-600"></i>
                <span class="hidden sm:inline">AI Copilot</span>
            </button>

            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" 
               class="relative p-2 text-slate-500 hover:text-teal-600 hover:bg-slate-100 rounded-lg transition"
               title="Notifications">
                <i class="fas fa-bell text-base"></i>
                @php
                    $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                @endphp
                @if($unreadCount > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                @endif
            </a>

            <!-- Profile / Avatar or Sign In Link -->
            @auth
                <a href="{{ route('profile') }}" 
                   class="flex items-center gap-2 p-1 pl-1.5 pr-2 rounded-full hover:bg-slate-100 border border-slate-200 transition"
                   title="View Profile">
                    <div class="w-7 h-7 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold text-xs">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="text-xs font-semibold text-slate-700 hidden sm:inline-block max-w-[100px] truncate">
                        {{ auth()->user()->name }}
                    </span>
                </a>
            @else
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 transition">
                    <i class="fas fa-sign-in-alt text-[10px]"></i>
                    <span>Sign In</span>
                </a>
            @endauth
        </div>
    </div>
</header>
