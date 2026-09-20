@extends('layouts.app')

@section('title', 'Tracking Operations & Dispatch Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Tracking Operations Management</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/20">
                    <i class="fas fa-satellite-dish text-lg"></i>
                </span>
                <span>Global Tracking & Waybill Dispatch Center</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage Master Air Waybills (MAWB), Last-Mile Carrier Tracking, and Client Consignment Associations</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.shipments.index') }}" 
               class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2">
                <i class="fas fa-boxes-stacked text-slate-500"></i>
                <span>Master Consignments</span>
            </a>
        </div>
    </div>

    <!-- Telemetry Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- 1. Total Active -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Active Consignments</span>
            <div class="flex items-center justify-between mt-2">
                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalActive }}</span>
                <span class="h-8 w-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fas fa-boxes-stacked"></i>
                </span>
            </div>
            <span class="text-[10px] text-slate-400">Excluding delivered/closed</span>
        </div>

        <!-- 2. Needs MAWB -->
        <a href="{{ route('admin.tracking.index', ['filter' => 'needs_mawb']) }}" 
           class="bg-white dark:bg-slate-900 rounded-2xl p-4 border {{ request('filter') === 'needs_mawb' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800' }} shadow-sm hover:border-amber-400 transition">
            <span class="text-[10px] font-bold uppercase text-amber-600 tracking-wider">Missing MAWB</span>
            <div class="flex items-center justify-between mt-2">
                <span class="text-2xl font-black text-amber-600">{{ $needsMawbCount }}</span>
                <span class="h-8 w-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fas fa-plane-slash"></i>
                </span>
            </div>
            <span class="text-[10px] text-amber-600/80 font-semibold">Requires airline flight allocation</span>
        </a>

        <!-- 3. Needs Carrier -->
        <a href="{{ route('admin.tracking.index', ['filter' => 'needs_carrier']) }}" 
           class="bg-white dark:bg-slate-900 rounded-2xl p-4 border {{ request('filter') === 'needs_carrier' ? 'border-teal-500 ring-2 ring-teal-500/20' : 'border-slate-200 dark:border-slate-800' }} shadow-sm hover:border-teal-400 transition">
            <span class="text-[10px] font-bold uppercase text-teal-600 tracking-wider">Missing Last-Mile</span>
            <div class="flex items-center justify-between mt-2">
                <span class="text-2xl font-black text-teal-600">{{ $needsCarrierCount }}</span>
                <span class="h-8 w-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm">
                    <i class="fas fa-truck-ramp-box"></i>
                </span>
            </div>
            <span class="text-[10px] text-teal-600/80 font-semibold">Requires DHL/FedEx/UPS waybill</span>
        </a>

        <!-- 4. In-Flight -->
        <a href="{{ route('admin.tracking.index', ['filter' => 'active_flight']) }}" 
           class="bg-white dark:bg-slate-900 rounded-2xl p-4 border {{ request('filter') === 'active_flight' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800' }} shadow-sm hover:border-blue-400 transition">
            <span class="text-[10px] font-bold uppercase text-blue-600 tracking-wider">In Flight Transit</span>
            <div class="flex items-center justify-between mt-2">
                <span class="text-2xl font-black text-blue-600">{{ $inFlightCount }}</span>
                <span class="h-8 w-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fas fa-plane-departure"></i>
                </span>
            </div>
            <span class="text-[10px] text-blue-600/80 font-semibold">Air cargo corridor active</span>
        </a>

        <!-- 5. Delivered -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-[10px] font-bold uppercase text-emerald-600 tracking-wider">Successfully Delivered</span>
            <div class="flex items-center justify-between mt-2">
                <span class="text-2xl font-black text-emerald-600">{{ $deliveredCount }}</span>
                <span class="h-8 w-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fas fa-circle-check"></i>
                </span>
            </div>
            <span class="text-[10px] text-emerald-600/80 font-semibold">Confirmed consignee POD</span>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <form method="GET" action="{{ route('admin.tracking.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Query -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by Tracking #, HAWB #, MAWB #, Carrier #, Receiver..."
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>
            </div>

            <!-- Client Filter -->
            <div>
                <select name="customer_id" class="w-full text-xs py-2 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    <option value="">-- All Clients --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} {{ $c->company_name ? "({$c->company_name})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter text-xs"></i>
                    <span>Apply Filter</span>
                </button>
                <a href="{{ route('admin.tracking.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                    Reset
                </a>
            </div>
        </form>

        <!-- Quick Filter Pills -->
        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
            <span class="text-slate-400 font-bold uppercase text-[10px] mr-1">Quick Views:</span>
            <a href="{{ route('admin.tracking.index') }}" 
               class="px-3 py-1 rounded-lg font-bold {{ !request('filter') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Active ({{ $totalActive }})
            </a>
            <a href="{{ route('admin.tracking.index', ['filter' => 'needs_mawb']) }}" 
               class="px-3 py-1 rounded-lg font-bold {{ request('filter') === 'needs_mawb' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                Needs MAWB ({{ $needsMawbCount }})
            </a>
            <a href="{{ route('admin.tracking.index', ['filter' => 'needs_carrier']) }}" 
               class="px-3 py-1 rounded-lg font-bold {{ request('filter') === 'needs_carrier' ? 'bg-teal-500 text-white' : 'bg-teal-50 text-teal-700 hover:bg-teal-100' }}">
                Needs Last-Mile Courier ({{ $needsCarrierCount }})
            </a>
            <a href="{{ route('admin.tracking.index', ['filter' => 'active_flight']) }}" 
               class="px-3 py-1 rounded-lg font-bold {{ request('filter') === 'active_flight' ? 'bg-blue-500 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                In Flight Corridor ({{ $inFlightCount }})
            </a>
            <a href="{{ route('admin.tracking.index', ['filter' => 'in_last_mile']) }}" 
               class="px-3 py-1 rounded-lg font-bold {{ request('filter') === 'in_last_mile' ? 'bg-purple-500 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                With Last-Mile Delivery Partner
            </a>
        </div>
    </div>

    <!-- Consignments Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Consignment & HAWB</th>
                        <th class="px-5 py-3.5">Associated Client</th>
                        <th class="px-5 py-3.5">Transit Corridor</th>
                        <th class="px-5 py-3.5">MAWB Air Cargo</th>
                        <th class="px-5 py-3.5">Last-Mile Courier</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Data Entry Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($shipments as $shipment)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <!-- Col 1: Identifiers -->
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" 
                                   class="font-mono font-black text-indigo-600 dark:text-indigo-400 hover:underline block">
                                    {{ $shipment->formatted_tracking_number ?? $shipment->tracking_number }}
                                </a>
                                @if($shipment->hawb_number)
                                    <span class="font-mono text-[11px] text-slate-500">HAWB: {{ $shipment->hawb_number }}</span>
                                @endif
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $shipment->created_at->format('M d, Y') }}</span>
                            </td>

                            <!-- Col 2: Client -->
                            <td class="px-5 py-4">
                                @if($shipment->customer)
                                    <p class="font-bold text-slate-800 dark:text-slate-200">{{ $shipment->customer->name }}</p>
                                    @if($shipment->customer->company_name)
                                        <p class="text-[11px] text-slate-500">{{ $shipment->customer->company_name }}</p>
                                    @endif
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $shipment->customer->phone ?? $shipment->customer->email }}</span>
                                @else
                                    <span class="text-slate-400 italic">Direct Walk-in</span>
                                    <p class="text-[11px] text-slate-600 dark:text-slate-400">{{ $shipment->sender_name }}</p>
                                @endif
                            </td>

                            <!-- Col 3: Corridor -->
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $shipment->sender_city ?: 'Kathmandu' }} ➔ <span class="font-bold text-slate-900 dark:text-white">{{ $shipment->receiver_city ?: 'Destination' }}</span>
                                </p>
                                <span class="text-[10px] text-slate-400">{{ $shipment->receiver_country }}</span>
                            </td>

                            <!-- Col 4: MAWB -->
                            <td class="px-5 py-4">
                                @if($shipment->mawb_number)
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-5 w-5 rounded bg-amber-500/20 text-amber-700 dark:text-amber-300 flex items-center justify-center text-[10px]">
                                            <i class="fas fa-plane"></i>
                                        </span>
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $shipment->mawb_number }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 block truncate max-w-[150px]">
                                        {{ $shipment->mawb?->airline_name ?? 'Airline Cargo' }}
                                    </span>
                                @else
                                    <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" 
                                       class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition inline-flex items-center gap-1">
                                        <i class="fas fa-plus-circle text-[9px]"></i>
                                        <span>Assign MAWB</span>
                                    </a>
                                @endif
                            </td>

                            <!-- Col 5: Last Mile Carrier -->
                            <td class="px-5 py-4">
                                @if($shipment->last_mile_tracking_number)
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-5 w-5 rounded bg-teal-500/20 text-teal-700 dark:text-teal-300 flex items-center justify-center text-[10px]">
                                            <i class="fas fa-truck"></i>
                                        </span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $shipment->last_mile_carrier_name ?: 'Carrier' }}</span>
                                    </div>
                                    <span class="font-mono text-[10px] text-slate-500 block">#{{ $shipment->last_mile_tracking_number }}</span>
                                @else
                                    <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" 
                                       class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200 hover:bg-teal-100 transition inline-flex items-center gap-1">
                                        <i class="fas fa-plus-circle text-[9px]"></i>
                                        <span>Assign Courier</span>
                                    </a>
                                @endif
                            </td>

                            <!-- Col 6: Status -->
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold inline-flex items-center gap-1
                                    @if($shipment->status === 'delivered') bg-emerald-100 text-emerald-800
                                    @elseif($shipment->status === 'in_transit') bg-blue-100 text-blue-800
                                    @elseif($shipment->status === 'out_for_delivery') bg-amber-100 text-amber-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    <span>{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                                </span>
                            </td>

                            <!-- Col 7: Actions -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" 
                                       class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                        <span>Tracking Console</span>
                                    </a>

                                    <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank"
                                       class="h-8 w-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" title="Public Radar">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <i class="fas fa-satellite-dish text-3xl text-slate-300"></i>
                                    <p class="font-bold text-sm">No shipments found</p>
                                    <p class="text-xs text-slate-400">Try changing your search query or quick filter views.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
