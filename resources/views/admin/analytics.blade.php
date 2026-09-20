@extends('layouts.app')

@section('title', 'Operational Intelligence & Analytics - NETPACK')
@section('page-title', 'Operational Analytics & Telemetry')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header & Time Range Filter -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-6 text-white shadow-sm border border-teal-900/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Operational Intelligence Pipeline
                    </span>
                    <span class="text-xs text-slate-400">&bull; 18 Operational Telemetry Parameters</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight flex items-center gap-2.5">
                    <i class="fas fa-chart-line text-teal-400"></i>
                    <span>Logistics Telemetry & Operational Analytics</span>
                </h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Automated data collection across every shipment lifecycle: tracking origin, destination, weights, vendor fulfillment, financial margins, SLA delays, incident damage, and client dispute situations.
                </p>
            </div>

            <!-- Range Filter Form -->
            <form method="GET" action="{{ route('admin.analytics') }}" class="flex items-center gap-2 bg-slate-800/80 p-1.5 rounded-2xl border border-slate-700">
                <a href="{{ route('admin.analytics', ['range' => 'today']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $range === 'today' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    Today
                </a>
                <a href="{{ route('admin.analytics', ['range' => '7days']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $range === '7days' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    7 Days
                </a>
                <a href="{{ route('admin.analytics', ['range' => '30days']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $range === '30days' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    30 Days
                </a>
                <a href="{{ route('admin.analytics', ['range' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $range === 'all' || !$range ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:text-white' }}">
                    All Time
                </a>
            </form>
        </div>
    </div>

    <!-- 1. FINANCIAL & COMMERCIAL MARGIN KPI TILES -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Price Sold -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Total Price Sold</span>
            <div class="text-2xl font-black text-slate-900 font-mono">
                Rs. {{ number_format($totalSold, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 flex items-center gap-1">
                <i class="fas fa-receipt text-teal-600"></i>
                <span>Gross billed freight</span>
            </p>
        </div>

        <!-- Total Fulfillment Cost -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Vendor / Carrier Cost</span>
            <div class="text-2xl font-black text-slate-800 font-mono">
                Rs. {{ number_format($totalCost, 2) }}
            </div>
            <p class="text-[11px] text-slate-500 flex items-center gap-1">
                <i class="fas fa-truck-moving text-slate-400"></i>
                <span>Linehaul & feeder cost</span>
            </p>
        </div>

        <!-- Total Gross Margin -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Gross Profit Margin</span>
            <div class="text-2xl font-black text-emerald-600 font-mono">
                Rs. {{ number_format($totalMargin, 2) }}
            </div>
            <p class="text-[11px] text-emerald-700 flex items-center gap-1 font-semibold">
                <i class="fas fa-arrow-trend-up text-emerald-600"></i>
                <span>{{ $marginPercentage }}% Net margin</span>
            </p>
        </div>

        <!-- Total Consignments -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Total Shipments</span>
            <div class="text-2xl font-black text-slate-900 font-mono">
                {{ number_format($totalShipments) }}
            </div>
            <p class="text-[11px] text-teal-700 flex items-center gap-1 font-medium">
                <i class="fas fa-circle-check text-emerald-500"></i>
                <span>{{ number_format($deliveredCount) }} Delivered</span>
            </p>
        </div>

        <!-- Repeat Customer Rate -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-1 col-span-2 lg:col-span-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Repeat Purchase Rate</span>
            <div class="text-2xl font-black text-purple-700 font-mono">
                {{ $repeatRate }}%
            </div>
            <p class="text-[11px] text-purple-600 flex items-center gap-1">
                <i class="fas fa-user-check"></i>
                <span>{{ number_format($repeatCount) }} Repeat &middot; {{ number_format($newCustomerCount) }} New</span>
            </p>
        </div>
    </div>

    <!-- 2. SERVICE QUALITY, SLA DELAYS & INCIDENT METRICS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Delay Rate -->
        <div class="p-4 rounded-2xl bg-white border {{ $delayRate > 10 ? 'border-amber-300 bg-amber-50/30' : 'border-slate-200/80' }} shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Delay Rate</span>
                <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                    <i class="fas fa-clock-rotate-left"></i>
                </span>
            </div>
            <div class="text-xl font-black text-amber-900 font-mono mt-2">
                {{ $delayRate }}%
            </div>
            <p class="text-[10px] text-slate-500 mt-1">
                {{ number_format($delayedCount) }} of {{ number_format($totalShipments) }} shipments delayed
            </p>
        </div>

        <!-- Damage Rate -->
        <div class="p-4 rounded-2xl bg-white border {{ $damageRate > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80' }} shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Damage Rate</span>
                <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs">
                    <i class="fas fa-box-tissue"></i>
                </span>
            </div>
            <div class="text-xl font-black text-rose-900 font-mono mt-2">
                {{ $damageRate }}%
            </div>
            <p class="text-[10px] text-slate-500 mt-1">
                {{ number_format($damagedCount) }} cargo damage reports
            </p>
        </div>

        <!-- Return to Origin Rate -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Return (RTO) Rate</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs">
                    <i class="fas fa-rotate-left"></i>
                </span>
            </div>
            <div class="text-xl font-black text-indigo-900 font-mono mt-2">
                {{ $returnRate }}%
            </div>
            <p class="text-[10px] text-slate-500 mt-1">
                {{ number_format($returnedCount) }} returned to sender
            </p>
        </div>

        <!-- Complaint Rate -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Complaint Rate</span>
                <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs">
                    <i class="fas fa-triangle-exclamation"></i>
                </span>
            </div>
            <div class="text-xl font-black text-slate-900 font-mono mt-2">
                {{ $complaintRate }}%
            </div>
            <p class="text-[10px] text-slate-500 mt-1">
                {{ number_format($complaintCount) }} total complaints filed
            </p>
        </div>
    </div>

    <!-- 3. NETWORK BREAKDOWN: CUSTOMER ACQUISITION & DESTINATION GEOGRAPHY -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Customer Acquisition Sources -->
        <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-xs">
                        <i class="fas fa-filter-circle-dollar"></i>
                    </span>
                    <h3 class="font-extrabold text-sm text-slate-900">Acquisition Channels</h3>
                </div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Attribution</span>
            </div>

            @if(count($acquisitionBreakdown) > 0)
                <div class="space-y-3">
                    @foreach($acquisitionBreakdown as $source => $count)
                        @php
                            $sourcePct = $totalShipments > 0 ? round(($count / $totalShipments) * 100, 1) : 0;
                            $label = match($source) {
                                'direct_portal' => 'Direct Web Portal',
                                'website_organic' => 'Website / Organic Search',
                                'referral' => 'Client Referral',
                                'sales_representative' => 'Sales Executive',
                                'social_media' => 'Social Media / Ads',
                                'agent_walkin' => 'Walk-in Cargo Counter',
                                'api_integration' => 'API / ERP Integration',
                                default => ucwords(str_replace('_', ' ', $source ?: 'Direct Portal')),
                            };
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-800">{{ $label }}</span>
                                <span class="font-mono font-bold text-slate-600">{{ $count }} ({{ $sourcePct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-teal-600 h-2 rounded-full" style="width: {{ $sourcePct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 text-center py-6">No channel data recorded yet.</p>
            @endif
        </div>

        <!-- Top Destination Countries -->
        <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                        <i class="fas fa-globe"></i>
                    </span>
                    <h3 class="font-extrabold text-sm text-slate-900">Destination Countries</h3>
                </div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Volume</span>
            </div>

            @if($topDestinations->count() > 0)
                <div class="space-y-3">
                    @foreach($topDestinations as $dest)
                        @php
                            $destPct = $totalShipments > 0 ? round(($dest->total / $totalShipments) * 100, 1) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-location-dot text-teal-600 text-[10px]"></i>
                                    <span>{{ $dest->country ?: 'Nepal' }}</span>
                                </span>
                                <span class="font-mono font-bold text-slate-600">{{ $dest->total }} shipments &middot; Rs. {{ number_format($dest->revenue) }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $destPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 text-center py-6">No destination records available.</p>
            @endif
        </div>

        <!-- Vendor & Carrier Fulfillment Network -->
        <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xs">
                        <i class="fas fa-network-wired"></i>
                    </span>
                    <h3 class="font-extrabold text-sm text-slate-900">Carrier / Vendor Network</h3>
                </div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Fulfillment</span>
            </div>

            @if($vendorBreakdown->count() > 0)
                <div class="space-y-3">
                    @foreach($vendorBreakdown as $vendor)
                        @php
                            $vPct = $totalShipments > 0 ? round(($vendor->count / $totalShipments) * 100, 1) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-800">{{ $vendor->vendor_name }}</span>
                                <span class="font-mono font-bold text-slate-600">{{ $vendor->count }} (Cost: Rs. {{ number_format($vendor->total_cost) }})</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $vPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-3 bg-slate-50 rounded-xl text-xs text-slate-600">
                    <p>Internal Operations Fleet handles default local dispatch.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- 4. CLIENT REPORTED ISSUES & SITUATIONS RESOLUTION QUEUE -->
    <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-triangle-exclamation"></i>
                </span>
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900">Client Situation & Issue Reporting Log</h3>
                    <p class="text-xs text-slate-500">Live operational disputes, damage claims, transit delays, and customer feedback</p>
                </div>
            </div>
            <span class="text-xs font-mono font-black text-rose-800 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                {{ $recentIssues->count() }} Recent Issue(s)
            </span>
        </div>

        @if($recentIssues->count() > 0)
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Issue #</th>
                            <th class="py-3 px-4">Tracking Number</th>
                            <th class="py-3 px-4">Situation Category</th>
                            <th class="py-3 px-4">Client Details & Description</th>
                            <th class="py-3 px-4">Claim</th>
                            <th class="py-3 px-4">Proof</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentIssues as $issue)
                            @php
                                $badge = match($issue->status) {
                                    'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'in_review' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-amber-100 text-amber-800 border-amber-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $issue->issue_number }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('tracking.show', $issue->tracking_number) }}" class="font-mono font-bold text-teal-700 hover:underline">
                                        {{ $issue->tracking_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 font-semibold text-[11px]">
                                        {{ ucwords(str_replace('_', ' ', $issue->issue_type)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-sm">
                                    <p class="font-semibold text-slate-800">{{ $issue->contact_name ?: ($issue->customer->name ?? 'Client') }}</p>
                                    <p class="text-slate-500 line-clamp-2 text-[11px]">{{ $issue->situation_description }}</p>
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    {{ $issue->claimed_amount ? $issue->claimed_currency . ' ' . number_format($issue->claimed_amount, 2) : '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($issue->attachment_file)
                                        <a href="{{ asset('storage/' . $issue->attachment_file) }}" target="_blank" class="text-teal-600 hover:text-teal-800 font-bold flex items-center gap-1">
                                            <i class="fas fa-paperclip"></i> Proof
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $badge }}">
                                        {{ $issue->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right text-slate-400 font-mono text-[11px]">
                                    {{ $issue->created_at?->format('M d, H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-slate-400">
                <i class="fas fa-circle-check text-3xl text-emerald-500 mb-2"></i>
                <p class="text-xs font-semibold">No issues currently reported. Operational quality is at 100%.</p>
            </div>
        @endif
    </div>

    <!-- 5. 18-METRIC OPERATIONAL TELEMETRY DATA STREAM -->
    <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fas fa-table-list text-teal-600"></i>
                    <span>Live Consignment Telemetry Stream (18 Tracked Parameters)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Origin, destination, scale & volumetric weights, channel attribution, price sold vs cost, margins, transit time, and status.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs border-collapse min-w-[900px]">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Tracking / HAWB</th>
                        <th class="py-3 px-3">Origin &rarr; Dest</th>
                        <th class="py-3 px-3">Weights (Act/Vol)</th>
                        <th class="py-3 px-3">Source Channel</th>
                        <th class="py-3 px-3 text-right">Sold (Revenue)</th>
                        <th class="py-3 px-3 text-right">Cost</th>
                        <th class="py-3 px-3 text-right">Gross Margin</th>
                        <th class="py-3 px-3">Vendor / Carrier</th>
                        <th class="py-3 px-3">Customer Type</th>
                        <th class="py-3 px-3 text-center">Status / SLA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentShipments as $s)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3">
                                <a href="{{ route('tracking.show', $s->tracking_number) }}" class="font-mono font-bold text-teal-700 hover:underline">
                                    {{ $s->tracking_number }}
                                </a>
                                @if($s->hawb_number)
                                    <span class="block text-[10px] font-mono text-slate-400">HAWB: {{ $s->hawb_number }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-semibold text-slate-900">{{ $s->sender_city ?: 'Kathmandu' }}</span>
                                <span class="text-slate-400 mx-1">&rarr;</span>
                                <span class="font-semibold text-slate-900">{{ $s->destination_country ?: ($s->receiver_country ?? 'Nepal') }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $s->receiver_city }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px]">
                                <span>{{ number_format($s->actual_weight, 1) }} KG</span>
                                <span class="text-slate-400 text-[10px] block">Vol: {{ number_format($s->volumetric_weight ?: $s->actual_weight, 1) }} KG</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[10px]">
                                    {{ ucwords(str_replace('_', ' ', $s->acquisition_source ?: 'direct_portal')) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                Rs. {{ number_format($s->price_sold ?: $s->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-600">
                                Rs. {{ number_format($s->cost_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700">
                                Rs. {{ number_format($s->gross_margin, 2) }}
                                <span class="block text-[9px] text-emerald-600">{{ $s->gross_margin_percentage }}%</span>
                            </td>
                            <td class="py-3 px-3 text-[11px] text-slate-700">
                                {{ $s->vendor_name ?: ($s->shipment_type === 'international' ? 'Air Cargo Network' : 'Ground Fleet') }}
                            </td>
                            <td class="py-3 px-3">
                                @if($s->is_repeat_customer)
                                    <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[10px] font-bold">
                                        Repeat (#{{ $s->customer_shipment_sequence }})
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-medium">
                                        1st Order
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-teal-50 text-teal-800 border border-teal-200">
                                    {{ $s->status }}
                                </span>
                                @if($s->is_delayed)
                                    <span class="block text-[9px] text-amber-600 font-bold mt-0.5">Delayed</span>
                                @endif
                                @if($s->is_damaged)
                                    <span class="block text-[9px] text-rose-600 font-bold mt-0.5">Damaged</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">No shipments found for this time period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection