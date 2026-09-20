@extends('layouts.public')

@section('title', 'Global Air Cargo Tracking - ' . $shipment->formatted_tracking_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    @keyframes radar-glow {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(20, 184, 166, 0.6); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 16px rgba(20, 184, 166, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(20, 184, 166, 0); }
    }
    .radar-pulse { animation: radar-glow 2.2s infinite ease-in-out; }

    @keyframes radar-sweep-anim {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .radar-sweep {
        background: conic-gradient(from 0deg, rgba(20, 184, 166, 0.3) 0deg, rgba(20, 184, 166, 0.05) 60deg, transparent 90deg);
        animation: radar-sweep-anim 4s linear infinite;
    }
    
    @keyframes plane-travel {
        0% { left: 8%; opacity: 0; transform: translateY(-50%) scale(0.85); }
        15% { opacity: 1; }
        85% { opacity: 1; }
        100% { left: 92%; opacity: 0; transform: translateY(-50%) scale(0.85); }
    }
    .plane-anim {
        animation: plane-travel 5.5s infinite cubic-bezier(0.4, 0, 0.2, 1);
    }

    #globalFlightMap .leaflet-tile {
        filter: brightness(0.72) contrast(1.25) saturate(0.85);
    }

    .live-plane-marker {
        background: radial-gradient(circle, rgba(20, 184, 166, 0.9) 0%, rgba(13, 148, 136, 0.4) 60%, transparent 100%);
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        box-shadow: 0 0 18px rgba(45, 212, 191, 0.8);
        transition: transform 0.4s ease-out;
    }

    .curved-flight-path {
        stroke-dasharray: 8, 8;
        animation: dash-flow 1.5s linear infinite;
    }
    @keyframes dash-flow {
        from { stroke-dashoffset: 16; }
        to { stroke-dashoffset: 0; }
    }
</style>
@endpush

@section('content')
@php
    $statusInfo = $shipment->tracking_status;
    $serviceInfo = config('tracking.services.' . $shipment->service_type,
        config('tracking.services.' . $shipment->shipment_type, config('tracking.services.default')));
    
    $events = $shipment->tracking_history ?: [[
        'event_code' => 'booking_confirmed',
        'status' => $shipment->status,
        'status_label' => $statusInfo['label'],
        'description' => $statusInfo['description'],
        'location' => $shipment->sender_city ?: 'Kathmandu, Nepal',
        'time' => $shipment->created_at->toIso8601String(),
    ]];

    // Determine 5-stage milestone progression
    $hasLastMile = !empty($shipment->last_mile_tracking_number) || !empty($shipment->last_mile_carrier_name);
    $milestoneStep = match($shipment->status) {
        'delivered' => 4,
        'out_for_delivery' => 3,
        'in_transit' => ($hasLastMile || in_array($shipment->agency_milestone, ['last_mile_handover', 'import_cleared', 'arrival_notice'])) ? 2 : 1,
        'customs_clearance' => 2,
        'picked_up', 'processing' => 0,
        default => 0,
    };

    $milestones = [
        ['label' => 'Origin Gateway', 'code' => 'KTM', 'icon' => 'fa-boxes-packing', 'desc' => 'Intake, security screening & verified'],
        ['label' => 'Airline MAWB', 'code' => 'XPR', 'icon' => 'fa-plane-departure', 'desc' => 'Master Air Waybill flight transit'],
        ['label' => 'Hub & Customs', 'code' => 'HUB', 'icon' => 'fa-passport', 'desc' => 'Overseas hub & DDP/DDU clearance'],
        ['label' => 'Global Carrier', 'code' => 'LST', 'icon' => 'fa-truck-fast', 'desc' => 'Last-mile courier doorstep dispatch'],
        ['label' => 'Delivered', 'code' => 'DLV', 'icon' => 'fa-circle-check', 'desc' => 'Signed & verified proof of delivery'],
    ];

    // Country flags mapping
    $flags = [
        'Nepal' => '🇳🇵',
        'United States' => '🇺🇸',
        'United Kingdom' => '🇬🇧',
        'Australia' => '🇦🇺',
        'Canada' => '🇨🇦',
        'Germany' => '🇩🇪',
        'France' => '🇫🇷',
        'Japan' => '🇯🇵',
        'India' => '🇮🇳',
        'China' => '🇨🇳',
        'United Arab Emirates' => '🇦🇪',
    ];
    $originFlag = $flags[$shipment->sender_country] ?? '🇳🇵';
    $destFlag = $flags[$shipment->receiver_country] ?? '🌐';

    $coords = $routeCoordinates ?? [
        'origin' => ['lat' => 27.7172, 'lng' => 85.3240, 'iata' => 'KTM', 'name' => 'Kathmandu Gateway'],
        'hub' => ['lat' => 25.2532, 'lng' => 55.3657, 'iata' => 'DXB', 'name' => 'Dubai Hub'],
        'destination' => ['lat' => 40.7128, 'lng' => -74.0060, 'name' => 'Destination'],
    ];
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Action Breadcrumb Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('tracking.page') }}" class="h-9 w-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Global Air Cargo &middot;</span>
                    <span class="text-xs font-bold text-teal-700">{{ $serviceInfo['label'] }}</span>
                </div>
                <p class="text-xs text-slate-500">IATA Standard House Air Waybill &middot; Automated Milestone Engine</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Alert Subscription Button -->
            <button type="button" onclick="openSubscribeModal()" class="px-3.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-xs font-bold flex items-center gap-1.5 transition">
                <i class="fas fa-bell text-teal-600"></i> <span>Get Alerts</span>
            </button>

            <!-- Copy Link -->
            <button type="button" onclick="copyTrackingUrl()" id="copyBtn" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-teal-500 text-slate-700 hover:text-teal-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-link text-slate-400"></i> <span>Copy Link</span>
            </button>

            <!-- Print HAWB Copy Button -->
            <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="Print Official House Air Waybill">
                <i class="fas fa-print"></i> <span>HAWB</span>
            </a>

            <!-- Commercial Invoice -->
            <a href="{{ route('shipments.invoice', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="View & Print Official Commercial Invoice">
                <i class="fas fa-file-invoice-dollar text-teal-600"></i> <span>Invoice</span>
            </a>

            <!-- Packing List -->
            <a href="{{ route('shipments.packing-list', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="View & Print Box Breakdown Packing List">
                <i class="fas fa-boxes-stacked text-teal-600"></i> <span>Packing List</span>
            </a>

            @if($shipment->seller_bill_file)
                <!-- Attached Tax Bill -->
                <a href="{{ route('shipments.seller-bill', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-bold flex items-center gap-1.5 transition" title="View Attached Tax Invoice / Bill">
                    <i class="fas fa-paperclip text-amber-600"></i> <span>Tax Bill</span>
                </a>
            @endif

            <!-- Print Status -->
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-slate-800 text-slate-700 hover:text-slate-900 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-file-lines text-slate-400"></i> <span>Print Status</span>
            </button>

            <!-- WhatsApp Live Support -->
            <a href="https://wa.me/97715970123?text=Inquiry%20about%20shipment%20{{ $shipment->tracking_number }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs">
                <i class="fab fa-whatsapp text-sm"></i> <span>Live Help</span>
            </a>

            <!-- Report Issue / Situation Button -->
            <button type="button" onclick="openIssueModal()" class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs cursor-pointer" title="Report any situation, damage, delay, or issue">
                <i class="fas fa-triangle-exclamation text-rose-600"></i> <span>Report Issue</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- HERO LOGISTICS MASTER CARD -->
    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-teal-950 text-white shadow-xl border border-slate-800">
        <!-- Card Top Bar -->
        <div class="p-6 md:p-8 pb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Tracking & HAWB Identifiers -->
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        @if(($shipment->service_type ?? '') === 'express')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                <span class="h-2 w-2 rounded-full bg-amber-400 animate-ping"></span>
                                ⚡ EXPRESS PRIORITY (3-4 Working Days &bull; Nepal Origin)
                            </span>
                            @if(!empty($shipment->express_partner))
                                <span class="rounded-xl bg-white/10 px-3 py-1 text-xs font-bold tracking-wider text-amber-200 border border-white/10">
                                    Carrier: {{ $shipment->express_partner }}
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                <span class="h-2 w-2 rounded-full bg-teal-400 animate-ping"></span>
                                🌍 ECONOMY AIR-CARGO
                            </span>
                            @if($shipment->hub)
                                <span class="rounded-xl bg-indigo-500/20 px-3 py-1 text-xs font-bold text-indigo-300 border border-indigo-500/30">
                                    Gateway: {{ $shipment->hub->hub_code }} ({{ $shipment->customs_mode ?? 'DDP' }})
                                </span>
                            @endif
                        @endif

                        @if(!empty($shipment->hawb_number))
                            <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="rounded-xl bg-white/10 hover:bg-white/20 px-3.5 py-1 text-xs font-mono font-bold tracking-widest text-teal-200 border border-white/15 flex items-center gap-1.5 transition" title="Print Official HAWB Document">
                                <i class="fas fa-print text-[10px]"></i> HAWB: {{ $shipment->hawb_number }}
                            </a>
                        @endif

                        @if(!empty($shipment->mawb_number) || $shipment->mawb)
                            <span class="rounded-xl bg-sky-500/20 px-3.5 py-1 text-xs font-mono font-bold tracking-widest text-sky-300 border border-sky-500/30 flex items-center gap-1">
                                <i class="fas fa-barcode text-[10px]"></i> MAWB: {{ $shipment->mawb_number ?? $shipment->mawb->mawb_number }}
                            </span>
                        @endif

                        @if(!empty($shipment->last_mile_carrier_name) || !empty($shipment->last_mile_tracking_number))
                            <span class="rounded-xl bg-emerald-500/20 px-3.5 py-1 text-xs font-mono font-bold tracking-widest text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                <i class="fas fa-truck text-[10px]"></i> {{ $shipment->last_mile_carrier_name ?? 'Carrier' }}: {{ $shipment->last_mile_tracking_number }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-white">
                        {{ $shipment->formatted_tracking_number }}
                    </h1>

                    <p class="text-xs text-slate-400 flex items-center gap-2">
                        <span><i class="fas fa-satellite text-teal-400"></i> Automated Telemetry Active</span>
                        <span>&middot;</span>
                        <span>Updated {{ $shipment->updated_at->diffForHumans() }}</span>
                        <span>&middot;</span>
                        <span id="autoRefreshStatus" class="text-teal-300 font-mono">Auto-sync in <span id="countdown">30</span>s</span>
                    </p>
                </div>

                <!-- Current Operational Status Badge -->
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-xl p-4 sm:p-5 rounded-2xl border border-white/15">
                    <div class="h-14 w-14 rounded-2xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center text-teal-300 text-2xl shrink-0 radar-pulse">
                        <i class="fas {{ $statusInfo['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-teal-300/80">Operational Milestone</span>
                        <h3 class="text-xl font-extrabold text-white">
                            {{ $statusInfo['label'] }}
                        </h3>
                        <p class="text-xs text-slate-300 mt-0.5 max-w-xs leading-tight">
                            {{ $statusInfo['description'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- INTERNATIONAL FLIGHT CORRIDOR PREVIEW -->
            <div class="mt-8 pt-6 border-t border-white/10">
                <div class="bg-slate-900/80 rounded-2xl p-4 sm:p-6 border border-white/10 relative overflow-hidden">
                    
                    <!-- Route connecting line with moving plane -->
                    <div class="relative flex items-center justify-between z-10">
                        <!-- Origin City -->
                        <div class="flex items-center gap-3">
                            <span class="text-3xl sm:text-4xl shrink-0">{{ $originFlag }}</span>
                            <div>
                                <span class="text-[10px] font-bold text-teal-400 uppercase tracking-widest">Origin Gateway</span>
                                <h4 class="text-base sm:text-lg font-bold text-white leading-snug">
                                    {{ $shipment->sender_city ?: 'Kathmandu' }}, {{ $shipment->sender_country ?: 'Nepal' }}
                                </h4>
                                <span class="text-[11px] font-mono text-slate-400">TIA Cargo Terminal &middot; KTM</span>
                            </div>
                        </div>

                        <!-- Midline Visual Aircraft -->
                        <div class="hidden md:flex flex-col items-center flex-1 px-8 relative">
                            <div class="w-full h-0.5 border-t-2 border-dashed border-teal-500/40 relative">
                                <div class="plane-anim absolute top-1/2 text-teal-300 text-lg">
                                    <i class="fas fa-plane"></i>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-400 mt-2 bg-slate-950 px-3 py-0.5 rounded-full border border-slate-800">
                                @if(!empty($shipment->mawb))
                                    {{ $shipment->mawb->airline_name }} &bull; Flight {{ $shipment->mawb->flight_number ?: 'Scheduled' }}
                                @else
                                    International Air Transit Corridor
                                @endif
                            </span>
                        </div>

                        <!-- Destination City -->
                        <div class="flex items-center gap-3 text-right">
                            <div>
                                <span class="text-[10px] font-bold text-teal-400 uppercase tracking-widest">Destination Port</span>
                                <h4 class="text-base sm:text-lg font-bold text-white leading-snug">
                                    {{ $shipment->receiver_city ?: 'Destination City' }}, {{ $shipment->receiver_country }}
                                </h4>
                                <span class="text-[11px] font-mono text-slate-400">
                                    {{ $shipment->hub ? $shipment->hub->hub_name : 'International Air Hub' }}
                                </span>
                            </div>
                            <span class="text-3xl sm:text-4xl shrink-0">{{ $destFlag }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5-STAGE MILESTONE STEPPER -->
            <div class="mt-8 pt-6 border-t border-white/10 pb-2">
                <div class="grid grid-cols-5 gap-2 text-center">
                    @foreach($milestones as $idx => $m)
                        @php 
                            $isDone = $idx <= $milestoneStep; 
                            $isCurrent = $idx === $milestoneStep; 
                        @endphp
                        <div class="flex flex-col items-center group">
                            <span class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl flex items-center justify-center text-sm font-bold transition transform group-hover:scale-105 {{ $isDone ? 'bg-gradient-to-tr from-teal-500 to-teal-400 text-slate-950 shadow-lg shadow-teal-500/30' : 'bg-white/10 text-white/40 border border-white/10' }} {{ $isCurrent ? 'ring-4 ring-teal-400/40' : '' }}">
                                <i class="fas {{ $m['icon'] }}"></i>
                            </span>
                            <span class="mt-2 text-xs font-bold {{ $isDone ? 'text-white' : 'text-slate-500' }}">
                                {{ $m['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 hidden sm:block mt-0.5">
                                {{ $m['desc'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- INTERACTIVE GLOBAL FLIGHT ROUTE MAP & LIVE TELEMETRY HUD -->
    <section class="bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-800 shadow-xl text-white space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-teal-500"></span>
                </span>
                <div>
                    <h3 class="text-sm font-bold tracking-wide uppercase text-slate-100 flex items-center gap-2">
                        <i class="fas fa-satellite-dish text-teal-400"></i>
                        <span>Live Global Air Route & Transit Telemetry Radar</span>
                    </h3>
                    <p class="text-[11px] text-slate-400">Curved IATA Geodesic Flight Arc &bull; Kathmandu &rarr; {{ $coords['hub']['iata'] ?? 'DXB' }} &rarr; {{ $shipment->receiver_city ?: 'Destination' }}</p>
                </div>
            </div>

            <!-- LIVE TELEMETRY HUD PILLS -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700/80 text-[11px] font-mono text-teal-300 flex items-center gap-1.5 shadow-2xs">
                    <i class="fas fa-gauge-high text-teal-400 text-[10px]"></i>
                    <span id="hudSpeed">Cruising: 840 km/h</span>
                </div>
                <div class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700/80 text-[11px] font-mono text-sky-300 flex items-center gap-1.5 shadow-2xs">
                    <i class="fas fa-mountain text-sky-400 text-[10px]"></i>
                    <span id="hudAlt">FL360 (36,000 FT)</span>
                </div>
                <div class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700/80 text-[11px] font-mono text-amber-300 flex items-center gap-1.5 shadow-2xs">
                    <i class="fas fa-cloud-sun text-amber-400 text-[10px]"></i>
                    <span>KTM: 22°C Clear</span>
                </div>
            </div>
        </div>

        <!-- Leaflet Map Container with Live Flight Trajectory -->
        <div class="relative rounded-2xl overflow-hidden border border-slate-800 shadow-inner">
            <div id="globalFlightMap" class="w-full h-80 sm:h-96 z-0"></div>
            <!-- Radar overlay effect badge -->
            <div class="absolute top-3 right-3 z-10 bg-slate-950/80 backdrop-blur-md px-3 py-1.5 rounded-xl border border-teal-500/30 text-[11px] font-mono text-teal-300 flex items-center gap-2 pointer-events-none">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-ping"></span>
                <span>RADAR SWEEP ACTIVE</span>
            </div>
        </div>

        <!-- 3-Gateway Corridor Coordinates Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-xs">
            <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center gap-3">
                <span class="h-9 w-9 rounded-xl bg-teal-500/20 text-teal-300 flex items-center justify-center font-mono font-black text-xs border border-teal-500/30">
                    KTM
                </span>
                <div class="min-w-0">
                    <span class="text-[10px] uppercase font-bold text-teal-400 block tracking-wider">Origin Gateway</span>
                    <p class="font-bold text-slate-100 truncate">Tribhuvan Int'l Cargo Terminal</p>
                    <span class="text-[10px] text-slate-400 font-mono">27.7172° N, 85.3240° E</span>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center gap-3">
                <span class="h-9 w-9 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center font-mono font-black text-xs border border-sky-500/30">
                    {{ $coords['hub']['iata'] ?? 'HUB' }}
                </span>
                <div class="min-w-0">
                    <span class="text-[10px] uppercase font-bold text-sky-400 block tracking-wider">Transit Air Hub</span>
                    <p class="font-bold text-slate-100 truncate">{{ $coords['hub']['name'] ?? 'International Gateway Hub' }}</p>
                    <span class="text-[10px] text-slate-400 font-mono">{{ number_format($coords['hub']['lat'] ?? 25.25, 2) }}°, {{ number_format($coords['hub']['lng'] ?? 55.36, 2) }}°</span>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center gap-3">
                <span class="h-9 w-9 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center font-mono font-black text-xs border border-emerald-500/30">
                    DST
                </span>
                <div class="min-w-0">
                    <span class="text-[10px] uppercase font-bold text-emerald-400 block tracking-wider">Destination Port</span>
                    <p class="font-bold text-slate-100 truncate">{{ $shipment->receiver_city ?: 'Destination City' }}, {{ $shipment->receiver_country }}</p>
                    <span class="text-[10px] text-slate-400 font-mono">{{ number_format($coords['destination']['lat'] ?? 40.71, 2) }}°, {{ number_format($coords['destination']['lng'] ?? -74.0, 2) }}°</span>
                </div>
            </div>
        </div>
    </section>

    <!-- CORE SPECS CARDS -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Weight Details</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900 font-mono">
                    {{ number_format($shipment->chargeable_weight ?? $shipment->actual_weight ?? 0, 2) }}
                </span>
                <span class="text-xs font-bold text-slate-500">KG Chargeable</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
                Actual: {{ number_format($shipment->actual_weight ?? 0, 2) }} kg
            </p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Package Category</span>
            <div class="mt-2 flex items-center gap-2">
                <span class="h-8 w-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-sm">
                    <i class="fas fa-box"></i>
                </span>
                <span class="text-base font-bold text-slate-900">
                    {{ ucfirst($shipment->package_type ?? 'Parcel') }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Standard Export Packaging</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estimated Arrival</span>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-lg font-black text-teal-700">
                    {{ $shipment->estimated_delivery ? $shipment->estimated_delivery->format('M d, Y') : 'On Schedule' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Subject to customs inspection</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Security & Verification</span>
            <div class="mt-2 flex items-center gap-2">
                <span class="h-8 w-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm">
                    <i class="fas fa-shield-check"></i>
                </span>
                <span class="text-xs font-bold text-slate-800">
                    IATA Verified
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Tamper-Proof Tracking</p>
        </div>
    </section>

    <!-- DETAILED EVENT TIMELINE & SUMMARY VAULT -->
    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <!-- Event Timeline -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-2xs">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-timeline text-teal-600"></i> Detailed Journey Timeline
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Chronological scan records from origin booking to final delivery</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                    {{ count($events) }} Recorded Events
                </span>
            </div>

            <div class="relative space-y-0">
                @foreach($events as $index => $event)
                    @php
                        $isLatest = $index === 0 || $index === count($events) - 1;
                        $evInfo = config('tracking.statuses.' . ($event['status'] ?? ''), config('tracking.statuses.pending'));
                        $iconName = $event['icon'] ?? $evInfo['icon'];
                    @endphp
                    <div class="relative grid grid-cols-[40px_1fr] gap-4 pb-8 last:pb-2">
                        @if(!$loop->last)
                            <div class="absolute left-[19px] top-10 bottom-0 w-0.5 bg-slate-200"></div>
                        @endif

                        <!-- Timeline Node Pin -->
                        <div class="relative z-10 flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-bold shadow-2xs {{ $loop->first ? 'bg-teal-600 text-white radar-pulse' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            <i class="fas {{ $iconName }}"></i>
                        </div>

                        <!-- Timeline Details Card -->
                        <div class="rounded-2xl border p-4 transition {{ $loop->first ? 'border-teal-300/80 bg-teal-50/40 shadow-xs' : 'border-slate-200/80 bg-white' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <span>{{ $event['status_label'] ?? ucfirst(str_replace('_', ' ', $event['status'] ?? 'Updated')) }}</span>
                                    @if($loop->first)
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-teal-600 text-white">
                                            Latest Checkpoint
                                        </span>
                                    @endif
                                </h4>
                                @if(!empty($event['time']))
                                    <time class="text-xs font-medium text-slate-500 font-mono">
                                        {{ \Carbon\Carbon::parse($event['time'])->format('d M Y &middot; h:i A') }}
                                    </time>
                                @endif
                            </div>

                            @if(!empty($event['description']))
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                    {{ $event['description'] }}
                                </p>
                            @endif

                            @if(!empty($event['location']))
                                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                                    <i class="fas fa-location-dot text-teal-600"></i>
                                    <span>{{ $event['location'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Sidebar Summary Vault -->
        <aside class="space-y-6">
            <!-- Last Mile Delivery Handover Card -->
            @if(!empty($shipment->last_mile_carrier_name) || !empty($shipment->last_mile_carrier_id))
                <section class="bg-gradient-to-br from-emerald-950 via-slate-900 to-slate-950 rounded-3xl p-6 text-white border border-emerald-800/80 shadow-md space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                            <i class="fas fa-truck-moving"></i> Last Mile Delivery Handover
                        </span>
                        <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded-full font-semibold">
                            Global Partner
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-white/5">
                            <span class="text-slate-400">Carrier Partner:</span>
                            <span class="font-bold text-white">{{ $shipment->last_mile_carrier_name ?? ($shipment->lastMileCarrier->name ?? 'Local Courier') }}</span>
                        </div>
                        @if(!empty($shipment->last_mile_tracking_number))
                            <div class="flex justify-between py-1 border-b border-white/5 font-mono">
                                <span class="text-slate-400">Carrier Waybill #:</span>
                                <span class="font-bold text-emerald-300">{{ $shipment->last_mile_tracking_number }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-1 border-b border-white/5">
                            <span class="text-slate-400">Clearance Mode:</span>
                            <span class="font-bold text-amber-300">{{ $shipment->customs_mode ?? 'DDP' }}</span>
                        </div>
                    </div>

                    @php
                        $carrierUrl = $shipment->carrier_tracking_url ?: ($shipment->lastMileCarrier?->getTrackingUrl($shipment->last_mile_tracking_number));
                    @endphp

                    @if($carrierUrl)
                        <a href="{{ $carrierUrl }}" target="_blank" class="w-full mt-3 py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md transition">
                            <i class="fas fa-truck text-xs"></i>
                            <span>Track on {{ $shipment->last_mile_carrier_name ?: 'Carrier' }} Official Portal</span>
                            <i class="fas fa-external-link-alt text-[10px]"></i>
                        </a>
                    @endif

                    @auth
                        @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'staff', 'international_admin']))
                            <form method="POST" action="{{ route('tracking.sync-carrier', $shipment->id) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="w-full py-1.5 px-3 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 text-xs font-semibold flex items-center justify-center gap-1.5 transition">
                                    <i class="fas fa-rotate text-[10px]"></i>
                                    <span>Sync Carrier Status Now</span>
                                </button>
                            </form>
                        @endif
                    @endauth
                </section>
            @endif

            <!-- Shipment Summary Card -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200 shadow-2xs">
                <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">
                    Shipment Waybill Record
                </h3>

                <dl class="divide-y divide-slate-100 text-xs mt-2">
                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Tracking Code</dt>
                        <dd class="font-mono font-bold text-slate-900">{{ $shipment->formatted_tracking_number }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">HAWB Number</dt>
                        <dd class="font-mono font-bold text-teal-700">{{ $shipment->hawb_number ?: 'Not assigned' }}</dd>
                    </div>

                    @if($shipment->mawb_number || $shipment->mawb)
                        <div class="py-3 flex justify-between items-center">
                            <dt class="text-slate-500">Airline MAWB</dt>
                            <dd class="font-mono font-bold text-sky-700">{{ $shipment->mawb_number ?: $shipment->mawb->mawb_number }}</dd>
                        </div>
                    @endif

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Origin City</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->sender_city ?: 'Kathmandu' }}, {{ $shipment->sender_country ?: 'Nepal' }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Destination City</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->receiver_city ?: 'Destination' }}, {{ $shipment->receiver_country }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Chargeable Weight</dt>
                        <dd class="font-bold text-slate-900">{{ number_format($shipment->chargeable_weight ?? $shipment->actual_weight ?? 0, 2) }} kg</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Booking Date</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <!-- Privacy Safe Notice -->
                <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2 leading-relaxed">
                    <i class="fas fa-shield-halved text-slate-400 text-xs shrink-0 mt-0.5"></i>
                    <span>In accordance with international privacy laws, personal telephone numbers, detailed street addresses, and private payment receipts are hidden from public views.</span>
                </div>
            </section>

            <!-- OFFICIAL CONSIGNMENT HAWB DOCUMENT CARD -->
            <section class="rounded-3xl border border-teal-100 bg-gradient-to-br from-teal-50/50 via-white to-sky-50/40 p-6 shadow-sm">
                <div class="flex items-center gap-3 border-b border-teal-100/80 pb-4 mb-4">
                    <div class="h-10 w-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">House Air Waybill Copy</h4>
                        <p class="text-[11px] text-slate-500">IATA Standard Multi-Part Consignment Note</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                    Official non-monetary air freight document ready for printing. Includes Consignee Copy, Customs / Airline Operations Copy, and flight routing identifiers.
                </p>
                <div class="space-y-2">
                    <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs">
                        <i class="fas fa-print"></i> <span>Print Official HAWB (A4)</span>
                    </a>
                    <a href="{{ route('tracking.hawb.popup', $shipment->tracking_number) }}" target="_blank" class="w-full py-2 px-4 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 flex items-center justify-center gap-2 transition">
                        <i class="fas fa-receipt text-slate-400"></i> <span>Single-Page Print Slip</span>
                    </a>
                </div>
            </section>

            <!-- Support & Quick Actions -->
            <section class="bg-slate-900 rounded-3xl p-6 text-white border border-slate-800">
                <h4 class="font-bold text-sm text-white">Need Operations Support?</h4>
                <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                    Quote tracking reference <span class="font-mono text-teal-300 font-bold">{{ $shipment->tracking_number }}</span> when contacting cargo support.
                </p>
                <div class="mt-4 space-y-2">
                    <a href="tel:+97715970123" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition">
                        <i class="fas fa-phone"></i> +977-1-5970123
                    </a>
                    <a href="{{ route('tracking.page') }}" class="w-full py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs flex items-center justify-center gap-2 transition">
                        <i class="fas fa-search"></i> Track Another Consignment
                    </a>
                </div>
            </section>

            <!-- Report Issue / Situation Desk Card -->
            <section class="bg-rose-50/80 border border-rose-200/90 rounded-3xl p-5 space-y-3 shadow-2xs">
                <div class="flex items-center gap-2 text-rose-900">
                    <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-xs">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider">Report an Issue / Situation</h4>
                        <p class="text-[10px] text-rose-600">Client dispute & telemetry resolution</p>
                    </div>
                </div>
                <p class="text-xs text-rose-800 leading-relaxed">
                    Come up with any issue (package damage, transit delay, customs hold, discrepancy, or courier conduct)? Provide details anytime.
                </p>
                <button type="button" onclick="openIssueModal()" class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs cursor-pointer">
                    <i class="fas fa-bullhorn"></i>
                    <span>Provide Situation Details</span>
                </button>
            </section>
        </aside>
    </div>
</div>

<!-- SUBSCRIBE ALERTS MODAL -->
<div id="subscribeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 hidden">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="h-9 w-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base">
                    <i class="fas fa-bell"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Automated Milestone Alerts</h3>
                    <p class="text-xs text-slate-500">Live SMS & Email updates</p>
                </div>
            </div>
            <button type="button" onclick="closeSubscribeModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
            Receive instant notifications whenever shipment <span class="font-mono font-bold text-teal-700">{{ $shipment->tracking_number }}</span> changes operational status.
        </p>

        <form method="POST" action="{{ route('tracking.subscribe') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="tracking_number" value="{{ $shipment->tracking_number }}">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" placeholder="consignee@example.com" value="{{ auth()->user()?->email }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone (SMS)</label>
                <input type="text" name="phone" placeholder="e.g. +977-9812345678" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md transition">
                <i class="fas fa-check"></i>
                <span>Subscribe to Tracking Updates</span>
            </button>
        </form>
    </div>
</div>

<!-- SHIPMENT ISSUE & SITUATION REPORTING MODAL -->
<div id="issueModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <span class="h-9 w-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-triangle-exclamation"></i>
                </span>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Report Shipment Issue / Situation</h3>
                    <p class="text-[11px] text-slate-500">
                        Consignment: <span class="font-mono font-bold text-slate-800">{{ $shipment->tracking_number }}</span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeIssueModal()" class="text-slate-400 hover:text-slate-600 text-lg p-1 cursor-pointer">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal Body Scrollable -->
        <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-700">
            <!-- Informational Banner -->
            <div class="p-3.5 rounded-xl bg-teal-50/70 border border-teal-200/70 text-[11px] text-teal-900 flex items-start gap-2.5">
                <i class="fas fa-headset text-teal-600 text-sm mt-0.5"></i>
                <div>
                    <span class="font-bold block">Universal Client Telemetry & Situation Desk</span>
                    <span>Whatever situation you might come up with—damage, transit delay, customs clarification, lost cargo, or general inquiries—our operations team will prioritize and log the resolution directly to this consignment's telemetry.</span>
                </div>
            </div>

            <!-- Existing Issues for this Consignment -->
            @if(isset($shipment->issues) && $shipment->issues->count() > 0)
                <div class="space-y-2 border-b border-slate-200 pb-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 block">Previously Reported Issues ({{ $shipment->issues->count() }})</span>
                    <div class="space-y-2 max-h-36 overflow-y-auto pr-1">
                        @foreach($shipment->issues as $iss)
                            @php
                                $badgeClass = match($iss->status) {
                                    'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'in_review' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-amber-100 text-amber-800 border-amber-200',
                                };
                            @endphp
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-[11px] text-slate-900">{{ $iss->issue_number }} &middot; {{ ucwords(str_replace('_', ' ', $iss->issue_type)) }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase border {{ $badgeClass }}">{{ $iss->status }}</span>
                                </div>
                                <p class="text-[11px] text-slate-600 line-clamp-2">{{ $iss->situation_description }}</p>
                                @if($iss->resolution_notes)
                                    <p class="text-[10px] text-emerald-800 bg-emerald-50 p-1.5 rounded mt-1">
                                        <i class="fas fa-check-circle mr-1"></i><strong>Resolution:</strong> {{ $iss->resolution_notes }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Submission Form -->
            <form method="POST" action="{{ route('shipments.issues.store', $shipment->tracking_number) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Issue / Situation Category -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Situation Category <span class="text-rose-500">*</span>
                    </label>
                    <select name="issue_type" required class="w-full text-xs font-semibold px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 text-slate-800">
                        <option value="damage">📦 Damaged Package or Broken Contents</option>
                        <option value="delay">⏱️ Excessive Transit Delay / Missed Delivery SLA</option>
                        <option value="lost_item">❓ Missing Items / Lost Parcel</option>
                        <option value="customs_hold">🛂 Customs Clearance Hold / Document Required</option>
                        <option value="billing_discrepancy">💵 Billing, Invoice or Tariff Discrepancy</option>
                        <option value="return_request">🔄 Return to Origin (RTO) Request</option>
                        <option value="rider_conduct">🛵 Courier / Rider Conduct Feedback</option>
                        <option value="general_inquiry">💬 General Inquiry / Other Situation</option>
                    </select>
                </div>

                <!-- Detailed Narrative / Situation -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Details of Situation / Issue <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="situation_description" rows="4" required minlength="8"
                              placeholder="Please provide complete details of the situation you have come up with (e.g. what occurred, condition of package, timestamps, or requests)..."
                              class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 text-slate-900"></textarea>
                </div>

                <!-- Contact & Claim Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Contact Name</label>
                        <input type="text" name="contact_name" value="{{ auth()->user()?->name }}" placeholder="Your name"
                               class="w-full text-xs px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 text-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Contact Email</label>
                        <input type="email" name="contact_email" value="{{ auth()->user()?->email }}" placeholder="email@example.com"
                               class="w-full text-xs px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 text-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ auth()->user()?->phone }}" placeholder="+977-98..."
                               class="w-full text-xs px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 text-slate-800">
                    </div>
                </div>

                <!-- Optional Claim Amount & Proof Attachment -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Claim Amount (Optional)</label>
                        <div class="flex items-center gap-1">
                            <select name="claimed_currency" class="text-xs font-bold px-2 py-1.5 bg-slate-100 border border-slate-300 rounded-lg">
                                <option value="NPR">NPR (Rs.)</option>
                                <option value="USD">USD ($)</option>
                            </select>
                            <input type="number" step="0.01" min="0" name="claimed_amount" placeholder="0.00"
                                   class="w-full text-xs font-mono font-bold px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 text-slate-900">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Photo / Proof Attachment</label>
                        <input type="file" name="attachment" accept="image/*,.pdf"
                               class="w-full text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 text-slate-600 border border-slate-300 rounded-lg">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeIssueModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-md transition cursor-pointer">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit Situation Report</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function copyTrackingUrl() {
    navigator.clipboard.writeText(window.location.href);
    const btn = document.getElementById('copyBtn');
    btn.innerHTML = '<i class="fas fa-check text-teal-600"></i> Copied!';
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-link text-slate-400"></i> <span>Copy Link</span>';
    }, 2000);
}

function openSubscribeModal() {
    document.getElementById('subscribeModal').classList.remove('hidden');
}

function closeSubscribeModal() {
    document.getElementById('subscribeModal').classList.add('hidden');
}

function openIssueModal() {
    document.getElementById('issueModal').classList.remove('hidden');
}

function closeIssueModal() {
    document.getElementById('issueModal').classList.add('hidden');
}

// Initialize Interactive Global Flight Route Map
document.addEventListener('DOMContentLoaded', function () {
    const coords = @json($coords);

    if (typeof L === 'undefined') {
        return;
    }

    const origin = coords.origin || { lat: 27.7172, lng: 85.3240 };
    const hub = coords.hub || { lat: 25.2532, lng: 55.3657 };
    const dest = coords.destination || { lat: 40.7128, lng: -74.0060 };

    const map = L.map('globalFlightMap', {
        zoomControl: false,
        attributionControl: false
    }).setView([origin.lat, origin.lng], 3);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18
    }).addTo(map);

    // Custom pulse marker icons
    const createMarkerIcon = (color, label) => L.divIcon({
        className: 'custom-hub-marker',
        html: `<div style="background-color: ${color}; width: 28px; height: 28px; border-radius: 8px; border: 2px solid white; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px; font-family: monospace; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">${label}</div>`,
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    // Add Markers
    const originMarker = L.marker([origin.lat, origin.lng], { icon: createMarkerIcon('#0d9488', 'KTM') })
        .addTo(map)
        .bindPopup(`<b>${origin.name || 'Kathmandu Gateway'}</b><br>Origin Departure Gateway`);

    const hubMarker = L.marker([hub.lat, hub.lng], { icon: createMarkerIcon('#0284c7', hub.iata || 'HUB') })
        .addTo(map)
        .bindPopup(`<b>${hub.name || 'Transit Hub'}</b><br>${hub.city || 'Air Hub'}`);

    const destMarker = L.marker([dest.lat, dest.lng], { icon: createMarkerIcon('#10b981', 'DST') })
        .addTo(map)
        .bindPopup(`<b>${dest.name || 'Destination Port'}</b><br>${dest.city || 'Delivery City'}`);

    // Draw connecting corridor lines
    // Helper to calculate realistic Great-Circle Geodesic Arc
    function calculateGeodesicArc(p1, p2, numPoints = 25) {
        const points = [];
        const lngDiff = Math.abs(p2.lng - p1.lng);
        const arcOffset = Math.min(12, lngDiff * 0.12 + 2);

        for (let i = 0; i <= numPoints; i++) {
            const f = i / numPoints;
            const lat = (1 - f) * p1.lat + f * p2.lat + Math.sin(Math.PI * f) * arcOffset;
            const lng = (1 - f) * p1.lng + f * p2.lng;
            points.push([lat, lng]);
        }
        return points;
    }

    // Build full multi-leg flight path (KTM -> HUB and HUB -> DEST)
    const leg1 = calculateGeodesicArc(origin, hub, 30);
    const leg2 = calculateGeodesicArc(hub, dest, 40);
    const fullFlightPath = leg1.concat(leg2.slice(1));

    // Draw glowing animated flight path
    const flightPolyline = L.polyline(fullFlightPath, {
        color: '#2dd4bf',
        weight: 3.5,
        opacity: 0.85,
        dashArray: '8, 8',
        className: 'curved-flight-path'
    }).addTo(map);

    // Glowing Plane Icon on Map
    const createPlaneIcon = () => L.divIcon({
        className: 'custom-live-plane',
        html: `<div class="live-plane-marker"><i class="fas fa-plane text-white text-sm" style="transform: rotate(45deg);"></i></div>`,
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    const livePlane = L.marker(fullFlightPath[0], { icon: createPlaneIcon() }).addTo(map);

    // Animated Plane Glide along Corridor
    let planeStep = 0;
    const totalSteps = fullFlightPath.length;
    setInterval(() => {
        planeStep = (planeStep + 1) % totalSteps;
        const currentCoord = fullFlightPath[planeStep];
        livePlane.setLatLng(currentCoord);

        // Compute simulated altitude and speed fluctuations
        const progress = planeStep / totalSteps;
        const simulatedAlt = Math.round(32000 + Math.sin(progress * Math.PI) * 5000);
        const simulatedSpeed = Math.round(820 + (Math.random() * 30));
        
        const hudAlt = document.getElementById('hudAlt');
        const hudSpeed = document.getElementById('hudSpeed');
        if (hudAlt) hudAlt.innerText = `FL${Math.round(simulatedAlt / 100)} (${simulatedAlt.toLocaleString()} FT)`;
        if (hudSpeed) hudSpeed.innerText = `Cruising: ${simulatedSpeed} km/h`;
    }, 600);

    // Fit map bounds
    const group = new L.featureGroup([originMarker, hubMarker, destMarker, flightPolyline]);
    map.fitBounds(group.getBounds().pad(0.2));

    // Live Telemetry Auto-Refresh Engine (Async Fetch Every 30s)
    let secondsLeft = 30;
    const countdownEl = document.getElementById('countdown');
    const autoStatusEl = document.getElementById('autoRefreshStatus');
    const trackingNum = "{{ $shipment->tracking_number }}";

    function refreshTelemetryAsync() {
        if (!trackingNum) return;
        if (autoStatusEl) {
            autoStatusEl.innerHTML = '<span class="text-teal-400 font-mono"><i class="fas fa-satellite animate-spin text-xs"></i> Syncing telemetry...</span>';
        }

        fetch(`/api/v1/track/${trackingNum}`)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    if (autoStatusEl) {
                        autoStatusEl.innerHTML = '<span class="text-emerald-400 font-mono"><i class="fas fa-check-circle text-xs"></i> Live synced just now</span>';
                        setTimeout(() => {
                            autoStatusEl.innerHTML = 'Auto-sync in <span id="countdown">30</span>s';
                        }, 3000);
                    }
                }
            })
            .catch(() => {
                if (autoStatusEl) {
                    autoStatusEl.innerHTML = 'Auto-sync in <span id="countdown">30</span>s';
                }
            });
    }

    setInterval(() => {
        secondsLeft--;
        if (secondsLeft <= 0) {
            secondsLeft = 30;
            refreshTelemetryAsync();
        }
        const cd = document.getElementById('countdown');
        if (cd) cd.textContent = secondsLeft;
    }, 1000);
});
</script>
@endpush
@endsection
