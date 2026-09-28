<!-- Default Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] lg:w-64 lg:static lg:inset-auto h-screen bg-slate-900 text-white flex-shrink-0 overflow-y-auto custom-scrollbar select-none shadow-2xl lg:shadow-none transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0 lg:ml-0' : '-translate-x-full lg:-ml-64'">
    <div class="p-4 border-b border-gray-700 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-teal-400">NetPack</h2>
            <p class="text-xs text-gray-400 mt-1">Welcome</p>
        </div>
        <button @click="sidebarOpen = false" 
                type="button" 
                class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg lg:hidden"
                title="Close Sidebar">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    
    <nav class="p-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-teal-400' : 'text-gray-300' }}">
            <i class="fas fa-home w-5"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition {{ request()->routeIs('profile') ? 'bg-gray-800 text-teal-400' : 'text-gray-300' }}">
            <i class="fas fa-user w-5"></i>
            <span>My Profile</span>
        </a>
    </nav>
</aside>