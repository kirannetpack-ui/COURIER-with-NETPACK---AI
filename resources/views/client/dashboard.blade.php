@extends('layouts.app')

@section('title', 'Client Portal & Dashboard - COURIER with NETPACK')
@section('page-title', 'Client Portal')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-2xl p-6 text-white shadow-sm border border-teal-900/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Unified Client Portal
                    </span>
                    <span class="text-xs text-slate-400">&bull; Verified Logistics Account</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight flex items-center gap-2">
                    <span>Welcome back, {{ Auth::user()->name }}!</span>
                </h1>
                <p class="text-xs text-slate-300 mt-1 max-w-xl">
                    Manage pickup inquiries, calculate real-time courier tariffs, track live consignments via GPS radar, and view proof-of-delivery receipts.
                </p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('shipments.create') }}" 
                   class="px-4 py-2.5 bg-gradient-to-r from-teal-400 to-emerald-400 hover:from-teal-300 hover:to-emerald-300 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fas fa-box-archive"></i>
                    <span>Create Shipment & Pickup</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('client.history', ['filter' => 'all']) }}" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 hover:border-teal-500/40 transition block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Consignments</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalShipments) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('client.history', ['filter' => 'active']) }}" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 hover:border-blue-500/40 transition block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">In Transit</p>
                    <p class="text-2xl font-black text-blue-600 mt-1">{{ number_format($inTransit) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fas fa-truck-fast"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('client.history', ['filter' => 'delivered']) }}" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 hover:border-emerald-500/40 transition block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Delivered</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($delivered) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('shipments.create', ['tab' => 'queue']) }}" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 hover:border-amber-500/40 transition block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Pickups</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ number_format($pendingInquiries) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fas fa-truck-pickup"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- 4 Direct Action Shortcuts -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('shipments.create') }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200/80 hover:border-teal-500/50 hover:shadow-md transition flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg group-hover:scale-110 transition flex-shrink-0">
                <i class="fas fa-box-archive"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs font-bold text-slate-900 group-hover:text-teal-700 transition">Create Shipment</h3>
                <p class="text-[11px] text-slate-500 mt-0.5 truncate">Book full domestic / intl consignment</p>
            </div>
            <i class="fas fa-arrow-right text-slate-300 group-hover:text-teal-600 group-hover:translate-x-1 transition ml-auto text-xs"></i>
        </a>

        <a href="{{ route('shipments.create', ['tab' => 'queue']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200/80 hover:border-sky-500/50 hover:shadow-md transition flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg group-hover:scale-110 transition flex-shrink-0">
                <i class="fas fa-boxes-packing"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs font-bold text-slate-900 group-hover:text-sky-700 transition">Dispatches Queue & Shipment Inquiries</h3>
                <p class="text-[11px] text-slate-500 mt-0.5 truncate">Live courier pickups & intake status</p>
            </div>
            <i class="fas fa-arrow-right text-slate-300 group-hover:text-sky-600 group-hover:translate-x-1 transition ml-auto text-xs"></i>
        </a>

        <a href="{{ route('rates.inquiry') }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200/80 hover:border-amber-500/50 hover:shadow-md transition flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition flex-shrink-0">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs font-bold text-slate-900 group-hover:text-amber-700 transition">Rate Calculator</h3>
                <p class="text-[11px] text-slate-500 mt-0.5 truncate">Instant freight quote & tariff check</p>
            </div>
            <i class="fas fa-arrow-right text-slate-300 group-hover:text-amber-600 group-hover:translate-x-1 transition ml-auto text-xs"></i>
        </a>

        <a href="{{ route('client.history') }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200/80 hover:border-emerald-500/50 hover:shadow-md transition flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition flex-shrink-0">
                <i class="fas fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition">History & Radar</h3>
                <p class="text-[11px] text-slate-500 mt-0.5 truncate">Live tracking & HAWB copies</p>
            </div>
            <i class="fas fa-arrow-right text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-1 transition ml-auto text-xs"></i>
        </a>
    </div>

    <!-- ============================================================= -->
    <!-- ACTIVE SHIPMENT LIVE TRACKING RADAR (ALWAYS VISIBLE) -->
    <!-- ============================================================= -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <!-- Section Header -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-black uppercase tracking-wider text-white">Active Consignment Live Tracking Radar</h2>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            REAL-TIME TELEMETRY
                        </span>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">Live status, movement telemetry, and milestone tracking for your ongoing shipments.</p>
                </div>
            </div>

            @if(!empty($latestActiveShipment))
                <a href="{{ route('tracking.show', $latestActiveShipment->tracking_number) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-black text-xs uppercase tracking-wider transition shadow-sm flex-shrink-0">
                    <i class="fas fa-satellite-dish"></i>
                    <span>Open Full Tracking Page</span>
                    <i class="fas fa-arrow-right text-[11px]"></i>
                </a>
            @else
                <a href="{{ route('shipments.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs uppercase tracking-wider transition flex-shrink-0">
                    <i class="fas fa-plus"></i>
                    <span>Book New Consignment</span>
                </a>
            @endif
        </div>

        @if(!empty($latestActiveShipment))
            <div class="p-6 space-y-6">
                <!-- Top Consignment Summary Row -->
                <a href="{{ route('tracking.show', $latestActiveShipment->tracking_number) }}" 
                   class="block p-4 rounded-xl bg-slate-50 hover:bg-teal-50/40 border border-slate-200/80 hover:border-teal-500/50 transition group">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="font-mono text-base sm:text-lg font-black text-slate-900 group-hover:text-teal-700 flex items-center gap-2">
                                    <i class="fas fa-barcode text-teal-600"></i>
                                    {{ $latestActiveShipment->tracking_number }}
                                </span>
                                @if($latestActiveShipment->hawb_number)
                                    <span class="px-2 py-0.5 rounded bg-slate-200/80 text-slate-700 text-xs font-mono font-semibold">
                                        HAWB: {{ $latestActiveShipment->hawb_number }}
                                    </span>
                                @endif
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                                    {{ ucwords(str_replace('_', ' ', $latestActiveShipment->service_type ?? 'Standard Express')) }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 flex items-center gap-2">
                                <span class="font-medium text-slate-700">{{ $latestActiveShipment->origin ?? ($latestActiveShipment->sender_city ?? 'Kathmandu Hub') }}</span>
                                <i class="fas fa-arrow-right-long text-teal-600 text-[10px]"></i>
                                <span class="font-bold text-slate-900">{{ $latestActiveShipment->destination ?? ($latestActiveShipment->receiver_city ?? 'Destination') }}</span>
                                @if($latestActiveShipment->receiver_name)
                                    <span class="text-slate-400">&bull; Consignee: {{ $latestActiveShipment->receiver_name }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                @php
                                    $st = strtolower($latestActiveShipment->status ?? 'pending');
                                    $statusBadgeClass = match($st) {
                                        'in_transit' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'out_for_delivery' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'picked_up' => 'bg-teal-100 text-teal-800 border-teal-200',
                                        'manifested', 'created', 'confirmed' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border {{ $statusBadgeClass }}">
                                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                    {{ str_replace('_', ' ', $latestActiveShipment->status) }}
                                </span>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Updated {{ $latestActiveShipment->updated_at ? $latestActiveShipment->updated_at->diffForHumans() : 'Recently' }}
                                </p>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-700 transition flex-shrink-0">
                                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 5-Step Visual Milestone Stepper -->
                @php
                    $curStatus = strtolower($latestActiveShipment->status ?? 'pending');
                    $stepIndex = 1;
                    if (in_array($curStatus, ['picked_up', 'arrived_at_hub', 'sorted'])) {
                        $stepIndex = 2;
                    } elseif (in_array($curStatus, ['in_transit', 'customs_cleared', 'departed_hub', 'linehaul'])) {
                        $stepIndex = 3;
                    } elseif (in_array($curStatus, ['out_for_delivery', 'with_rider'])) {
                        $stepIndex = 4;
                    } elseif (in_array($curStatus, ['delivered'])) {
                        $stepIndex = 5;
                    }
                @endphp

                <div class="relative px-2">
                    <div class="grid grid-cols-5 gap-2 text-center relative">
                        <div class="absolute top-4 left-6 right-6 h-1 bg-slate-200 -z-0">
                            <div class="h-1 bg-gradient-to-r from-teal-500 to-blue-600 transition-all duration-500"
                                 style="width: {{ (($stepIndex - 1) / 4) * 100 }}%;"></div>
                        </div>

                        <!-- Step 1: Booked -->
                        <div class="flex flex-col items-center z-10">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-xs {{ $stepIndex >= 1 ? 'bg-teal-600 text-white ring-4 ring-teal-100' : 'bg-slate-200 text-slate-500' }}">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 mt-2">Booked</p>
                        </div>

                        <!-- Step 2: Picked Up -->
                        <div class="flex flex-col items-center z-10">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-xs {{ $stepIndex >= 2 ? 'bg-teal-600 text-white ring-4 ring-teal-100' : 'bg-slate-200 text-slate-500' }}">
                                <i class="fas fa-box"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 mt-2">Picked Up</p>
                        </div>

                        <!-- Step 3: In Transit -->
                        <div class="flex flex-col items-center z-10">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-xs {{ $stepIndex >= 3 ? 'bg-blue-600 text-white ring-4 ring-blue-100 animate-pulse' : 'bg-slate-200 text-slate-500' }}">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 mt-2">In Transit</p>
                        </div>

                        <!-- Step 4: Out for Delivery -->
                        <div class="flex flex-col items-center z-10">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-xs {{ $stepIndex >= 4 ? 'bg-amber-600 text-white ring-4 ring-amber-100' : 'bg-slate-200 text-slate-500' }}">
                                <i class="fas fa-motorcycle"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 mt-2">Out for Delivery</p>
                        </div>

                        <!-- Step 5: Delivered -->
                        <div class="flex flex-col items-center z-10">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-xs {{ $stepIndex >= 5 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500' }}">
                                <i class="fas fa-circle-check"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 mt-2">Delivered</p>
                        </div>
                    </div>
                </div>

                <!-- Telemetry Info Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-slate-100 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider flex items-center gap-1">
                            <i class="fas fa-location-dot text-teal-600"></i> Current Location
                        </span>
                        <p class="font-bold text-slate-900 mt-1 text-xs">
                            {{ $latestActiveShipment->current_location ?? ($latestActiveShipment->origin ?? 'Kathmandu Gateway') }}
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                        <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider flex items-center gap-1">
                            <i class="fas fa-stopwatch text-blue-600"></i> Estimated Delivery
                        </span>
                        <p class="font-bold text-slate-900 mt-1 text-xs">
                            {{ $latestActiveShipment->estimated_delivery ? \Carbon\Carbon::parse($latestActiveShipment->estimated_delivery)->format('M d, Y (h:i A)') : 'Standard Corridor (24-48 hrs)' }}
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider flex items-center gap-1">
                                <i class="fas fa-scale-balanced text-amber-600"></i> Chargeable Weight
                            </span>
                            <p class="font-bold text-slate-900 mt-1 text-xs font-mono">
                                {{ number_format($latestActiveShipment->chargeable_weight ?: $latestActiveShipment->actual_weight ?: 1.0, 1) }} KG
                            </p>
                        </div>
                        <a href="{{ route('tracking.show', $latestActiveShipment->tracking_number) }}" 
                           class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-bold text-[11px] transition shadow-xs">
                            Track &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Standby State -->
            <div class="p-8 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fas fa-circle-check"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">All Consignments Up to Date</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        You have no active shipments moving in transit right now. Book a new parcel pickup or check completed shipments.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <a href="{{ route('shipments.create') }}" 
                       class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fas fa-plus"></i>
                        <span>Book Shipment & Pickup</span>
                    </a>
                    <a href="{{ route('client.history', ['filter' => 'delivered']) }}" 
                       class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        View Delivered History
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Bottom Row: Recent Consignments & Profile Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Shipments List -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fas fa-boxes text-teal-600"></i>
                    <span>Recent Consignments</span>
                </h3>
                <a href="{{ route('client.history') }}" class="text-xs font-bold text-teal-700 hover:underline">
                    View Full History &rarr;
                </a>
            </div>

            @if($recentShipments->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($recentShipments as $shipment)
                        <div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition px-2 rounded-lg">
                            <div>
                                <a href="{{ route('tracking.show', $shipment->tracking_number) }}"
                                   class="font-mono font-bold text-xs text-slate-900 hover:text-teal-700 flex items-center gap-1.5">
                                    <span>{{ $shipment->tracking_number }}</span>
                                    <i class="fas fa-arrow-up-right-from-square text-[9px] text-teal-600"></i>
                                </a>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $shipment->destination ?? 'Destination' }} &bull; {{ $shipment->service_type ?? 'Standard' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $shipment->status ?? 'pending') }}
                                </span>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $shipment->created_at ? $shipment->created_at->diffForHumans() : '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-400">
                    <i class="fas fa-inbox text-2xl mb-1.5 block"></i>
                    <p class="text-xs">No consignments recorded yet.</p>
                </div>
            @endif
        </div>

        <!-- Client Account & Address Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-slate-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-id-card text-teal-600"></i>
                    <span>Account Profile</span>
                </h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Name:</span>
                        <span class="font-bold text-slate-800">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Email:</span>
                        <span class="font-medium text-slate-800 truncate max-w-[150px]">{{ Auth::user()->email }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Phone:</span>
                        <span class="font-medium text-slate-800 font-mono">{{ Auth::user()->phone ?? 'Not set' }}</span>
                    </div>
                    <div class="py-1.5">
                        <span class="text-slate-500 block mb-0.5">Pickup Address:</span>
                        <span class="font-medium text-slate-800 block text-[11px]">
                            {{ Auth::user()->address ?? Auth::user()->permanent_address ?? 'No address saved yet' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('profile') }}" 
                   class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-pen-to-square text-teal-600"></i>
                    <span>Manage Profile & Addresses</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
