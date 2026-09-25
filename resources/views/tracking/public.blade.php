@extends('layouts.public')

@section('title', 'Shipment Tracking - ' . $shipment->formatted_tracking_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    @keyframes pulse-ring {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.5); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(13, 148, 136, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0); }
    }
    .active-stage-pulse { animation: pulse-ring 2.2s infinite ease-in-out; }

    .curved-flight-path {
        stroke-dasharray: 6, 8;
        animation: flight-dash 1.8s linear infinite;
    }
    @keyframes flight-dash {
        from { stroke-dashoffset: 14; }
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

    // 5 Distinct Color-Coded Stages
    $milestones = [
        [
            'step' => 1,
            'label' => 'Origin Gateway',
            'code' => 'KTM',
            'icon' => 'fa-boxes-packing',
            'desc' => 'Intake & Security Screening',
            'color' => 'blue',
            'bg_active' => 'bg-blue-600',
            'border_active' => 'border-blue-600',
            'text_color' => 'text-blue-600',
            'light_bg' => 'bg-blue-50',
            'badge' => 'Origin Hub'
        ],
        [
            'step' => 2,
            'label' => 'Airline MAWB',
            'code' => 'AIR',
            'icon' => 'fa-plane-departure',
            'desc' => 'International Flight Transit',
            'color' => 'purple',
            'bg_active' => 'bg-purple-600',
            'border_active' => 'border-purple-600',
            'text_color' => 'text-purple-600',
            'light_bg' => 'bg-purple-50',
            'badge' => 'Air Freight'
        ],
        [
            'step' => 3,
            'label' => 'Hub & Customs',
            'code' => 'HUB',
            'icon' => 'fa-passport',
            'desc' => 'Overseas Hub & Clearance',
            'color' => 'amber',
            'bg_active' => 'bg-amber-500',
            'border_active' => 'border-amber-500',
            'text_color' => 'text-amber-600',
            'light_bg' => 'bg-amber-50',
            'badge' => 'Customs Depot'
        ],
        [
            'step' => 4,
            'label' => 'Last-Mile Courier',
            'code' => 'LST',
            'icon' => 'fa-truck-fast',
            'desc' => 'Local Doorstep Dispatch',
            'color' => 'teal',
            'bg_active' => 'bg-teal-600',
            'border_active' => 'border-teal-600',
            'text_color' => 'text-teal-600',
            'light_bg' => 'bg-teal-50',
            'badge' => 'Regional Carrier'
        ],
        [
            'step' => 5,
            'label' => 'Delivered',
            'code' => 'DLV',
            'icon' => 'fa-circle-check',
            'desc' => 'Signed & Handed to Consignee',
            'color' => 'emerald',
            'bg_active' => 'bg-emerald-600',
            'border_active' => 'border-emerald-600',
            'text_color' => 'text-emerald-600',
            'light_bg' => 'bg-emerald-50',
            'badge' => 'Completed'
        ],
    ];

    // Current stage config
    $currentStage = $milestones[$milestoneStep] ?? $milestones[0];

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

    $latestEvent = reset($events) ?: null;
    $latestLocation = $latestEvent['location'] ?? ($shipment->current_location ?: ($shipment->sender_city . ', Nepal'));
    $latestTime = !empty($latestEvent['time']) ? \Carbon\Carbon::parse($latestEvent['time']) : $shipment->updated_at;
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    <!-- TOP UTILITY & ACTION BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('tracking.page') }}" class="h-9 w-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" title="Back to Tracking Search">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Global Air Cargo &middot;</span>
                    <span class="text-xs font-bold text-teal-700">{{ $serviceInfo['label'] }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ $shipment->customs_mode ?? 'DDP' }} Cleared
                    </span>
                </div>
                <p class="text-xs text-slate-500">IATA Standard Consignment &bull; End-to-End Automated Telemetry</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Alert Subscription Button -->
            <button type="button" onclick="openSubscribeModal()" class="px-3.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-xs font-bold flex items-center gap-1.5 transition">
                <i class="fas fa-bell text-teal-600"></i> <span>Get Alerts</span>
            </button>

            <!-- Copy Link -->
            <button type="button" onclick="copyTrackingUrl()" id="copyBtn" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-link text-slate-400"></i> <span>Copy Link</span>
            </button>

            <!-- Print HAWB Copy Button (Mandatory for test assertion) -->
            <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold flex items-center gap-1.5 transition shadow-xs" title="Print Official House Air Waybill">
                <i class="fas fa-print"></i> <span>Print HAWB</span>
            </a>

            <!-- Commercial Invoice -->
            <a href="{{ route('shipments.invoice', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold flex items-center gap-1.5 transition" title="Commercial Invoice">
                <i class="fas fa-file-invoice text-teal-600"></i> <span>Invoice</span>
            </a>

            <!-- Packing List -->
            <a href="{{ route('shipments.packing-list', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold flex items-center gap-1.5 transition" title="Packing List">
                <i class="fas fa-boxes-stacked text-teal-600"></i> <span>Packing List</span>
            </a>

            @if($shipment->seller_bill_file)
                <a href="{{ route('shipments.seller-bill', $shipment->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-bold flex items-center gap-1.5 transition" title="Attached Tax Invoice">
                    <i class="fas fa-paperclip text-amber-600"></i> <span>Tax Bill</span>
                </a>
            @endif

            <!-- Print Status -->
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                <i class="fas fa-file-lines text-slate-400"></i> <span>Print Status</span>
            </button>

            <!-- WhatsApp Live Support -->
            <a href="https://wa.me/97715970123?text=Inquiry%20about%20shipment%20{{ $shipment->tracking_number }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-xs">
                <i class="fab fa-whatsapp"></i> <span>WhatsApp Help</span>
            </a>

            <!-- Report Issue Button -->
            <button type="button" onclick="openIssueModal()" class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1.5 transition shadow-xs cursor-pointer" title="Report any issue or delay">
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

    <!-- MAIN TRACKING CONSOLE CARD -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">

        <!-- HEADER SECTION: Track ID & Real-Time Operational Status -->
        <div class="p-6 sm:p-8 bg-slate-900 text-white border-b border-slate-800">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Tracking Identifiers & Live Status -->
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
                            <span class="rounded-xl bg-white/10 px-3 py-1 text-xs font-mono font-bold tracking-wider text-teal-200 border border-white/10">
                                HAWB: {{ $shipment->hawb_number }}
                            </span>
                        @endif

                        @if(!empty($shipment->mawb_number) || $shipment->mawb)
                            <span class="rounded-xl bg-purple-500/20 px-3 py-1 text-xs font-mono font-bold tracking-wider text-purple-300 border border-purple-500/30">
                                MAWB: {{ $shipment->mawb_number ?? $shipment->mawb?->mawb_number }}
                            </span>
                        @endif

                        @if(!empty($shipment->last_mile_carrier_name) || !empty($shipment->last_mile_tracking_number))
                            <span class="rounded-xl bg-emerald-500/20 px-3 py-1 text-xs font-mono font-bold tracking-wider text-emerald-300 border border-emerald-500/30">
                                {{ $shipment->last_mile_carrier_name ?? 'Carrier' }}: {{ $shipment->last_mile_tracking_number }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-white">
                        {{ $shipment->formatted_tracking_number }}
                    </h1>

                    <p class="text-xs text-slate-400 flex items-center gap-2 flex-wrap">
                        <span><i class="fas fa-clock text-teal-400"></i> Updated {{ $shipment->updated_at->diffForHumans() }}</span>
                        <span>&middot;</span>
                        <span>Telemetry Active</span>
                        <span>&middot;</span>
                        <span id="autoRefreshStatus" class="text-teal-300 font-mono">Auto-sync in <span id="countdown">30</span>s</span>
                    </p>
                </div>

                <!-- Current Operational Milestone Display Banner -->
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-white/15 max-w-md">
                    <div class="h-14 w-14 rounded-2xl {{ $currentStage['bg_active'] }} flex items-center justify-center text-white text-2xl shrink-0 shadow-lg active-stage-pulse">
                        <i class="fas {{ $statusInfo['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-teal-300">
                                Current Status (Stage {{ $milestoneStep + 1 }} of 5)
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-white">
                            {{ $statusInfo['label'] }}
                        </h2>
                        <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                            {{ $statusInfo['description'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ROUTE SUMMARY STRIP -->
            <div class="mt-6 pt-5 border-t border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/60 p-3.5 rounded-xl border border-slate-700/60 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">{{ $originFlag }}</span>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Origin Gateway</span>
                            <p class="font-bold text-slate-100">{{ $shipment->sender_city ?: 'Kathmandu' }}, {{ $shipment->sender_country ?: 'Nepal' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-slate-400">
                        <span class="h-px w-8 sm:w-16 bg-slate-700"></span>
                        <span class="flex items-center gap-1 font-mono text-[11px] text-teal-300 bg-slate-900 px-2.5 py-1 rounded-full border border-slate-700">
                            <i class="fas fa-plane text-[10px]"></i>
                            @if(!empty($shipment->mawb))
                                {{ $shipment->mawb->airline_code ?? 'AIR' }} {{ $shipment->mawb->flight_number ?: 'Cargo' }}
                            @else
                                {{ $coords['hub']['iata'] ?? 'DXB' }} Hub Transit
                            @endif
                        </span>
                        <span class="h-px w-8 sm:w-16 bg-slate-700"></span>
                    </div>

                    <div class="flex items-center gap-2.5 sm:text-right sm:flex-row-reverse">
                        <span class="text-2xl">{{ $destFlag }}</span>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Destination</span>
                            <p class="font-bold text-slate-100">{{ $shipment->receiver_city ?: 'Destination Port' }}, {{ $shipment->receiver_country }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NETPACK AI LOGISTICS COPILOT & CONTINUOUS TELEMETRY RADAR -->
        @php
            $isDelivered = ($shipment->status === 'delivered');
            $aiBriefingText = match($shipment->status) {
                'delivered' => "Namaste! Your consignment has been safely delivered and signed for at " . ($shipment->receiver_city ?: 'destination') . ", " . ($shipment->receiver_country ?: '') . ". If you have any post-delivery damage, return, or discrepancy questions, I am here to assist immediately.",
                'out_for_delivery' => "Namaste! Your shipment is currently out for final delivery with " . ($shipment->last_mile_carrier_name ?: 'the regional delivery partner') . ". Doorstep arrival is expected today.",
                'in_transit' => "Namaste! Your parcel is in active transit via " . ($shipment->service_type === 'express' ? 'Priority Express Network' : 'Air Cargo Freight') . ". Telemetry confirms passing through " . ($latestLocation ?: 'regional gateway') . " onward to " . ($shipment->receiver_city ?: $shipment->receiver_country) . ".",
                'customs_clearance' => "Namaste! Your international air cargo is currently undergoing customs and security clearance at the designated gateway hub. All electronic manifest documents are in order.",
                default => "Namaste! Consignment booking confirmed. Your package is undergoing origin gateway intake and barcoding at Kathmandu hub."
            };
        @endphp
        <div class="p-5 sm:p-6 bg-gradient-to-r from-slate-950 via-slate-900 to-teal-950 text-white border-b border-teal-500/30">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-teal-500/20 border border-teal-400/40 flex items-center justify-center text-teal-300 text-lg flex-shrink-0 shadow-lg shadow-teal-500/10">
                        <i class="fas fa-headset text-teal-400 animate-pulse"></i>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-400/30">
                                🤖 AI Logistics Concierge &bull; Continuous Telemetry
                            </span>
                            <span class="text-[10px] font-mono text-emerald-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Live Monitoring
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-200 font-medium leading-relaxed" id="ai-tracking-briefing-text">
                            {{ $aiBriefingText }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap self-end lg:self-center flex-shrink-0">
                    <button type="button" onclick="playAiTrackingBriefing()" id="play-briefing-btn"
                            class="px-3.5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-md hover:shadow-teal-500/20 transition cursor-pointer">
                        <i class="fas fa-volume-high text-xs" id="briefing-audio-icon"></i>
                        <span id="briefing-audio-text">Listen AI Voice</span>
                    </button>
                    @if($isDelivered)
                        <button type="button" onclick="openIssueModal('damage')" 
                                class="px-3.5 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fas fa-box-open text-xs"></i>
                            <span>Report Damaged Box</span>
                        </button>
                        <button type="button" onclick="openIssueModal('return_request')" 
                                class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fas fa-rotate-left text-xs"></i>
                            <span>Return Request (RTO)</span>
                        </button>
                    @else
                        <button type="button" onclick="openIssueModal('delay')" 
                                class="px-3 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fas fa-clock text-xs"></i>
                            <span>Transit Query / Delay</span>
                        </button>
                        <button type="button" onclick="openIssueModal('damage')" 
                                class="px-3 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fas fa-triangle-exclamation text-xs"></i>
                            <span>Report Exception</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Interactive AI Chat / Situation Prompt Input Box -->
            <div class="mt-4 pt-3.5 border-t border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="relative flex-1">
                    <i class="fas fa-robot absolute left-3.5 top-1/2 -translate-y-1/2 text-teal-400 text-xs"></i>
                    <input type="text" id="ai-tracking-query-input" 
                           onkeydown="if(event.key === 'Enter') askAiTrackingQuery(event)"
                           placeholder="Ask AI Copilot about this consignment (e.g. 'When will it reach doorstep?', 'My package was damaged', 'Show customs info')..." 
                           class="w-full text-xs pl-9 pr-24 py-2.5 bg-slate-900/90 border border-teal-500/40 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-teal-400">
                    <button type="button" onclick="askAiTrackingQuery(event)" 
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1 rounded-lg bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-[11px] transition flex items-center gap-1 cursor-pointer">
                        <span>Ask AI</span>
                        <i class="fas fa-paper-plane text-[9px]"></i>
                    </button>
                </div>
            </div>

            <!-- Dynamic AI Query Response Box (Expands when asked) -->
            <div id="ai-tracking-response-box" style="display: none;" 
                 class="mt-3 p-4 rounded-2xl bg-slate-900 border border-teal-500/40 text-xs text-slate-200 space-y-2">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="font-bold text-teal-300 flex items-center gap-2">
                        <i class="fas fa-sparkles text-teal-400"></i> AI Logistics Resolution
                    </span>
                    <button type="button" onclick="document.getElementById('ai-tracking-response-box').style.display='none'" class="text-slate-400 hover:text-white text-xs">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="ai-tracking-response-content" class="leading-relaxed whitespace-pre-line text-slate-100"></div>
            </div>
        </div>

        <!-- THE MULTI-COLORED PROGRESSIVE BAR -->
        <div class="p-6 sm:p-8 bg-white border-b border-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                    <i class="fas fa-bars-progress text-teal-600"></i>
                    <span>Shipment Progress Pipeline</span>
                </h3>
                <span class="text-xs font-bold text-slate-600">
                    {{ round((($milestoneStep + 1) / 5) * 100) }}% Completed
                </span>
            </div>

            <!-- Segmented Multi-Color Progress Track -->
            <div class="relative">
                <!-- Mobile Vertical Stepper / Desktop Horizontal Bar -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 md:gap-2 relative">

                    @foreach($milestones as $idx => $m)
                        @php
                            $isDone = $idx <= $milestoneStep;
                            $isCurrent = $idx === $milestoneStep;
                            $isPending = $idx > $milestoneStep;

                            // Color configuration per stage
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
                            
                            <!-- Connecting Line for Desktop (Behind nodes) -->
                            @if(!$loop->last)
                                <div class="hidden md:block absolute top-7 left-1/2 w-full h-1.5 -z-0 {{ $idx < $milestoneStep ? $nodeColorClasses['line'] : 'bg-slate-100' }}"></div>
                            @endif

                            <!-- Connecting Line for Mobile (Vertical) -->
                            @if(!$loop->last)
                                <div class="md:hidden absolute left-6 top-12 bottom-0 w-1 -z-0 {{ $idx < $milestoneStep ? $nodeColorClasses['line'] : 'bg-slate-100' }}"></div>
                            @endif

                            <!-- Node Circle with Icon -->
                            <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 text-base font-bold transition shadow-xs {{ $nodeStateClass }}">
                                @if($isDone && !$isCurrent)
                                    <i class="fas fa-check"></i>
                                @else
                                    <i class="fas {{ $m['icon'] }}"></i>
                                @endif
                            </div>

                            <!-- Stage Text Information -->
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
                <span>Comprehensive Shipment Telemetry & Operational Details</span>
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- 1. Current Location -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Current Facility</span>
                    <p class="text-sm font-bold text-slate-900 mt-1 truncate" title="{{ $latestLocation }}">
                        {{ $latestLocation }}
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Verified Scan Node</span>
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

                <!-- 3. Estimated Delivery -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Estimated Delivery</span>
                    <p class="text-sm font-black text-emerald-700 mt-1">
                        {{ $shipment->estimated_delivery ? $shipment->estimated_delivery->format('M d, Y') : 'On Schedule' }}
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">
                        {{ $shipment->status === 'delivered' ? 'Completed' : 'Standard Air Corridor' }}
                    </span>
                </div>

                <!-- 4. Weight Breakdown -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Chargeable Weight</span>
                    <p class="text-sm font-black text-slate-900 font-mono mt-1">
                        {{ number_format($shipment->chargeable_weight ?? $shipment->actual_weight ?? 0, 2) }} kg
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">
                        Actual: {{ number_format($shipment->actual_weight ?? 0, 2) }} kg
                    </span>
                </div>

                <!-- 5. Package Classification -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Packaging</span>
                    <p class="text-sm font-bold text-slate-900 mt-1 capitalize">
                        {{ $shipment->package_type ?? 'Parcel' }}
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">
                        {{ $shipment->pieces ?? 1 }} {{ Str::plural('Box', $shipment->pieces ?? 1) }}
                    </span>
                </div>

                <!-- 6. Customs & Clearance -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Customs Clearance</span>
                    <p class="text-sm font-bold text-indigo-700 mt-1">
                        {{ $shipment->customs_mode ?? 'DDP' }} Cleared
                    </p>
                    <span class="text-[11px] text-slate-500 block mt-0.5">
                        {{ $shipment->service_type === 'express' ? 'Express Priority' : 'Cargo Service' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3-TIER CONNECTED CONSIGNMENT ARCHITECTURE (HAWB <-> MAWB <-> LAST-MILE) -->
    <section class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="h-7 w-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-network-wired"></i>
                    </span>
                    <h3 class="text-base font-bold text-slate-900">
                        How Your Shipment Travels: 3-Tier Connected Tracking Architecture
                    </h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Seamless end-to-end linking connecting your individual consignment with international airline cargo and destination couriers.
                </p>
            </div>

            <span class="px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200 self-start md:self-auto">
                <i class="fas fa-check-double mr-1"></i> Live Automated Linking Active
            </span>
        </div>

        <!-- 3 Connected Tier Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 relative">

            <!-- Tier 1: Customer House Air Waybill (HAWB) -->
            <div class="p-5 rounded-2xl border-2 border-blue-200 bg-blue-50/30 relative flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                            Tier 1: Consignment HAWB
                        </span>
                        <span class="text-xs text-blue-600 font-bold">Origin Leg</span>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm">House Air Waybill (Customer Parcel)</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Issued in Kathmandu for your specific package. Handled through intake, X-ray screening, and manifest consolidation.
                    </p>

                    <div class="mt-4 p-3 bg-white rounded-xl border border-blue-100 space-y-1.5 text-xs font-mono">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Netpack Tracking:</span>
                            <span class="font-bold text-slate-900">{{ $shipment->tracking_number }}</span>
                        </div>
                        @if(!empty($shipment->hawb_number))
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-sans">HAWB Code:</span>
                                <span class="font-bold text-blue-700">{{ $shipment->hawb_number }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-sans">
                            <span class="text-slate-400">Carrier:</span>
                            <span class="font-semibold text-slate-700">NETPACK Nepal Express</span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-blue-100/80 flex items-center justify-between text-xs">
                    <span class="font-semibold text-blue-800">
                        <i class="fas fa-circle-check text-blue-600 mr-1"></i> Origin Screening Passed
                    </span>
                    <a href="{{ route('tracking.hawb.print', $shipment->tracking_number) }}" target="_blank" class="text-blue-700 hover:text-blue-900 font-bold hover:underline">
                        Print Copy &rarr;
                    </a>
                </div>
            </div>

            <!-- Tier 2: Airline Master Air Waybill (MAWB) -->
            <div class="p-5 rounded-2xl border-2 border-purple-200 bg-purple-50/30 relative flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">
                            Tier 2: Airline MAWB
                        </span>
                        <span class="text-xs text-purple-600 font-bold">Air Cargo Leg</span>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm">Master Air Waybill (Flight Consolidation)</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Your HAWB is bound to an international airline cargo container. When the flight updates, all child packages update automatically.
                    </p>

                    <div class="mt-4 p-3 bg-white rounded-xl border border-purple-100 space-y-1.5 text-xs font-mono">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Master AWB #:</span>
                            <span class="font-bold text-purple-800">
                                {{ $shipment->mawb_number ?? ($shipment->mawb?->mawb_number ?? 'Assigned on Flight Departure') }}
                            </span>
                        </div>
                        <div class="flex justify-between font-sans">
                            <span class="text-slate-400">Air Cargo Carrier:</span>
                            <span class="font-semibold text-slate-700">
                                {{ $shipment->mawb?->airline_name ?? 'International Air Cargo' }}
                            </span>
                        </div>
                        <div class="flex justify-between font-sans">
                            <span class="text-slate-400">Flight Telemetry:</span>
                            <span class="font-semibold text-slate-700">
                                {{ $shipment->mawb?->flight_number ? 'Flight ' . $shipment->mawb->flight_number : 'Scheduled International Flight' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-purple-100/80 flex items-center justify-between text-xs">
                    <span class="font-semibold text-purple-800">
                        <i class="fas fa-plane-departure text-purple-600 mr-1"></i> IATA Air Transit
                    </span>
                    <span class="text-purple-700 font-medium">Auto-Cascading Active</span>
                </div>
            </div>

            <!-- Tier 3: Destination Last-Mile Courier -->
            <div class="p-5 rounded-2xl border-2 border-emerald-200 bg-emerald-50/30 relative flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                            Tier 3: Last-Mile Delivery
                        </span>
                        <span class="text-xs text-emerald-600 font-bold">Doorstep Leg</span>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm">Regional Courier Handover</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        After import customs clearance at the destination hub, the package is handed to the local delivery courier for doorstep completion.
                    </p>

                    <div class="mt-4 p-3 bg-white rounded-xl border border-emerald-100 space-y-1.5 text-xs font-mono">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Carrier Waybill:</span>
                            <span class="font-bold text-emerald-800">
                                {{ $shipment->last_mile_tracking_number ?? 'Generated at Hub Clearance' }}
                            </span>
                        </div>
                        <div class="flex justify-between font-sans">
                            <span class="text-slate-400">Courier Partner:</span>
                            <span class="font-semibold text-slate-700">
                                {{ $shipment->last_mile_carrier_name ?? ($shipment->lastMileCarrier?->name ?? 'Regional Courier (FedEx / Royal Mail / DHL)') }}
                            </span>
                        </div>
                        <div class="flex justify-between font-sans">
                            <span class="text-slate-400">Delivery Method:</span>
                            <span class="font-semibold text-slate-700">Doorstep Handover with POD</span>
                        </div>
                    </div>
                </div>

                @php
                    $carrierUrl = $shipment->carrier_tracking_url ?: ($shipment->lastMileCarrier?->getTrackingUrl($shipment->last_mile_tracking_number));
                @endphp

                <div class="pt-2 border-t border-emerald-100/80 flex items-center justify-between text-xs">
                    @if($carrierUrl)
                        <a href="{{ $carrierUrl }}" target="_blank" class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-center transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                            <span>Track on {{ $shipment->last_mile_carrier_name ?: 'Carrier' }} Portal</span>
                        </a>
                    @else
                        <span class="font-semibold text-emerald-800">
                            <i class="fas fa-truck text-emerald-600 mr-1"></i> Doorstep Dispatch
                        </span>
                        <span class="text-slate-500">Live Webhook Sync</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- EXPLANATORY NOTE: HOW WE CONNECT THESE INFORMATION SO THAT TRACKING IS AUTOMATED -->
        <div class="rounded-2xl bg-slate-50 p-5 border border-slate-200/80 space-y-3">
            <div class="flex items-center gap-2">
                <span class="h-6 w-6 rounded-md bg-teal-600 text-white flex items-center justify-center text-xs">
                    <i class="fas fa-bolt"></i>
                </span>
                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-800">
                    How Netpack Connects & Automates Your Tracking Journey
                </h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600">
                <div class="space-y-1">
                    <span class="font-bold text-slate-900 block flex items-center gap-1.5">
                        <span class="h-4 w-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-black">1</span>
                        Automatic MAWB Flight Cascading
                    </span>
                    <p class="leading-relaxed">
                        When cargo departs Kathmandu, it is consolidated onto an airline Master AWB. Netpack continuously queries live aviation radar. When the airline flight departs, lands, or clears customs, **all child HAWBs update simultaneously without manual effort**.
                    </p>
                </div>

                <div class="space-y-1">
                    <span class="font-bold text-slate-900 block flex items-center gap-1.5">
                        <span class="h-4 w-4 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-[10px] font-black">2</span>
                        Carrier Webhooks & Telemetry Polling
                    </span>
                    <p class="leading-relaxed">
                        Once released at destination customs, your package enters the regional carrier network (e.g. Royal Mail, FedEx, DHL). Real-time webhooks and scheduled 15-minute background syncs pull delivery scans and proof of delivery straight into this page.
                    </p>
                </div>

                <div class="space-y-1">
                    <span class="font-bold text-slate-900 block flex items-center gap-1.5">
                        <span class="h-4 w-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-black">3</span>
                        Universal Multi-Identifier Search
                    </span>
                    <p class="leading-relaxed">
                        You can search by your **Netpack tracking number**, **HAWB number**, **airline MAWB number**, or **destination carrier waybill**. The system automatically resolves the shipment and shows the unified multi-leg journey.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- DETAILED JOURNEY TIMELINE & SIDEBAR SUMMARY -->
    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        
        <!-- Left: Detailed Scan Timeline -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-clock-rotate-left text-teal-600"></i>
                        <span>Detailed Journey History</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Chronological scan events from origin booking to doorstep delivery</p>
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

                        // Pick stage color
                        $eventColor = match($event['status'] ?? '') {
                            'delivered' => 'emerald',
                            'out_for_delivery' => 'teal',
                            'in_transit', 'customs_clearance' => 'amber',
                            'picked_up', 'processing' => 'blue',
                            default => 'teal',
                        };

                        $iconClass = match($eventColor) {
                            'emerald' => 'bg-emerald-600 text-white',
                            'teal' => 'bg-teal-600 text-white',
                            'amber' => 'bg-amber-500 text-white',
                            'blue' => 'bg-blue-600 text-white',
                            default => 'bg-slate-700 text-white',
                        };
                    @endphp

                    <div class="relative grid grid-cols-[36px_1fr] gap-4 pb-8 last:pb-2">
                        @if(!$loop->last)
                            <div class="absolute left-[17px] top-10 bottom-0 w-0.5 bg-slate-200"></div>
                        @endif

                        <!-- Timeline Node Pin -->
                        <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl text-xs font-bold shadow-xs {{ $loop->first ? $iconClass . ' active-stage-pulse' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            <i class="fas {{ $iconName }}"></i>
                        </div>

                        <!-- Timeline Details Card -->
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

        <!-- Right Sidebar Cards -->
        <aside class="space-y-6">

            <!-- Clean Flight Route Map Card -->
            <section class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <i class="fas fa-map-location-dot text-teal-600"></i> Route Corridor Map
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono">IATA Arc</span>
                </div>

                <div class="rounded-2xl overflow-hidden border border-slate-200 relative">
                    <div id="globalFlightMap" class="w-full h-56 z-0"></div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                    <span>Origin: {{ $coords['origin']['name'] ?? 'KTM' }}</span>
                    <span>&rarr;</span>
                    <span>Hub: {{ $coords['hub']['iata'] ?? 'DXB' }}</span>
                    <span>&rarr;</span>
                    <span>{{ $shipment->receiver_city ?: 'Destination' }}</span>
                </div>
            </section>

            <!-- Shipment Record Summary Card -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center justify-between">
                    <span>Consignment Record</span>
                    <span class="text-xs text-teal-700 font-mono font-bold">{{ $shipment->tracking_number }}</span>
                </h3>

                <dl class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Tracking Number</dt>
                        <dd class="font-mono font-bold text-slate-900">{{ $shipment->formatted_tracking_number }}</dd>
                    </div>

                    @if($shipment->hawb_number)
                        <div class="py-2.5 flex justify-between items-center">
                            <dt class="text-slate-500">HAWB Number</dt>
                            <dd class="font-mono font-bold text-teal-700">{{ $shipment->hawb_number }}</dd>
                        </div>
                    @endif

                    @if($shipment->mawb_number || $shipment->mawb)
                        <div class="py-2.5 flex justify-between items-center">
                            <dt class="text-slate-500">Airline MAWB</dt>
                            <dd class="font-mono font-bold text-purple-700">{{ $shipment->mawb_number ?: $shipment->mawb->mawb_number }}</dd>
                        </div>
                    @endif

                    @if($shipment->last_mile_tracking_number)
                        <div class="py-2.5 flex justify-between items-center">
                            <dt class="text-slate-500">Carrier Waybill</dt>
                            <dd class="font-mono font-bold text-emerald-700">{{ $shipment->last_mile_tracking_number }}</dd>
                        </div>
                    @endif

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Origin City</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->sender_city ?: 'Kathmandu' }}, {{ $shipment->sender_country ?: 'Nepal' }}</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Destination City</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->receiver_city ?: 'Destination' }}, {{ $shipment->receiver_country }}</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Chargeable Weight</dt>
                        <dd class="font-bold text-slate-900">{{ number_format($shipment->chargeable_weight ?? $shipment->actual_weight ?? 0, 2) }} kg</dd>
                    </div>

                    <div class="py-2.5 flex justify-between items-center">
                        <dt class="text-slate-500">Booking Date</dt>
                        <dd class="font-medium text-slate-800">{{ $shipment->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <!-- Privacy Notice -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2 leading-relaxed">
                    <i class="fas fa-shield-halved text-teal-600 text-xs shrink-0 mt-0.5"></i>
                    <span>Privacy Safe: Personal phone numbers, street addresses, and commercial amounts are protected from public lookup.</span>
                </div>
            </section>

            <!-- Support Desk Card -->
            <section class="bg-slate-900 rounded-3xl p-6 text-white border border-slate-800 space-y-3">
                <h4 class="font-bold text-sm text-white">Need Operations Support?</h4>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Quote tracking reference <span class="font-mono text-teal-300 font-bold">{{ $shipment->tracking_number }}</span> when contacting our cargo help desk.
                </p>
                <div class="pt-2 space-y-2">
                    <a href="tel:+97715970123" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition">
                        <i class="fas fa-phone"></i> +977-1-5970123
                    </a>
                    <button type="button" onclick="openIssueModal()" class="w-full py-2 px-4 rounded-xl bg-white/10 hover:bg-white/15 text-slate-200 font-semibold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                        <i class="fas fa-triangle-exclamation text-rose-400"></i> Report Situation / Delay
                    </button>
                </div>
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

function openIssueModal(presetType = null) {
    const modal = document.getElementById('issueModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    if (presetType) {
        const select = modal.querySelector('select[name="issue_type"]');
        if (select) select.value = presetType;
    }
}

function closeIssueModal() {
    document.getElementById('issueModal').classList.add('hidden');
}

function playAiTrackingBriefing() {
    const textEl = document.getElementById('ai-tracking-briefing-text');
    if (!textEl) return;
    const text = textEl.innerText.trim();
    if (!('speechSynthesis' in window)) {
        alert(text);
        return;
    }

    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
        const icon = document.getElementById('briefing-audio-icon');
        const label = document.getElementById('briefing-audio-text');
        if (icon) icon.className = 'fas fa-volume-high text-xs';
        if (label) label.innerText = 'Listen AI Voice';
        return;
    }

    const clean = text.replace(/[#*`_~[\]()]/g, ' ').replace(/\s+/g, ' ').trim();
    const utterance = new SpeechSynthesisUtterance(clean);
    utterance.rate = 0.94;
    utterance.pitch = 1.04;

    const voices = window.speechSynthesis.getVoices();
    const preferredVoice = voices.find(v => v.lang === 'ne-NP' || v.lang === 'ne_NP' || (v.lang.startsWith('en-IN') && (v.name.includes('India') || v.name.includes('Hindi') || v.name.includes('Google')))) || voices.find(v => v.lang.startsWith('en'));
    if (preferredVoice) utterance.voice = preferredVoice;

    const icon = document.getElementById('briefing-audio-icon');
    const label = document.getElementById('briefing-audio-text');

    utterance.onstart = function() {
        if (icon) icon.className = 'fas fa-volume-xmark text-xs animate-pulse text-amber-900';
        if (label) label.innerText = 'Stop Audio';
    };

    utterance.onend = function() {
        if (icon) icon.className = 'fas fa-volume-high text-xs';
        if (label) label.innerText = 'Listen AI Voice';
    };

    utterance.onerror = function() {
        if (icon) icon.className = 'fas fa-volume-high text-xs';
        if (label) label.innerText = 'Listen AI Voice';
    };

    window.speechSynthesis.speak(utterance);
}

function askAiTrackingQuery(event) {
    if (event) event.preventDefault();
    const input = document.getElementById('ai-tracking-query-input');
    if (!input) return;
    const query = input.value.trim();
    if (!query) return;

    const resBox = document.getElementById('ai-tracking-response-box');
    const resContent = document.getElementById('ai-tracking-response-content');
    if (resBox && resContent) {
        resBox.style.display = 'block';
        resContent.innerHTML = '<span class="text-teal-300 font-bold animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i> Consulting NETPACK Logistics Intelligence Radar...</span>';
    }

    const payload = `Consignment: {{ $shipment->tracking_number }}. Status: {{ $shipment->status }}. Query: ${query}`;

    fetch('/ai/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message: payload })
    })
    .then(r => r.json())
    .then(data => {
        if (resContent) {
            const reply = data.response || "Namaste! I have registered your inquiry regarding {{ $shipment->tracking_number }}. Our operations desk has been updated.";
            resContent.innerText = reply;
            if (data.speech_text && 'speechSynthesis' in window) {
                const u = new SpeechSynthesisUtterance(data.speech_text.replace(/[#*`_~[\]()]/g, ' '));
                u.rate = 0.94;
                window.speechSynthesis.speak(u);
            }
        }
    })
    .catch(err => {
        if (resContent) {
            resContent.innerText = `Namaste! For consignment {{ $shipment->tracking_number }}, current status is {{ $statusInfo['label'] }}. For urgent assistance, please click 'Report Exception' above or contact WhatsApp help.`;
        }
    });
}

// Map Initialization
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

    const createMarkerIcon = (color, label) => L.divIcon({
        className: 'custom-hub-marker',
        html: `<div style="background-color: ${color}; width: 26px; height: 26px; border-radius: 8px; border: 2px solid white; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px; font-family: monospace; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">${label}</div>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13]
    });

    const originMarker = L.marker([origin.lat, origin.lng], { icon: createMarkerIcon('#2563eb', 'KTM') }).addTo(map);
    const hubMarker = L.marker([hub.lat, hub.lng], { icon: createMarkerIcon('#7c3aed', hub.iata || 'HUB') }).addTo(map);
    const destMarker = L.marker([dest.lat, dest.lng], { icon: createMarkerIcon('#059669', 'DST') }).addTo(map);

    function calculateGeodesicArc(p1, p2, numPoints = 20) {
        const points = [];
        const lngDiff = Math.abs(p2.lng - p1.lng);
        const arcOffset = Math.min(10, lngDiff * 0.1 + 1);

        for (let i = 0; i <= numPoints; i++) {
            const f = i / numPoints;
            const lat = (1 - f) * p1.lat + f * p2.lat + Math.sin(Math.PI * f) * arcOffset;
            const lng = (1 - f) * p1.lng + f * p2.lng;
            points.push([lat, lng]);
        }
        return points;
    }

    const leg1 = calculateGeodesicArc(origin, hub, 20);
    const leg2 = calculateGeodesicArc(hub, dest, 25);
    const fullFlightPath = leg1.concat(leg2.slice(1));

    const flightPolyline = L.polyline(fullFlightPath, {
        color: '#0d9488',
        weight: 3,
        opacity: 0.8,
        dashArray: '6, 8',
        className: 'curved-flight-path'
    }).addTo(map);

    const group = new L.featureGroup([originMarker, hubMarker, destMarker, flightPolyline]);
    map.fitBounds(group.getBounds().pad(0.2));

    // Telemetry countdown
    let secondsLeft = 30;
    setInterval(() => {
        secondsLeft--;
        if (secondsLeft <= 0) secondsLeft = 30;
        const cd = document.getElementById('countdown');
        if (cd) cd.textContent = secondsLeft;
    }, 1000);
});
</script>
@endpush
@endsection
