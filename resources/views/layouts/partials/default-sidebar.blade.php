<!-- Default Sidebar (Nordic Light Theme) -->
<aside class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] lg:w-64 lg:static lg:inset-auto h-screen bg-white text-slate-800 border-r border-slate-200/90 flex-shrink-0 overflow-y-auto custom-scrollbar select-none shadow-2xl lg:shadow-none transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0 lg:ml-0' : '-translate-x-full lg:-ml-64'">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div class="flex flex-col gap-1.5">
            <x-logo variant="dark" size="sm" :href="route('dashboard')" />
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200/70 w-fit tracking-wider uppercase">
                <i class="fas fa-cube text-[9px] text-teal-600"></i> NetPack Logistics
            </span>
        </div>
        <button @click="sidebarOpen = false" 
                type="button" 
                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg lg:hidden"
                title="Close Sidebar">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    
    <nav class="p-3 space-y-1.5 text-xs">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-home w-4 text-center {{ request()->routeIs('dashboard') ? 'text-teal-600' : 'text-slate-400' }}"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('profile') ? 'bg-teal-50 text-teal-900 font-bold border border-teal-200/80 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
            <i class="fas fa-user w-4 text-center {{ request()->routeIs('profile') ? 'text-teal-600' : 'text-slate-400' }}"></i>
            <span>My Profile</span>
        </a>
    </nav>
</aside>