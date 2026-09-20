@extends('layouts.public')

@section('title', 'Domestic Express Tracking - ' . $shipment->tracking_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    @keyframes domestic-pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.5); }
        70% { transform: scale(1); box-shadow: 0 0 0 14px rgba(13, 148, 136, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0); }
    }
    .domestic-active { animation: domestic-pulse 2.2s infinite ease-in-out; }
    
    @keyframes truck-travel {
        0% { left: 8%; opacity: 0; transform: translateY(-50%); }
        15% { opacity: 1; }
        85% { opacity: 1; }
        100% { left: 90%; opacity: 0; transform: translateY(-50%); }
    }
    .truck-anim {
        animation: truck-travel 6s infinite cubic-bezier(0.4, 0, 0.2, 1);
    }

    #domesticRouteMap .leaflet-tile {
        filter: brightness(0.85) contrast(1.1);
    }
</style>
@endpush

@section('content')
@php
    $service = config('tracking.services.' . $shipment->service_type, config('tracking.services.default'));
    $status = config('tracking.statuses.' . $shipment->status, config('tracking.statuses.pending'));
    
    $events = $shipment->tracking_history ?: [[
        'event_code' => 'booking_confirmed',
        'status' => $shipment->status,
        'status_label' => $status['label'],
        'description' => $status['description'],
        'location' => $shipment->sender_city ?: 'Kathmandu Central Sorting Hub',
        'time' => $shipment->created_at->toIso8601String(),
    ]];

    $milestoneStep = match($shipment->status) {
        'delivered' => 4,
        'out_for_delivery' => 3,
        'in_transit' => 2,
        'picked_up' => 1,
        default => 0,
    };

    $milestones = [
        ['label' => 'Booking Placed', 'icon' => 'fa-receipt', 'desc' => 'Scheduled & verified in Nepal'],
        ['label' => 'Pickup & Sorted', 'icon' => 'fa-box', 'desc' => 'Collected from sender & depot verified'],
        ['label' => 'Highway Express', 'icon' => 'fa-truck-fast', 'desc' => 'Inter-district highway transit'],
        ['label' => 'Ward Dispatch', 'icon' => 'fa-motorcycle', 'desc' => 'Rider assigned for doorstep delivery'],
        ['label' => 'Delivered', 'icon' => 'fa-circle-check', 'desc' => 'Verified proof of delivery'],
    ];

    // Nepal city coordinate mapping
    $nepalCities = [
        'KATHMANDU' => ['lat' => 27.7172, 'lng' => 85.3240],
        'LALITPUR' => ['lat' => 27.6588, 'lng' => 85.3247],
        'BHAKTAPUR' => ['lat' => 27.6710, 'lng' => 85.4298],
        'POKHARA' => ['lat' => 28.2096, 'lng' => 83.9856],
        'BIRATNAGAR' => ['lat' => 26.4525, 'lng' => 87.2718],
        'BUTWAL' => ['lat' => 27.7006, 'lng' => 83.4484],
        'BHARAATPUR' => ['lat' => 27.6833, 'lng' => 84.4333],
        'CHITWAN' => ['lat' => 27.6833, 'lng' => 84.4333],
        'NARAYANGARH' => ['lat' => 27.6934, 'lng' => 84.4285],
        'NEPALGUNJ' => ['lat' => 28.0500, 'lng' => 81.6167],
        'DHANGADHI' => ['lat' => 28.6944, 'lng' => 80.5894],
        'HETAUDA' => ['lat' => 27.4287, 'lng' => 85.0322],
        'BIRGUNJ' => ['lat' => 27.0167, 'lng' => 84.8667],
        'DHARAN' => ['lat' => 26.8124, 'lng' => 87.2834],
        'DAMAK' => ['lat' => 26.6667, 'lng' => 87.7000],
        'BIRTAMODE' => ['lat' => 26.6333, 'lng' => 87.9833],
    ];

    $originCityUpper = strtoupper(trim($shipment->sender_city ?? 'KATHMANDU'));
    $destCityUpper = strtoupper(trim($shipment->receiver_city ?? 'POKHARA'));

    $originCoords = $nepalCities[$originCityUpper] ?? ['lat' => 27.7172, 'lng' => 85.3240];
    $destCoords = $nepalCities[$destCityUpper] ?? ['lat' => 28.2096, 'lng' => 83.9856];
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
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Domestic Delivery &middot;</span>
                    <span class="text-xs font-bold text-teal-700">All 7 Provinces of Nepal</span>
                </div>
                <p class="text-xs text-slate-500">Real-Time Hub Transit & Proof of Delivery</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Alert Subscription Button -->
            <button type="button" onclick="openDomesticSubscribeModal()" class="px-3.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-xs font-bold flex items-center gap-1.5 transition">
                <i class="fas fa-bell text-teal-600"></i> <span>Get Alerts</span>
            </button>

            <!-- Copy Link -->
            <button type="button" onclick="copyDomesticUrl()" id="domCopyBtn" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-teal-500 text-slate-700 hover:text-teal-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-link text-slate-400"></i> <span>Copy Link</span>
            </button>

            <!-- Print Waybill / HAWB Copy Button -->
            <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="Print Domestic Consignment Note (HAWB)">
                <i class="fas fa-print"></i> <span>Waybill</span>
            </a>

            <!-- Commercial Invoice -->
            <a href="{{ route('shipments.invoice', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="View & Print Official Invoice">
                <i class="fas fa-file-invoice-dollar text-teal-600"></i> <span>Invoice</span>
            </a>

            <!-- Packing List -->
            <a href="{{ route('shipments.packing-list', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="View & Print Packing List">
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

            <!-- WhatsApp Live Help -->
            <a href="https://wa.me/97715970123?text=Inquiry%20about%20domestic%20shipment%20{{ $shipment->tracking_number }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs">
                <i class="fab fa-whatsapp text-sm"></i> <span>Live Help</span>
            </a>
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

    <!-- HERO DOMESTIC MASTER CARD -->
    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-teal-950 text-white shadow-xl border border-slate-800">
        <div class="p-6 md:p-8 pb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Identifiers -->
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            <span class="h-2 w-2 rounded-full bg-teal-400 animate-ping"></span>
                            {{ strtoupper($service['label']) }}
                        </span>
                        <span class="rounded-xl bg-white/10 px-3.5 py-1 text-xs font-mono font-bold tracking-widest text-teal-200 border border-white/10">
                            77 DISTRICT EXPRESS NETWORK
                        </span>
                        <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="rounded-xl bg-white/10 hover:bg-white/20 px-3.5 py-1 text-xs font-mono font-bold tracking-widest text-teal-200 border border-white/15 flex items-center gap-1.5 transition" title="Print Domestic Consignment Note">
                            <i class="fas fa-print text-[10px]"></i> Waybill Copy
                        </a>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-white">
                        {{ $shipment->tracking_number }}
                    </h1>

                    <p class="text-xs text-slate-400 flex items-center gap-2">
                        <span><i class="fas fa-truck-fast text-teal-400"></i> Highway Fleet Synchronized</span>
                        <span>&middot;</span>
                        <span>Updated {{ $shipment->updated_at->diffForHumans() }}</span>
                        <span>&middot;</span>
                        <span class="text-teal-300 font-mono">Live Sync Active</span>
                    </p>
                </div>

                <!-- Status Badge -->
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-xl p-4 sm:p-5 rounded-2xl border border-white/15">
                    <div class="h-14 w-14 rounded-2xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center text-teal-300 text-2xl shrink-0 domestic-active">
                        <i class="fas {{ $status['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-teal-300/80">Delivery Milestone</span>
                        <h3 class="text-xl font-extrabold text-white">
                            {{ $status['label'] }}
                        </h3>
                        <p class="text-xs text-slate-300 mt-0.5 max-w-xs leading-tight">
                            {{ $status['description'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- INTER-DISTRICT HIGHWAY ROUTE CORRIDOR PREVIEW -->
            <div class="mt-8 pt-6 border-t border-white/10">
                <div class="bg-slate-900/80 rounded-2xl p-4 sm:p-6 border border-white/10 relative overflow-hidden">
                    <div class="relative flex items-center justify-between z-10">
                        <!-- Origin Hub -->
                        <div class="flex items-center gap-3">
                            <span class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center text-xl shrink-0 border border-teal-500/30">
                                <i class="fas fa-warehouse"></i>
                            </span>
                            <div>
                                <span class="text-[10px] font-bold text-teal-400 uppercase tracking-widest">Origin Hub</span>
                                <h4 class="text-base sm:text-lg font-bold text-white leading-snug">
                                    {{ $shipment->sender_city ?: 'Kathmandu' }}
                                </h4>
                                <span class="text-[11px] font-mono text-slate-400">{{ $shipment->sender_zone ?: 'Central Bagmati Hub' }}</span>
                            </div>
                        </div>

                        <!-- Midline Animated Truck -->
                        <div class="hidden md:flex flex-col items-center flex-1 px-8 relative">
                            <div class="w-full h-0.5 border-t-2 border-dashed border-teal-500/40 relative">
                                <div class="truck-anim absolute top-1/2 text-teal-300 text-lg">
                                    <i class="fas fa-truck-fast"></i>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-400 mt-2 bg-slate-950 px-3 py-0.5 rounded-full border border-slate-800">
                                Nepal Highway Fleet Corridor
                            </span>
                        </div>

                        <!-- Destination Hub -->
                        <div class="flex items-center gap-3 text-right">
                            <div>
                                <span class="text-[10px] font-bold text-teal-400 uppercase tracking-widest">Destination Hub</span>
                                <h4 class="text-base sm:text-lg font-bold text-white leading-snug">
                                    {{ $shipment->receiver_city ?: 'District Center' }}
                                </h4>
                                <span class="text-[11px] font-mono text-slate-400">
                                    {{ $shipment->receiver_zone ?: 'Regional Delivery Depot' }} &bull; Ward {{ $shipment->receiver_ward ?: '1' }}
                                </span>
                            </div>
                            <span class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center text-xl shrink-0 border border-teal-500/30">
                                <i class="fas fa-location-dot"></i>
                            </span>
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

    <!-- INTERACTIVE NEPAL PROVINCIAL ROUTE MAP (Leaflet) -->
    <section class="bg-slate-900 rounded-3xl p-5 border border-slate-800 shadow-lg text-white space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-teal-400 animate-pulse"></span>
                <h3 class="text-sm font-bold tracking-wide uppercase text-slate-200 flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-teal-400"></i>
                    Nepal Provincial Highway Transit Map
                </h3>
            </div>
            <span class="text-xs text-slate-400 font-mono">
                Route: {{ $shipment->sender_city ?: 'Kathmandu' }} &rarr; {{ $shipment->receiver_city ?: 'Destination' }}
            </span>
        </div>

        <div id="domesticRouteMap" class="w-full h-72 md:h-80 rounded-2xl overflow-hidden border border-slate-800 z-0"></div>
    </section>

    <!-- SPECS TILES -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Weight Details</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900 font-mono">
                    {{ number_format($shipment->weight ?? 1, 2) }}
                </span>
                <span class="text-xs font-bold text-slate-500">KG Verified</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Certified Tare & Gross</p>
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
            <p class="text-[11px] text-slate-400 mt-1">Inter-District Express</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estimated Delivery</span>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-lg font-black text-teal-700">
                    {{ $shipment->estimated_delivery_at ? $shipment->estimated_delivery_at->format('M d, Y') : 'On Schedule' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Highway Transit SLA Guaranteed</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Payment / COD</span>
            <div class="mt-2 flex items-center gap-2">
                @if($shipment->is_cod)
                    <span class="h-8 w-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-sm">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </span>
                    <div>
                        <span class="text-xs font-bold text-slate-800">COD: NPR {{ number_format($shipment->cod_amount, 2) }}</span>
                    </div>
                @else
                    <span class="h-8 w-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm">
                        <i class="fas fa-circle-check"></i>
                    </span>
                    <span class="text-xs font-bold text-slate-800">Prepaid / Billed</span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Secure Settlement</p>
        </div>
    </section>

    <!-- TIMELINE & SUMMARY VAULT -->
    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <!-- Event Timeline -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-2xs">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-timeline text-teal-600"></i> Domestic Journey Milestones
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Chronological scan telemetry from origin booking to recipient verification</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                    {{ count($events) }} Recorded Events
                </span>
            </div>

            <div class="relative space-y-0">
                @foreach($events as $index => $event)
                    @php
                        $isLatest = $loop->first;
                        $evInfo = config('tracking.statuses.' . ($event['status'] ?? ''), config('tracking.statuses.pending'));
                        $iconName = $event['icon'] ?? $evInfo['icon'];
                    @endphp
                    <div class="relative grid grid-cols-[40px_1fr] gap-4 pb-8 last:pb-2">
                        @if(!$loop->last)
                            <div class="absolute left-[19px] top-10 bottom-0 w-0.5 bg-slate-200"></div>
                        @endif

                        <!-- Pin -->
                        <div class="relative z-10 flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-bold shadow-2xs {{ $isLatest ? 'bg-teal-600 text-white domestic-active' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            <i class="fas {{ $iconName }}"></i>
                        </div>

                        <!-- Details Card -->
                        <div class="rounded-2xl border p-4 transition {{ $isLatest ? 'border-teal-300/80 bg-teal-50/40 shadow-xs' : 'border-slate-200/80 bg-white' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <span>{{ $event['status_label'] ?? ucfirst(str_replace('_', ' ', $event['status'] ?? 'Updated')) }}</span>
                                    @if($isLatest)
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-teal-600 text-white">
                                            Current Checkpoint
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
            <!-- Consignment Summary Card -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200 shadow-2xs">
                <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">
                    Consignment Manifest Details
                </h3>

                <dl class="divide-y divide-slate-100 text-xs mt-2">
                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Tracking Number</dt>
                        <dd class="font-mono font-bold text-slate-900">{{ $shipment->tracking_number }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Origin Hub</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->sender_city ?: 'Kathmandu' }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Destination Hub</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->receiver_city ?: 'District Depot' }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Target Ward</dt>
                        <dd class="font-medium text-slate-800">Ward {{ $shipment->receiver_ward ?: '1' }}, {{ $shipment->receiver_zone }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Service Category</dt>
                        <dd class="font-bold text-teal-700">{{ strtoupper($service['label']) }}</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Total Weight</dt>
                        <dd class="font-bold text-slate-900">{{ number_format($shipment->weight ?? 1, 2) }} kg</dd>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <dt class="text-slate-500">Booking Date</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <!-- Privacy Safe Notice -->
                <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2 leading-relaxed">
                    <i class="fas fa-shield-halved text-slate-400 text-xs shrink-0 mt-0.5"></i>
                    <span>In accordance with data protection guidelines, recipient phone numbers and detailed street addresses are protected.</span>
                </div>
            </section>

            <!-- OFFICIAL DOMESTIC CONSIGNMENT WAYBILL (HAWB) CARD -->
            <section class="rounded-3xl border border-teal-100 bg-gradient-to-br from-teal-50/50 via-white to-emerald-50/40 p-6 shadow-sm">
                <div class="flex items-center gap-3 border-b border-teal-100/80 pb-4 mb-4">
                    <div class="h-10 w-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Official Freight Waybill</h4>
                        <p class="text-[11px] text-slate-500">Official Consignment Note (A4 · 2 Copies)</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                    Official non-monetary domestic freight waybill with Consignee Delivery Run-sheet Copy, Proof of Delivery (POD) Carrier Copy, and regional routing barcode.
                </p>
                <div class="space-y-2">
                    <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs">
                        <i class="fas fa-print"></i> <span>Print Official Waybill (A4)</span>
                    </a>
                    <a href="{{ route('tracking.hawb.popup', $shipment->tracking_number) }}" target="_blank" class="w-full py-2 px-4 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 flex items-center justify-center gap-2 transition">
                        <i class="fas fa-receipt text-slate-400"></i> <span>Single-Page Print Slip</span>
                    </a>
                </div>
            </section>

            <!-- Support & Quick Actions -->
            <section class="bg-slate-900 rounded-3xl p-6 text-white border border-slate-800">
                <h4 class="font-bold text-sm text-white">Need Delivery Support?</h4>
                <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                    Quote reference <span class="font-mono text-teal-300 font-bold">{{ $shipment->tracking_number }}</span> when calling dispatch.
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
        </aside>
    </div>
</div>

<!-- SUBSCRIBE ALERTS MODAL -->
<div id="domesticSubscribeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 hidden">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="h-9 w-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base">
                    <i class="fas fa-bell"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Automated Milestone Alerts</h3>
                    <p class="text-xs text-slate-500">SMS & Email updates for Nepal delivery</p>
                </div>
            </div>
            <button type="button" onclick="closeDomesticSubscribeModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
            Receive instant notifications whenever shipment <span class="font-mono font-bold text-teal-700">{{ $shipment->tracking_number }}</span> moves through the highway fleet.
        </p>

        <form method="POST" action="{{ route('tracking.subscribe') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="tracking_number" value="{{ $shipment->tracking_number }}">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" placeholder="customer@example.com" value="{{ auth()->user()?->email }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone (Nepal SMS)</label>
                <input type="text" name="phone" placeholder="98XXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md transition">
                <i class="fas fa-check"></i>
                <span>Subscribe to Delivery Alerts</span>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function copyDomesticUrl() {
    navigator.clipboard.writeText(window.location.href);
    const btn = document.getElementById('domCopyBtn');
    btn.innerHTML = '<i class="fas fa-check text-teal-600"></i> Copied!';
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-link text-slate-400"></i> <span>Copy Link</span>';
    }, 2000);
}

function openDomesticSubscribeModal() {
    document.getElementById('domesticSubscribeModal').classList.remove('hidden');
}

function closeDomesticSubscribeModal() {
    document.getElementById('domesticSubscribeModal').classList.add('hidden');
}

// Initialize Nepal Provincial Route Map
document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') return;

    const origin = @json($originCoords);
    const dest = @json($destCoords);

    const map = L.map('domesticRouteMap', {
        zoomControl: false,
        attributionControl: false
    }).setView([origin.lat, origin.lng], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18
    }).addTo(map);

    const createPin = (color, label) => L.divIcon({
        className: 'custom-nepal-pin',
        html: `<div style="background-color: ${color}; width: 28px; height: 28px; border-radius: 8px; border: 2px solid white; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px; font-family: monospace; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">${label}</div>`,
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    const m1 = L.marker([origin.lat, origin.lng], { icon: createPin('#0d9488', 'ORG') })
        .addTo(map)
        .bindPopup(`<b>${origin.name || 'Origin Hub'}</b><br>Intake & Dispatch Hub`);

    const m2 = L.marker([dest.lat, dest.lng], { icon: createPin('#10b981', 'DST') })
        .addTo(map)
        .bindPopup(`<b>${dest.name || 'Destination Hub'}</b><br>Destination District Depot`);

    const polyline = L.polyline([[origin.lat, origin.lng], [dest.lat, dest.lng]], {
        color: '#0d9488',
        weight: 4,
        opacity: 0.85,
        dashArray: '6, 8'
    }).addTo(map);

    const group = new L.featureGroup([m1, m2]);
    map.fitBounds(group.getBounds().pad(0.3));
});
</script>
@endpush
@endsection
