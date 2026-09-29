@extends('layouts.app')

@section('title', 'Domestic Logistics Command Tower - COURIER with NETPACK')
@section('page-title', 'Domestic Logistics Command Tower')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner with Nationwide Hub Status -->
    <div class="relative overflow-hidden rounded-2xl bg-white p-6 sm:p-7 text-slate-800 border border-slate-200/90 shadow-xs">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200/70 uppercase tracking-widest">
                        <i class="fas fa-truck-fast text-[9px] mr-1 text-teal-600"></i> Nepal Provincial Network
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                        <i class="fas fa-circle text-[8px] mr-1 text-emerald-500 animate-pulse"></i> 7 Provinces Operational
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                    Domestic & Fleet Command Tower
                </h1>
                <p class="text-xs text-slate-600 max-w-xl">
                    Nationwide sortation manifests, regional arrival notice verification, barcode & QR scan desks, and last-mile partner SLA delivery dispatch.
                </p>
            </div>

            <!-- Command Action CTAs -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('domestic.manifests.scan') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider shadow-xs transition">
                    <i class="fas fa-barcode text-sm"></i>
                    <span>Nepal Scan Desk</span>
                </a>
                <a href="{{ route('domestic.manifests.create') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 transition">
                    <i class="fas fa-plus-circle text-sm text-teal-600"></i>
                    <span>Create Manifest</span>
                </a>
                <a href="{{ route('tracking.page') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 transition">
                    <i class="fas fa-satellite-dish text-sm text-teal-600"></i>
                    <span>Radar Map</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core Operational Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Domestic Shipments -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-teal-500/40 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Domestic Shipments</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($domesticStats['total_domestic_shipments'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 mt-2 text-[11px]">
                        <span class="text-amber-600 font-bold">{{ $domesticStats['pending_domestic_shipments'] ?? 0 }} Pending</span> •
                        <span class="text-emerald-600 font-bold">{{ $domesticStats['delivered_domestic_shipments'] ?? 0 }} Delivered</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
            </div>
        </div>

        <!-- Regional Manifests -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-blue-500/40 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sortation Manifests</p>
                    <p class="text-2xl sm:text-3xl font-black text-blue-600 mt-1">{{ number_format(\App\Models\Manifest::countDomestic()) }}</p>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">
                        <a href="{{ route('domestic.manifests.index') }}" class="text-blue-600 hover:underline font-bold">
                            Arrival Notice Desks &rarr;
                        </a>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </div>

        <!-- Pending Pickups -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-amber-500/40 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pickup Requests</p>
                    <p class="text-2xl sm:text-3xl font-black text-amber-600 mt-1">{{ number_format($domesticStats['total_pickups'] ?? 0) }}</p>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-200">
                            {{ $domesticStats['pending_pickups'] ?? 0 }} Needs Rider
                        </span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-dolly"></i>
                </div>
            </div>
        </div>

        <!-- Domestic Partners & Network -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-purple-500/40 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hub Partners</p>
                    <p class="text-2xl sm:text-3xl font-black text-purple-600 mt-1">{{ number_format($domesticStats['total_partners'] ?? 0) }}</p>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">
                        <span class="text-emerald-600 font-bold">{{ $domesticStats['active_partners'] ?? 0 }} Verified</span> across {{ $domesticStats['total_zones'] ?? 0 }} zones
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-handshake"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 7 Provinces Regional Gateway Hubs Status Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-teal-600"></i> Nepal 7 Provinces Sortation Hubs
                </h3>
                <p class="text-xs text-slate-500">Live gateway connectivity, provincial linehauls, and forward transshipments</p>
            </div>
            <a href="{{ route('domestic.manifests.index') }}" class="text-xs font-bold text-teal-600 hover:underline">
                Regional Manifests Desk &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 text-center">
            <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-200/80">
                <p class="text-[10px] uppercase font-extrabold text-teal-700 tracking-wider">Bagmati</p>
                <p class="font-bold text-xs text-slate-900 mt-1">Kathmandu Hub</p>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-teal-200/70 text-teal-900 mt-1">Central Gateway</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[10px] uppercase font-extrabold text-slate-500 tracking-wider">Gandaki</p>
                <p class="font-bold text-xs text-slate-900 mt-1">Pokhara Depot</p>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 mt-1">Operational</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[10px] uppercase font-extrabold text-slate-500 tracking-wider">Koshi</p>
                <p class="font-bold text-xs text-slate-900 mt-1">Biratnagar / Itahari</p>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 mt-1">Operational</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[10px] uppercase font-extrabold text-slate-500 tracking-wider">Madhesh</p>
                <p class="font-bold text-xs text-slate-900 mt-1">Birgunj Gateway</p>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 mt-1">Operational</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[10px] uppercase font-extrabold text-slate-500 tracking-wider">Lumbini</p>
                <p class="font-bold text-xs text-slate-900 mt-1">Butwal / Nepalgunj</p>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 mt-1">Operational</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[10px] uppercase font-extrabold text-slate-500 tracking-wider">Sudurpashchim</p>
                <p class="font-bold text-xs text-slate-900 mt-1">Dhangadhi Depot</p>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 mt-1">Operational</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[10px] uppercase font-extrabold text-slate-500 tracking-wider">Karnali</p>
                <p class="font-bold text-xs text-slate-900 mt-1">Surkhet Depot</p>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 mt-1">Operational</span>
            </div>
        </div>
    </div>

    <!-- Operations Quick Actions Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('domestic.manifests.scan') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-teal-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-barcode"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Nepal Scan Desk</p>
                <p class="text-xs text-slate-400">Bag & QR check-in</p>
            </div>
        </a>

        <a href="{{ route('domestic.manifests.create') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">New Manifest</p>
                <p class="text-xs text-slate-400">Route & bag dispatch</p>
            </div>
        </a>

        <a href="{{ route('domestic.partners') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-purple-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-handshake"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Hub Partners</p>
                <p class="text-xs text-slate-400">Manage depot network</p>
            </div>
        </a>

        <a href="{{ route('domestic.manifests.pods') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-file-signature"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">POD Registry</p>
                <p class="text-xs text-slate-400">Signed proof audit</p>
            </div>
        </a>
    </div>

    <!-- Recent Domestic Shipments Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Recent Domestic Consignments</h3>
                    <p class="text-xs text-slate-500">Live status, sender, and destination hub routing</p>
                </div>
                <a href="{{ route('domestic.shipments') }}" class="text-xs font-bold text-teal-600 hover:text-teal-700 hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="p-4">
                @if(isset($recentDomesticShipments) && $recentDomesticShipments->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($recentDomesticShipments as $shipment)
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 rounded-xl px-3 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">
                                        <i class="fas fa-truck-fast"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-xs text-slate-900">{{ $shipment->tracking_number }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $shipment->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                                   ($shipment->status === 'in_transit' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700') }}">
                                                {{ str_replace('_', ' ', $shipment->status) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ $shipment->origin_city ?? 'Origin' }} &rarr; <strong>{{ $shipment->destination_city ?? $shipment->destination ?? 'Destination' }}</strong>
                                            @if($shipment->partner)
                                                • Via Partner: {{ $shipment->partner->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 sm:text-right">
                                    <a href="{{ route('domestic.shipments.show', $shipment->id) }}" 
                                       class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-700 text-xs font-bold transition">
                                        Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-slate-400">
                        <i class="fas fa-inbox text-3xl mb-2 text-slate-300"></i>
                        <p class="text-xs">No domestic shipments recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Recent Pickups & E-Commerce Link -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-bold text-slate-900 text-sm">Active Pickup Requests</h4>
                    <a href="{{ route('domestic.pickups') }}" class="text-xs text-teal-600 font-bold hover:underline">All &rarr;</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentPickups ?? [] as $pickup)
                        <div class="flex items-center justify-between text-xs py-2 border-b border-slate-100 last:border-0">
                            <div>
                                <p class="font-bold text-slate-800">#{{ $pickup->id }} • {{ $pickup->pickup_location ?? 'Location' }}</p>
                                <p class="text-[10px] text-slate-400">{{ $pickup->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $pickup->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $pickup->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-3 text-center">No pending pickups.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection