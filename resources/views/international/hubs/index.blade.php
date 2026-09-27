@extends('layouts.app')

@section('title', 'International Hubs | International Logistics')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-full opacity-10 pointer-events-none flex items-center justify-end pr-8">
            <i class="fas fa-network-wired text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        <i class="fas fa-globe-americas mr-1"></i> International Gateways & Transit Hubs
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Active Logistics Network
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">International Hubs</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Consolidated management of international gateway hubs, intermediate transit points, airport handlers, customs clearance modes (DDP/DDU), and regional delivery routing.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('international.hubs.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Add New International Hub
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Hubs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        @forelse($hubs as $hub)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden group">
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/60 flex items-center justify-center font-black text-indigo-600 dark:text-indigo-400 text-lg shadow-inner">
                            {{ $hub->code }}
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-1.5">
                                @if($hub->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactive</span>
                                @endif
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $hub->mode_type ?? 'HYBRID' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    {{ $hub->hub_type === 'transit_point' ? 'Transit Point' : ($hub->hub_type === 'sorting_center' ? 'Sorting Center' : ($hub->hub_type === 'delivery_hub' ? 'Delivery Hub' : 'Main Gateway')) }}
                                </span>
                                @if($hub->is_mandatory)
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="Mandatory Hub">
                                        Mandatory
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 transition">
                        {{ $hub->name }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1">
                        <i class="fas fa-map-marker-alt text-rose-500"></i> {{ $hub->city ?? 'Gateway City' }}, {{ $hub->country ?? 'Global' }}
                    </p>

                    <div class="space-y-2 py-2.5 border-t border-b border-slate-100 dark:border-slate-800 text-xs">
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span>Airport / Terminal:</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300 truncate max-w-[170px]">{{ $hub->airport_name ?? 'Primary Airport' }}</span>
                        </div>
                    </div>

                    <!-- Multi-Partner Network -->
                    @php
                        $partners = $hub->partners->isNotEmpty() ? $hub->partners : $hub->agencies;
                    @endphp
                    <div class="py-2.5 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="fas fa-handshake text-cyan-600 dark:text-cyan-400"></i> Partner Network
                            </span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-cyan-50 dark:bg-cyan-950/70 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                                {{ $partners->count() }} Partner{{ $partners->count() === 1 ? '' : 's' }}
                            </span>
                        </div>
                        @if($partners->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($partners as $partner)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-800 dark:text-cyan-200 px-2 py-0.5 rounded border border-cyan-200 dark:border-cyan-800/60" title="{{ $partner->name }} ({{ $partner->code }})">
                                        <i class="fas fa-building text-[9px] opacity-60"></i> {{ \Illuminate\Support\Str::limit($partner->name, 22) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[11px] text-slate-400 italic">No partner agencies attached</p>
                        @endif
                    </div>

                    <!-- Main Delivery Areas (Pre-defined Direct Doorstep) -->
                    @php
                        $mainDelivery = !empty($hub->main_delivery_countries) ? $hub->main_delivery_countries : [];
                        $transit = !empty($hub->transit_countries) ? $hub->transit_countries : [];
                        // Fallback to coverage_countries if new json columns are empty
                        if (empty($mainDelivery) && empty($transit) && !empty($hub->coverage_countries)) {
                            $mainDelivery = array_slice((array)$hub->coverage_countries, 0, 3);
                            $transit = array_slice((array)$hub->coverage_countries, 3);
                        }
                    @endphp
                    <div class="mt-2.5">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                                <i class="fas fa-truck-fast text-[11px]"></i> Main Delivery Areas
                            </p>
                            <span class="text-[10px] font-bold text-emerald-600 font-mono">{{ count($mainDelivery) }}</span>
                        </div>
                        @if(!empty($mainDelivery))
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($mainDelivery, 0, 4) as $country)
                                    <span class="text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                        {{ $country }}
                                    </span>
                                @endforeach
                                @if(count($mainDelivery) > 4)
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-900/40 px-1.5 py-0.5 rounded">
                                        +{{ count($mainDelivery) - 4 }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="text-[10px] text-slate-400 italic">No direct delivery areas set</p>
                        @endif
                    </div>

                    <!-- Transit Services (Pre-defined Regional Forwarding / Cross-dock) -->
                    <div class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-1">
                                <i class="fas fa-plane-departure text-[10px]"></i> Transit Services
                            </p>
                            <span class="text-[10px] font-bold text-indigo-600 font-mono">{{ count($transit) }}</span>
                        </div>
                        @if(!empty($transit))
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($transit, 0, 4) as $country)
                                    <span class="text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 px-1.5 py-0.5 rounded border border-indigo-200 dark:border-indigo-800">
                                        {{ $country }}
                                    </span>
                                @endforeach
                                @if(count($transit) > 4)
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-100 dark:bg-indigo-900/40 px-1.5 py-0.5 rounded">
                                        +{{ count($transit) - 4 }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="text-[10px] text-slate-400 italic">No transit routing countries set</p>
                        @endif
                    </div>

                    @if(!empty($hub->service_routes))
                        <div class="mt-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Corridors & Routes</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice((array)$hub->service_routes, 0, 3) as $route)
                                    <span class="text-[9px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                                        {{ $route }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-1.5">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.international-rates.create', ['hub_id' => $hub->id]) }}" 
                           class="text-[11px] font-bold text-teal-700 dark:text-teal-400 bg-teal-100 dark:bg-teal-950/70 hover:bg-teal-200 px-2 py-1 rounded-lg border border-teal-300 dark:border-teal-800 transition flex items-center gap-1"
                           title="Enter Rates for this Hub's Countries">
                            <i class="fas fa-plus"></i> Rate
                        </a>
                        <a href="{{ route('admin.international-rates.index', ['hub_id' => $hub->id]) }}" 
                           class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center gap-1"
                           title="View All Rates Configured for this Hub">
                            <i class="fas fa-table-cells"></i> Rates
                        </a>
                    </div>
                    <div class="flex items-center gap-1">
                        <form method="POST" action="{{ route('international.hubs.toggle', $hub->id) }}" class="inline" onsubmit="return confirm('{{ $hub->is_active ? 'Deactivate' : 'Activate' }} this International Hub?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="{{ $hub->is_active ? 'Deactivate Hub' : 'Activate Hub' }}">
                                <i class="fas {{ $hub->is_active ? 'fa-toggle-on text-emerald-500' : 'fa-toggle-off text-slate-400' }}"></i>
                            </button>
                        </form>
                        <a href="{{ route('international.hubs.edit', $hub->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Edit Hub Configuration">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('international.hubs.destroy', $hub->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Hub? All related routing will be affected.');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Delete Hub">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center">
                <i class="fas fa-plane-slash text-4xl text-slate-400 mb-3"></i>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">No International Hubs Configured</h3>
                <p class="text-sm text-slate-500 mt-1">Get started by creating default regional hubs like Dubai, UK, Australia, and New Zealand.</p>
                <div class="mt-4">
                    <a href="{{ route('international.hubs.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold">
                        <i class="fas fa-plus"></i> Add Initial Hub
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
