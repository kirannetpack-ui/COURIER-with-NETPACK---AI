@extends('layouts.app')

@section('title', 'International Air Freight & Hubs Command - COURIER with NETPACK')
@section('page-title', 'International Air Cargo Command')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header with Global Hub Status -->
    <div class="relative overflow-hidden rounded-2xl bg-white p-6 sm:p-7 text-slate-800 border border-slate-200/90 shadow-xs">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/70 uppercase tracking-widest">
                        <i class="fas fa-plane-departure text-[9px] mr-1 text-sky-600"></i> Global Freight Command
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                        <i class="fas fa-network-wired text-[9px] mr-1 text-sky-600"></i> 4 Gateway Hubs Active
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                    International Air Cargo & Hub Gateway
                </h1>
                <p class="text-xs text-slate-600 max-w-xl">
                    Express 3-4 days air courier from Nepal (DHL, UPS, FedEx, SF) and agency-based economy hubs connecting Dubai, UK/Europe, Australia, and New Zealand.
                </p>
            </div>

            <!-- Fast Action CTAs -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('international.manifests.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs uppercase tracking-wider shadow-xs transition">
                    <i class="fas fa-file-invoice-dollar text-sm"></i>
                    <span>Build Flight Manifest</span>
                </a>
                <a href="{{ route('agency.manifests.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 transition">
                    <i class="fas fa-inbox text-sm text-emerald-600"></i>
                    <span>Agency Inbound</span>
                </a>
                <a href="{{ route('tracking.page') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 transition">
                    <i class="fas fa-satellite-dish text-sm text-sky-600"></i>
                    <span>Radar Map</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Core Freight Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- International Shipments -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-sky-500/40 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Air Freight Cargo</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_shipments'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 mt-2 text-[11px]">
                        <span class="text-sky-600 font-bold">{{ $stats['in_transit_shipments'] ?? 0 }} In-Flight</span> •
                        <span class="text-emerald-600 font-bold">{{ $stats['delivered_shipments'] ?? 0 }} Handed Over</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-plane-departure"></i>
                </div>
            </div>
        </div>

        <!-- MAWB Pool -->
        <a href="{{ route('international.mawbs.index') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-indigo-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">MAWB Stock Pool</p>
                    <p class="text-2xl sm:text-3xl font-black text-indigo-600 mt-1">{{ \App\Models\MAWB::unused()->count() }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-indigo-600 transition">
                        {{ \App\Models\MAWB::count() }} Total Master Waybills &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-barcode"></i>
                </div>
            </div>
        </a>

        <!-- Flight Manifests -->
        <a href="{{ route('international.manifests.index') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-teal-500/40 hover:shadow-md transition block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Flight Manifests</p>
                    <p class="text-2xl sm:text-3xl font-black text-teal-600 mt-1">{{ \App\Models\Manifest::countInternational() }}</p>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium group-hover:text-teal-600 transition">
                        Agency Datasheets & Email &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </a>

        <!-- Overseas Partners & Agencies -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-amber-500/40 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Agencies & Couriers</p>
                    <p class="text-2xl sm:text-3xl font-black text-amber-600 mt-1">{{ \App\Models\Agency::count() }}</p>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">
                        <span class="text-emerald-600 font-bold">{{ \App\Models\LastMileCarrier::count() }} Last-Mile</span> Handover Carriers
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Default Gateway Hubs Overview Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fas fa-network-wired text-sky-600"></i> International Hubs (Global Gateways)
                </h3>
                <p class="text-xs text-slate-500">Global linehaul routes, destination customs clearance, and last-mile carrier handovers</p>
            </div>
            <a href="{{ route('international.hubs.index') }}" class="text-xs font-bold text-sky-600 hover:underline">
                Manage International Hubs &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- DUBAI HUB -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-black text-sm text-sky-400">DUBAI (DXB)</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">Middle East & Global</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Gulf countries linehaul, UPS Worldwide Crossing & Direct Canada DDP route to Toronto.
                </p>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Last-Mile:</span>
                    <span class="font-bold text-emerald-400">Canpar • Obibox • Local</span>
                </div>
            </div>

            <!-- UK HUB -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-black text-sm text-indigo-400">UNITED KINGDOM (LHR)</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Europe & Americas</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    UK & Europe handled under DDP mode; USA & Canada handled under DDU mode.
                </p>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Customs Mode:</span>
                    <span class="font-bold text-indigo-300">DDP / DDU Routing</span>
                </div>
            </div>

            <!-- AUSTRALIA HUB -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-black text-sm text-emerald-400">AUSTRALIA (SYD)</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Oceania</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Handling nationwide Australia consignments with localized customs sortation.
                </p>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Last-Mile:</span>
                    <span class="font-bold text-emerald-400">AusPost • StarTrack</span>
                </div>
            </div>

            <!-- NEW ZEALAND HUB -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-black text-sm text-teal-400">NEW ZEALAND (AKL)</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">Pacific</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Auckland gateway handling North and South Island commercial and personal freight.
                </p>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Last-Mile:</span>
                    <span class="font-bold text-teal-300">NZ Post • CourierPost</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Operations Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('international.hubs.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-sky-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-network-wired"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">International Hubs</p>
                <p class="text-xs text-slate-400">Add, edit & routes</p>
            </div>
        </a>

        <a href="{{ route('international.mawbs.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-barcode"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">MAWB Pool</p>
                <p class="text-xs text-slate-400">Airline master numbers</p>
            </div>
        </a>

        <a href="{{ route('agency.manifests.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-inbox"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Agency Desk</p>
                <p class="text-xs text-slate-400">Inbound arrival notice</p>
            </div>
        </a>

        <a href="{{ route('agency.scan') }}" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-amber-500 hover:shadow-sm transition flex items-center gap-4 group">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                <i class="fas fa-qrcode"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm">Box QR Scanner</p>
                <p class="text-xs text-slate-400">Instant flight telemetry</p>
            </div>
        </a>
    </div>

    <!-- Recent International Shipments Registry -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Recent International Consignments</h3>
                <p class="text-xs text-slate-500">Live air freight telemetry, overseas destination, and partner routing</p>
            </div>
            <a href="{{ route('international.shipments') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 hover:underline">
                View All Shipments &rarr;
            </a>
        </div>

        <div class="p-4">
            @php
                $recentIntlShipments = \App\Models\Shipment::with(['customer', 'overseasPartner'])
                    ->whereNotNull('overseas_partner_id')
                    ->latest()
                    ->take(8)
                    ->get();
            @endphp

            @if($recentIntlShipments->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($recentIntlShipments as $shipment)
                        <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 rounded-xl px-3 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center font-bold text-xs">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-xs text-slate-900">{{ $shipment->tracking_number }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $shipment->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                               ($shipment->status === 'in_transit' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-slate-100 text-slate-700') }}">
                                            {{ str_replace('_', ' ', $shipment->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $shipment->receiver_country ?? 'International' }} • Consignee: {{ $shipment->receiver_name ?? 'Consignee' }}
                                        @if($shipment->overseasPartner)
                                            • Gateway: {{ $shipment->overseasPartner->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 sm:text-right">
                                <a href="{{ route('tracking.show', $shipment->tracking_number) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold transition">
                                    Radar
                                </a>
                                <a href="{{ route('hawb.print', ['id' => $shipment->id, 'type' => 'international']) }}" target="_blank"
                                   class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                    HAWB
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-400">
                    <i class="fas fa-plane text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs">No international air cargo shipments recorded yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection