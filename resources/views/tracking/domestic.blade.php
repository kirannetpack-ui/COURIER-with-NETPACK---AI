@extends('layouts.public')

@section('title', 'Domestic Express Tracking - ' . $shipment->tracking_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    @keyframes pulse-ring {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.5); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(13, 148, 136, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0); }
    }
    .active-stage-pulse { animation: pulse-ring 2.2s infinite ease-in-out; }
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

    // 5 Distinct Color-Coded Stages for Domestic Network
    $milestones = [
        [
            'step' => 1,
            'label' => 'Booking Placed',
            'code' => 'BKG',
            'icon' => 'fa-receipt',
            'desc' => 'Scheduled & Verified',
            'color' => 'blue',
            'bg_active' => 'bg-blue-600',
            'border_active' => 'border-blue-600',
            'text_color' => 'text-blue-600',
            'light_bg' => 'bg-blue-50',
            'badge' => 'Verified'
        ],
        [
            'step' => 2,
            'label' => 'Pickup & Sorted',
            'code' => 'HUB',
            'icon' => 'fa-box',
            'desc' => 'Collected from Sender',
            'color' => 'purple',
            'bg_active' => 'bg-purple-600',
            'border_active' => 'border-purple-600',
            'text_color' => 'text-purple-600',
            'light_bg' => 'bg-purple-50',
            'badge' => 'Origin Depot'
        ],
        [
            'step' => 3,
            'label' => 'Highway Express',
            'code' => 'TRK',
            'icon' => 'fa-truck-fast',
            'desc' => 'Inter-District Highway Transit',
            'color' => 'amber',
            'bg_active' => 'bg-amber-500',
            'border_active' => 'border-amber-500',
            'text_color' => 'text-amber-600',
            'light_bg' => 'bg-amber-50',
            'badge' => 'In Transit'
        ],
        [
            'step' => 4,
            'label' => 'Ward Dispatch',
            'code' => 'WST',
            'icon' => 'fa-motorcycle',
            'desc' => 'Rider Assigned for Doorstep',
            'color' => 'teal',
            'bg_active' => 'bg-teal-600',
            'border_active' => 'border-teal-600',
            'text_color' => 'text-teal-600',
            'light_bg' => 'bg-teal-50',
            'badge' => 'Out for Delivery'
        ],
        [
            'step' => 5,
            'label' => 'Delivered',
            'code' => 'DLV',
            'icon' => 'fa-circle-check',
            'desc' => 'Signed & Delivered to Recipient',
            'color' => 'emerald',
            'bg_active' => 'bg-emerald-600',
            'border_active' => 'border-emerald-600',
            'text_color' => 'text-emerald-600',
            'light_bg' => 'bg-emerald-50',
            'badge' => 'Completed'
        ],
    ];

    $currentStage = $milestones[$milestoneStep] ?? $milestones[0];

    // Nepal city coordinate mapping
    $nepalCities = [
        'KATHMANDU' => ['lat' => 27.7172, 'lng' => 85.3240],
        'LALITPUR' => ['lat' => 27.6588, 'lng' => 85.3247],
        'BHAKTAPUR' => ['lat' => 27.6710, 'lng' => 85.4298],
        'POKHARA' => ['lat' => 28.2096, 'lng' => 83.9856],
        'BIRATNAGAR' => ['lat' => 26.4525, 'lng' => 87.2718],
        'BUTWAL' => ['lat' => 27.7006, 'lng' => 83.4484],
        'BHARATPUR' => ['lat' => 27.6833, 'lng' => 84.4333],
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

    $latestEvent = reset($events) ?: null;
    $latestLocation = $latestEvent['location'] ?? ($shipment->current_location ?: ($shipment->sender_city . ', Nepal'));
    $latestTime = !empty($latestEvent['time']) ? \Carbon\Carbon::parse($latestEvent['time']) : $shipment->updated_at;
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    <!-- TOP ACTION BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('tracking.page') }}" class="h-9 w-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" title="Back to Tracking Search">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Domestic Delivery &middot;</span>
                    <span class="text-xs font-bold text-teal-700">77 Districts Network</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ strtoupper($service['label']) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">Real-Time Transit & Proof of Delivery</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Alert Subscription Button -->
            <button type="button" onclick="openDomesticSubscribeModal()" class="px-3.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-xs font-bold flex items-center gap-1.5 transition">
                <i class="fas fa-bell text-teal-600"></i> <span>Get Alerts</span>
            </button>

            <!-- Copy Link -->
            <button type="button" onclick="copyDomesticUrl()" id="domCopyBtn" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-link text-slate-400"></i> <span>Copy Link</span>
            </button>

            <!-- Print Waybill Action (Mandatory for test assertion) -->
            <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold flex items-center gap-1.5 transition shadow-xs" title="Print Domestic Waybill">
                <i class="fas fa-print"></i> <span>Print Waybill</span>
            </a>

            <!-- Commercial Invoice -->
            <a href="{{ route('shipments.invoice', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold flex items-center gap-1.5 transition" title="View Official Invoice">
                <i class="fas fa-file-invoice text-teal-600"></i> <span>Invoice</span>
            </a>

            <!-- Packing List -->
            <a href="{{ route('shipments.packing-list', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold flex items-center gap-1.5 transition" title="View Packing List">
                <i class="fas fa-boxes-stacked text-teal-600"></i> <span>Packing List</span>
            </a>

            @if($shipment->seller_bill_file)
                <a href="{{ route('shipments.seller-bill', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-bold flex items-center gap-1.5 transition" title="Attached Tax Bill">
                    <i class="fas fa-paperclip text-amber-600"></i> <span>Tax Bill</span>
                </a>
            @endif

            <!-- Print Status -->
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-file-lines text-slate-400"></i> <span>Print Status</span>
            </button>

            <!-- WhatsApp Live Help -->
            <a href="https://wa.me/97715970123?text=Inquiry%20about%20domestic%20shipment%20{{ $shipment->tracking_number }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-xs">
                <i class="fab fa-whatsapp"></i> <span>Live Help</span>
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

    <!-- MAIN TRACKING CONSOLE CARD -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">

        <!-- HEADER SECTION: Track ID & Real-Time Operational Status -->
        <div class="p-6 sm:p-8 bg-slate-900 text-white border-b border-slate-800">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Tracking Identifiers -->
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            <span class="h-2 w-2 rounded-full bg-teal-400 animate-ping"></span>
                            🇳🇵 NEPAL DOMESTIC COURIER
                        </span>
                        <span class="rounded-xl bg-white/10 px-3 py-1 text-xs font-mono font-bold tracking-wider text-teal-200 border border-white/10">
                            77 DISTRICT NETWORK
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-white">
                        {{ $shipment->tracking_number }}
                    </h1>

                    <p class="text-xs text-slate-400 flex items-center gap-2 flex-wrap">
                        <span><i class="fas fa-clock text-teal-400"></i> Updated {{ $shipment->updated_at->diffForHumans() }}</span>
                        <span>&middot;</span>
                        <span>Live Domestic Hub Telemetry</span>
                    </p>
                </div>

                <!-- Operational Milestone Banner -->
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-white/15 max-w-md">
                    <div class="h-14 w-14 rounded-2xl {{ $currentStage['bg_active'] }} flex items-center justify-center text-white text-2xl shrink-0 shadow-lg active-stage-pulse">
                        <i class="fas {{ $status['icon'] ?? 'fa-box' }}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-teal-300">
                                Current Status (Stage {{ $milestoneStep + 1 }} of 5)
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-white">
                            {{ $status['label'] }}
                        </h2>
                        <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                            {{ $status['description'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Route Summary Strip -->
            <div class="mt-6 pt-5 border-t border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/60 p-3.5 rounded-xl border border-slate-700/60 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">🇳🇵</span>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Origin City</span>
                            <p class="font-bold text-slate-100">{{ $shipment->sender_city ?: 'Kathmandu' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-slate-400">
                        <span class="h-px w-8 sm:w-16 bg-slate-700"></span>
                        <span class="flex items-center gap-1 font-mono text-[11px] text-teal-300 bg-slate-900 px-2.5 py-1 rounded-full border border-slate-700">
                            <i class="fas fa-truck text-[10px]"></i> Highway Fleet Transit
                        </span>
                        <span class="h-px w-8 sm:w-16 bg-slate-700"></span>
                    </div>

                    <div class="flex items-center gap-2.5 sm:text-right sm:flex-row-reverse">
                        <span class="text-2xl">📍</span>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Destination Hub</span>
                            <p class="font-bold text-slate-100">{{ $shipment->receiver_city ?: 'District Depot' }} (Ward {{ $shipment->receiver_ward ?: '1' }})</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- THE MULTI-COLORED PROGRESSIVE BAR -->
        <div class="p-6 sm:p-8 bg-white border-b border-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                    <i class="fas fa-bars-progress text-teal-600"></i>
                    <span>Delivery Progress Pipeline</span>
                </h3>
                <span class="text-xs font-bold text-slate-600">
                    {{ round((($milestoneStep + 1) / 5) * 100) }}% Completed
                </span>
            </div>

            <!-- Segmented Multi-Color Progress Track -->
            <div class="relative">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 md:gap-2 relative">

                    @foreach($milestones as $idx => $m)
                        @php
                            $isDone = $idx <= $milestoneStep;
                            $isCurrent = $idx === $milestoneStep;

                            $nodeColorClasses = match($m['color']) {
                                'blue' => [
                                    'active' => 'bg-blue-600 text-white border-blue-600 ring-4 ring-blue-100',
                                    'done' => 'bg-blue-600 text-white border-blue-600',
                                    'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'line' => 'bg-blue-600',
                                ],
                                'purple' => [
                                    'active' => 'bg-purple-600 text-white border-purple-600 ring-4 ring-purple-100',
                                    'done' => 'bg-purple-600 text-white border-purple-600',
                                    'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'line' => 'bg-purple-600',
                                ],
                                'amber' => [
                                    'active' => 'bg-amber-500 text-white border-amber-500 ring-4 ring-amber-100',
                                    'done' => 'bg-amber-500 text-white border-amber-500',
                                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'line' => 'bg-amber-500',
                                ],
                                'teal' => [
                                    'active' => 'bg-teal-600 text-white border-teal-600 ring-4 ring-teal-100',
                                    'done' => 'bg-teal-600 text-white border-teal-600',
                                    'badge' => 'bg-teal-50 text-teal-700 border-teal-200',
                                    'line' => 'bg-teal-600',
                                ],
                                'emerald' => [
                                    'active' => 'bg-emerald-600 text-white border-emerald-600 ring-4 ring-emerald-100',
                                    'done' => 'bg-emerald-600 text-white border-emerald-600',
                                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'line' => 'bg-emerald-600',
                                ],
                                default => [
                                    'active' => 'bg-slate-800 text-white',
                                    'done' => 'bg-slate-800 text-white',
                                    'badge' => 'bg-slate-100 text-slate-700',
                                    'line' => 'bg-slate-800',
                                ]
                            };

                            $nodeStateClass = $isCurrent 
                                ? $nodeColorClasses['active'] . ' active-stage-pulse' 
                                : ($isDone ? $nodeColorClasses['done'] : 'bg-slate-100 text-slate-400 border-slate-200');
                        @endphp

                        <div class="flex md:flex-col items-center md:items-center text-left md:text-center relative group p-2 rounded-2xl transition hover:bg-slate-50/80">
                            
                            @if(!$loop->last)
                                <div class="hidden md:block absolute top-7 left-1/2 w-full h-1.5 -z-0 {{ $idx < $milestoneStep ? $nodeColorClasses['line'] : 'bg-slate-100' }}"></div>
                            @endif

                            @if(!$loop->last)
                                <div class="md:hidden absolute left-6 top-12 bottom-0 w-1 -z-0 {{ $idx < $milestoneStep ? $nodeColorClasses['line'] : 'bg-slate-100' }}"></div>
                            @endif

                            <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 text-base font-bold transition shadow-xs {{ $nodeStateClass }}">
                                @if($isDone && !$isCurrent)
                                    <i class="fas fa-check"></i>
                                @else
                                    <i class="fas {{ $m['icon'] }}"></i>
                                @endif
                            </div>

                            <div class="ml-4 md:ml-0 md:mt-3 flex-1 min-w-0">
                                <div class="flex items-center md:justify-center gap-1.5">
                                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $isDone ? 'text-slate-900' : 'text-slate-400' }}">
                                        {{ $m['label'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-tight mt-0.5">
                                    {{ $m['desc'] }}
                                </p>
                                @if($isCurrent)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $nodeColorClasses['badge'] }}">
                                        Current Stage
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        <!-- MAXIMUM OPERATIONAL INFORMATION: DETAILED TELEMETRY GRID -->
        <div class="p-6 sm:p-8 bg-slate-50/50">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
                <i class="fas fa-circle-info text-teal-600"></i>
                <span>Comprehensive Domestic Consignment Telemetry</span>
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- 1. Current Location -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Current Depot</span>
                    <p class="text-sm font-bold text-slate-900 mt-1 truncate" title="{{ $latestLocation }}">
                        {{ $latestLocation }}
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Verified Depot Scan</span>
                </div>

                <!-- 2. Last Checkpoint Time -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Last Checkpoint</span>
                    <p class="text-sm font-bold text-slate-900 mt-1">
                        {{ $latestTime->format('d M, h:i A') }}
                    </p>
                    <span class="text-[11px] text-teal-600 font-medium block mt-0.5">
                        {{ $latestTime->diffForHumans() }}
                    </span>
                </div>

                <!-- 3. Target District & Ward -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Destination Ward</span>
                    <p class="text-sm font-bold text-slate-900 mt-1">
                        Ward {{ $shipment->receiver_ward ?: '1' }}
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">
                        {{ $shipment->receiver_city ?: 'District' }}
                    </span>
                </div>

                <!-- 4. Weight -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Weight</span>
                    <p class="text-sm font-black text-slate-900 font-mono mt-1">
                        {{ number_format($shipment->weight ?? 1, 2) }} kg
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Verified Scaled</span>
                </div>

                <!-- 5. Assigned Partner / Rider -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Depot Partner</span>
                    <p class="text-sm font-bold text-slate-900 mt-1 truncate">
                        {{ $shipment->domesticPartner?->name ?? 'Regional Hub' }}
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Depot Handling</span>
                </div>

                <!-- 6. Service Priority -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Service Tier</span>
                    <p class="text-sm font-bold text-teal-700 mt-1">
                        {{ strtoupper($service['label']) }}
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">All 7 Provinces</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TIMELINE & SIDEBAR -->
    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        
        <!-- Left: Event Timeline -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-clock-rotate-left text-teal-600"></i>
                        <span>Domestic Journey Timeline</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Chronological scan events across Nepal's transit hubs</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                    {{ count($events) }} {{ Str::plural('Event', count($events)) }}
                </span>
            </div>

            <div class="relative space-y-0 pl-2">
                @foreach($events as $index => $event)
                    @php
                        $isLatest = $index === 0;
                        $evInfo = config('tracking.statuses.' . ($event['status'] ?? ''), config('tracking.statuses.pending'));
                        $iconName = $event['icon'] ?? ($evInfo['icon'] ?? 'fa-circle-dot');

                        $eventColor = match($event['status'] ?? '') {
                            'delivered' => 'bg-emerald-600 text-white',
                            'out_for_delivery' => 'bg-teal-600 text-white',
                            'in_transit' => 'bg-amber-500 text-white',
                            'picked_up' => 'bg-purple-600 text-white',
                            default => 'bg-blue-600 text-white',
                        };
                    @endphp

                    <div class="relative grid grid-cols-[36px_1fr] gap-4 pb-8 last:pb-2">
                        @if(!$loop->last)
                            <div class="absolute left-[17px] top-10 bottom-0 w-0.5 bg-slate-200"></div>
                        @endif

                        <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl text-xs font-bold shadow-xs {{ $loop->first ? $eventColor . ' active-stage-pulse' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            <i class="fas {{ $iconName }}"></i>
                        </div>

                        <div class="rounded-2xl border p-4 transition {{ $loop->first ? 'border-teal-200 bg-teal-50/30 shadow-2xs' : 'border-slate-200/80 bg-white' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <span>{{ $event['status_label'] ?? ucfirst(str_replace('_', ' ', $event['status'] ?? 'Updated')) }}</span>
                                    @if($loop->first)
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-md bg-teal-600 text-white">
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

        <!-- Right: Summary & Actions -->
        <aside class="space-y-6">

            <!-- Route Map Card -->
            <section class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <i class="fas fa-map-location-dot text-teal-600"></i> Nepal Route Map
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono">Highway Fleet</span>
                </div>

                <div class="rounded-2xl overflow-hidden border border-slate-200 relative">
                    <div id="domesticRouteMap" class="w-full h-56 z-0"></div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                    <span>{{ $shipment->sender_city ?: 'Kathmandu' }}</span>
                    <span>&rarr;</span>
                    <span>Highway Transit</span>
                    <span>&rarr;</span>
                    <span>{{ $shipment->receiver_city ?: 'Depot' }}</span>
                </div>
            </section>

            <!-- Consignment Manifest Details -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center justify-between">
                    <span>Manifest Details</span>
                    <span class="text-xs text-teal-700 font-mono font-bold">{{ $shipment->tracking_number }}</span>
                </h3>

                <dl class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Tracking Number</dt>
                        <dd class="font-mono font-bold text-slate-900">{{ $shipment->tracking_number }}</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Origin Hub</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->sender_city ?: 'Kathmandu Central Sorting Hub' }}</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Destination Hub</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->receiver_city ?: 'District Depot' }}</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Target Ward</dt>
                        <dd class="font-medium text-slate-800">Ward {{ $shipment->receiver_ward ?: '1' }}, {{ $shipment->receiver_zone }}</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Total Weight</dt>
                        <dd class="font-bold text-slate-900">{{ number_format($shipment->weight ?? 1, 2) }} kg</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Booking Date</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <div class="pt-2">
                    <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs">
                        <i class="fas fa-print"></i> <span>Print Waybill (A4)</span>
                    </a>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2 leading-relaxed">
                    <i class="fas fa-shield-halved text-teal-600 text-xs shrink-0 mt-0.5"></i>
                    <span>In accordance with data protection guidelines, recipient phone numbers and street addresses are protected.</span>
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

    const createMarkerIcon = (color, label) => L.divIcon({
        className: 'custom-dom-marker',
        html: `<div style="background-color: ${color}; width: 24px; height: 24px; border-radius: 6px; border: 2px solid white; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 9px; font-family: monospace; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">${label}</div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    const m1 = L.marker([origin.lat, origin.lng], { icon: createMarkerIcon('#2563eb', 'ORG') }).addTo(map);
    const m2 = L.marker([dest.lat, dest.lng], { icon: createMarkerIcon('#059669', 'DST') }).addTo(map);

    const poly = L.polyline([[origin.lat, origin.lng], [dest.lat, dest.lng]], {
        color: '#0d9488',
        weight: 3,
        dashArray: '6, 8',
    }).addTo(map);

    const group = new L.featureGroup([m1, m2, poly]);
    map.fitBounds(group.getBounds().pad(0.3));
});
</script>
@endpush
@endsection
