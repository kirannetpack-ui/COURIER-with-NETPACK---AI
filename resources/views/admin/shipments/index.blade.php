@extends('layouts.app')

@section('title', 'Consignments & Shipments Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <i class="fas fa-boxes-stacked text-indigo-600"></i>
                <span>Master Consignments Registry</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Audit, inspect, and dispatch consignments across international and domestic corridors</p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.tracking.index') }}" 
               class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                <i class="fas fa-satellite-dish"></i>
                <span>Tracking & MAWB Dispatch Console</span>
            </a>
        </div>
    </div>
    
    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase font-extrabold tracking-wider border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Tracking / HAWB</th>
                        <th class="px-4 py-3">Customer / Shipper</th>
                        <th class="px-4 py-3">Route</th>
                        <th class="px-4 py-3">MAWB Allocation</th>
                        <th class="px-4 py-3">Last-Mile Courier</th>
                        <th class="px-4 py-3">Total Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3.5">
                            <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" class="font-mono font-bold text-indigo-600 hover:underline block">
                                {{ $shipment->tracking_number }}
                            </a>
                            @if($shipment->hawb_number)
                                <span class="font-mono text-[11px] text-slate-500">HAWB: {{ $shipment->hawb_number }}</span>
                            @endif
                            <span class="text-[10px] text-slate-400 block">{{ $shipment->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $shipment->customer->name ?? ($shipment->sender_name ?: 'Direct Client') }}</p>
                            @if($shipment->customer?->company_name)
                                <p class="text-[11px] text-slate-500">{{ $shipment->customer->company_name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ $shipment->sender_city ?: 'Kathmandu' }} ➔ {{ $shipment->receiver_city ?: 'Destination' }}
                            </span>
                            <span class="text-[10px] text-slate-400 block">{{ $shipment->receiver_country }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($shipment->mawb_number)
                                <span class="font-mono font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded text-[11px] inline-flex items-center gap-1">
                                    <i class="fas fa-plane text-[9px]"></i>
                                    <span>{{ $shipment->mawb_number }}</span>
                                </span>
                            @else
                                <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" class="text-[10px] font-semibold text-amber-600 hover:underline">
                                    + Assign MAWB
                                </a>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @if($shipment->last_mile_tracking_number)
                                <span class="font-bold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/30 px-2 py-0.5 rounded text-[11px] inline-flex items-center gap-1">
                                    <i class="fas fa-truck text-[9px]"></i>
                                    <span>{{ $shipment->last_mile_carrier_name ?: 'Carrier' }}</span>
                                </span>
                                <span class="font-mono text-[10px] text-slate-500 block truncate max-w-[120px]">#{{ $shipment->last_mile_tracking_number }}</span>
                            @else
                                <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" class="text-[10px] font-semibold text-teal-600 hover:underline">
                                    + Assign Courier
                                </a>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">
                            रू {{ number_format($shipment->total_amount, 2) }}
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg inline-block
                                @if($shipment->status == 'delivered') bg-emerald-100 text-emerald-800
                                @elseif($shipment->status == 'in_transit') bg-blue-100 text-blue-800
                                @elseif($shipment->status == 'out_for_delivery') bg-amber-100 text-amber-800
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.shipments.show', $shipment->id) }}" 
                                   class="h-7 w-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition" title="Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'staff'], true))
                                <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" 
                                   class="h-7 w-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 flex items-center justify-center transition" title="Tracking & MAWB Console">
                                    <i class="fas fa-satellite-dish text-xs"></i>
                                </a>
                                <button onclick="openTrackingModal('{{ $shipment->id }}', '{{ $shipment->tracking_number }}')" 
                                        class="h-7 w-7 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 flex items-center justify-center transition" title="Quick Scan">
                                    <i class="fas fa-sync-alt text-xs"></i>
                                </button>
                                @endif
                                <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank" 
                                   class="h-7 w-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center transition" title="Public Tracker">
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                            <i class="fas fa-box-open text-3xl text-slate-300 mb-2"></i>
                            <p class="font-bold text-sm">No shipments found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shipments->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>
</div>

@include('partials.tracking-update-modal')
@endsection
