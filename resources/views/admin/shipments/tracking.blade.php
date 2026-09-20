@extends('layouts.app')

@section('title', 'Operations Tracking & Dispatch Console - ' . ($shipment->tracking_number ?? 'Shipment'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Top Breadcrumb & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.shipments.index') }}" class="hover:text-slate-800 transition">Consignments</a>
                <span>/</span>
                <a href="{{ route('admin.tracking.index') }}" class="hover:text-slate-800 transition">Tracking Operations</a>
                <span>/</span>
                <span class="text-slate-800 font-mono font-bold">{{ $shipment->formatted_tracking_number ?? $shipment->tracking_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/20">
                    <i class="fas fa-satellite-dish text-lg"></i>
                </span>
                <span>Operations Tracking & Waybill Dispatch Console</span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.shipments.show', $shipment->id) }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2">
                <i class="fas fa-eye text-slate-500"></i>
                <span>Shipment Details</span>
            </a>

            <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank"
               class="px-3.5 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 border border-emerald-500/30 text-xs font-bold transition flex items-center gap-2">
                <i class="fas fa-radar text-emerald-600"></i>
                <span>Public Live Animated Tracker</span>
                <i class="fas fa-external-link-alt text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <p class="font-bold text-sm">Success</p>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <p class="font-bold text-sm">Action Notice</p>
                    <p class="text-xs text-rose-700">{{ session('error') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Consignment Header Specs Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative z-10">
            <!-- Col 1: Identification -->
            <div class="space-y-2 border-b md:border-b-0 md:border-r border-slate-800 pb-4 md:pb-0 md:pr-4">
                <span class="text-[10px] uppercase font-bold tracking-wider text-indigo-400">Master Waybill Identifiers</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-lg font-black tracking-wide text-white">{{ $shipment->formatted_tracking_number ?? $shipment->tracking_number }}</span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $shipment->tracking_number }}'); alert('Tracking Number Copied!');" 
                            class="text-slate-400 hover:text-white text-xs" title="Copy">
                        <i class="far fa-copy"></i>
                    </button>
                </div>
                @if($shipment->hawb_number)
                    <p class="text-xs font-mono text-slate-300">
                        <span class="text-slate-500">HAWB:</span> {{ $shipment->hawb_number }}
                    </p>
                @endif
                <div class="pt-1">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold inline-flex items-center gap-1.5
                        @if($shipment->status === 'delivered') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                        @elseif($shipment->status === 'in_transit') bg-blue-500/20 text-blue-300 border border-blue-500/30
                        @elseif($shipment->status === 'out_for_delivery') bg-amber-500/20 text-amber-300 border border-amber-500/30
                        @else bg-purple-500/20 text-purple-300 border border-purple-500/30 @endif">
                        <span class="h-1.5 w-1.5 rounded-full bg-current animate-pulse"></span>
                        <span>{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                    </span>
                </div>
            </div>

            <!-- Col 2: Route & Flight Leg -->
            <div class="space-y-2 border-b md:border-b-0 md:border-r border-slate-800 pb-4 md:pb-0 md:pr-4">
                <span class="text-[10px] uppercase font-bold tracking-wider text-teal-400">Air Cargo Transit Corridor</span>
                <div class="flex items-center gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400">Origin</p>
                        <p class="text-sm font-black text-slate-100">{{ $shipment->sender_city ?: 'Kathmandu' }}, Nepal</p>
                        <span class="text-[10px] text-teal-400 font-mono">TIA (KTM)</span>
                    </div>
                    <i class="fas fa-plane text-teal-400 text-sm"></i>
                    <div>
                        <p class="text-xs font-bold text-slate-400">Destination</p>
                        <p class="text-sm font-black text-slate-100">{{ $shipment->receiver_city ?: 'Global' }}, {{ $shipment->receiver_country }}</p>
                        <span class="text-[10px] text-teal-400 font-mono">{{ $shipment->destination_country ?? $shipment->receiver_country }}</span>
                    </div>
                </div>
            </div>

            <!-- Col 3: MAWB Status -->
            <div class="space-y-2 border-b md:border-b-0 md:border-r border-slate-800 pb-4 md:pb-0 md:pr-4">
                <span class="text-[10px] uppercase font-bold tracking-wider text-amber-400">MAWB Air Cargo Status</span>
                @if($shipment->mawb_number)
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-md bg-amber-500/20 text-amber-300 flex items-center justify-center text-xs">
                            <i class="fas fa-plane-departure"></i>
                        </span>
                        <span class="font-mono font-bold text-sm text-amber-200">{{ $shipment->mawb_number }}</span>
                    </div>
                    <p class="text-xs text-slate-300 truncate">
                        {{ $shipment->mawb?->airline_name ?: ($mawbAirlineInfo['name'] ?? 'Scheduled Cargo Carrier') }}
                    </p>
                    @if($shipment->mawb?->flight_number)
                        <span class="text-[10px] font-mono bg-slate-800 text-slate-300 px-2 py-0.5 rounded">
                            Flight: {{ $shipment->mawb->flight_number }}
                        </span>
                    @endif
                @else
                    <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-2">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>No MAWB Assigned Yet</span>
                    </div>
                @endif
            </div>

            <!-- Col 4: Last Mile Carrier Status -->
            <div class="space-y-2">
                <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">Last-Mile Carrier Status</span>
                @if($shipment->last_mile_tracking_number)
                    <div class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-md bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-xs">
                            <i class="fas fa-truck"></i>
                        </span>
                        <span class="font-bold text-sm text-emerald-200">{{ $shipment->last_mile_carrier_name ?: 'Courier Partner' }}</span>
                    </div>
                    <p class="font-mono text-xs text-slate-300 truncate">#{{ $shipment->last_mile_tracking_number }}</p>
                    @if($carrierPreviewUrl)
                        <a href="{{ $carrierPreviewUrl }}" target="_blank" 
                           class="inline-flex items-center gap-1 text-[10px] font-bold text-teal-400 hover:text-teal-300 underline">
                            <span>Open {{ $shipment->last_mile_carrier_name }} Portal</span>
                            <i class="fas fa-external-link-alt text-[9px]"></i>
                        </a>
                    @endif
                @else
                    <div class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        <span>No Last-Mile Tracking Yet</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Synchronize Actions Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-ping"></span>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Automated Background Engine: Active</span>
            <span class="text-[10px] text-slate-400">| Scheduled sync runs every 15 mins</span>
        </div>

        <div class="flex items-center gap-2">
            @if($shipment->mawb)
                <form action="{{ route('admin.shipments.sync-mawb', $shipment->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fas fa-plane-departure text-xs"></i>
                        <span>Sync Flight Radar Now</span>
                    </button>
                </form>
            @endif

            @if($shipment->last_mile_tracking_number)
                <form action="{{ route('admin.shipments.sync-carrier', $shipment->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fas fa-rotate text-xs"></i>
                        <span>Sync Carrier Checkpoints Now</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Main Data Entry Form -->
    <form action="{{ route('admin.shipments.update-tracking', $shipment->id) }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left 2 Cols: Form Controls -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. Client & Shipper Association -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                                1
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Client & Consignment Association</h3>
                                <p class="text-xs text-slate-500">Associate this shipment with a corporate client account or update addresses</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 px-2.5 py-1 rounded-lg">Account Mapping</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Client Dropdown -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                                Associated Client Account <span class="text-slate-400 font-normal">(Shipper / Corporate Client)</span>
                            </label>
                            <select name="customer_id" id="customer_id" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- No Client Account Assigned (Direct Walk-in) --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ (old('customer_id', $shipment->customer_id) == $client->id) ? 'selected' : '' }}>
                                        {{ $client->name }} ({{ $client->email }}) {{ $client->company_name ? '- ' . $client->company_name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sender Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Sender / Exporter Name</label>
                            <input type="text" name="sender_name" value="{{ old('sender_name', $shipment->sender_name) }}"
                                   class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>

                        <!-- Sender Phone -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Sender Phone</label>
                            <input type="text" name="sender_phone" value="{{ old('sender_phone', $shipment->sender_phone) }}"
                                   class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>

                        <!-- Receiver Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Receiver / Consignee Name</label>
                            <input type="text" name="receiver_name" value="{{ old('receiver_name', $shipment->receiver_name) }}"
                                   class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>

                        <!-- Receiver Phone -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Receiver Contact / Phone</label>
                            <input type="text" name="receiver_phone" value="{{ old('receiver_phone', $shipment->receiver_phone) }}"
                                   class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>

                        <!-- Receiver City & Country -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Destination City</label>
                            <input type="text" name="receiver_city" value="{{ old('receiver_city', $shipment->receiver_city) }}"
                                   class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Destination Country</label>
                            <input type="text" name="receiver_country" value="{{ old('receiver_country', $shipment->receiver_country) }}"
                                   class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- 2. Master Air Waybill (MAWB) Air Cargo Allocation -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4"
                     x-data="{ mode: '{{ $shipment->mawb_id ? 'existing' : ($shipment->mawb_number ? 'new' : 'existing') }}' }">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-8 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                                2
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Master Air Waybill (MAWB) Airline Allocation</h3>
                                <p class="text-xs text-slate-500">Assign air cargo flight space departing Kathmandu (KTM) on international airlines</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 px-2.5 py-1 rounded-lg">Flight Radar Integration</span>
                    </div>

                    <!-- Mode Toggle -->
                    <div class="flex gap-4 p-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80">
                        <button type="button" @click="mode = 'existing'" 
                                :class="mode === 'existing' ? 'bg-white dark:bg-slate-900 font-bold shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400'"
                                class="flex-1 py-2 rounded-lg text-xs transition flex items-center justify-center gap-2">
                            <i class="fas fa-list-check"></i>
                            <span>Select from Active MAWB Inventory</span>
                        </button>

                        <button type="button" @click="mode = 'new'" 
                                :class="mode === 'new' ? 'bg-white dark:bg-slate-900 font-bold shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400'"
                                class="flex-1 py-2 rounded-lg text-xs transition flex items-center justify-center gap-2">
                            <i class="fas fa-plus-circle"></i>
                            <span>Enter New / Custom MAWB</span>
                        </button>

                        <button type="button" @click="mode = 'none'" 
                                :class="mode === 'none' ? 'bg-white dark:bg-slate-900 font-bold shadow-sm text-rose-600' : 'text-slate-600 dark:text-slate-400'"
                                class="py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-1">
                            <i class="fas fa-ban"></i>
                            <span>None</span>
                        </button>
                    </div>
                    <input type="hidden" name="mawb_selection_mode" :value="mode">

                    <!-- Option A: Existing MAWB Dropdown -->
                    <div x-show="mode === 'existing'" class="space-y-3">
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">
                            Select Master Air Waybill (Scheduled Flights)
                        </label>
                        <select name="existing_mawb_id" class="w-full text-xs font-mono px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <option value="">-- Choose Active MAWB Inventory --</option>
                            @foreach($availableMawbs as $m)
                                <option value="{{ $m->id }}" {{ (old('existing_mawb_id', $shipment->mawb_id) == $m->id) ? 'selected' : '' }}>
                                    {{ $m->mawb_number }} | {{ $m->airline_name }} ({{ $m->flight_number }}) | {{ $m->origin_airport }} ➔ {{ $m->destination_airport }} [{{ $m->status }}]
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500">
                            <i class="fas fa-circle-info text-amber-500 mr-1"></i>
                            Selecting an active MAWB cascades flight takeoff, en-route, and hub arrival milestones to this consignment automatically.
                        </p>
                    </div>

                    <!-- Option B: New / Custom MAWB Form -->
                    <div x-show="mode === 'new'" class="space-y-4" style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- MAWB Number -->
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                                    MAWB Number <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="new_mawb_number" id="new_mawb_number"
                                       value="{{ old('new_mawb_number', $shipment->mawb_number) }}"
                                       placeholder="e.g. 157-78192041 or 176-54819203"
                                       oninput="detectAirlinePrefix(this.value)"
                                       class="w-full font-mono text-xs font-bold px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                <span id="airlineDetectionBadge" class="hidden mt-1.5 text-[11px] font-bold px-2 py-0.5 rounded inline-flex items-center gap-1.5"></span>
                            </div>

                            <!-- Airline Name -->
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Airline Carrier Name</label>
                                <input type="text" name="airline_name" id="airline_name"
                                       value="{{ old('airline_name', $shipment->mawb?->airline_name) }}"
                                       placeholder="e.g. Qatar Airways Cargo, Emirates, flydubai..."
                                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>

                            <!-- Flight Number -->
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Flight Number</label>
                                <input type="text" name="flight_number" id="flight_number"
                                       value="{{ old('flight_number', $shipment->mawb?->flight_number) }}"
                                       placeholder="e.g. QR651, EK2355, FZ576..."
                                       class="w-full font-mono text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>

                            <!-- Flight Date -->
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Scheduled Flight Departure</label>
                                <input type="datetime-local" name="flight_date"
                                       value="{{ old('flight_date', $shipment->mawb?->flight_date ? \Carbon\Carbon::parse($shipment->mawb->flight_date)->format('Y-m-d\TH:i') : date('Y-m-d\T18:15')) }}"
                                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>

                            <!-- Departure Airport -->
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Departure Airport</label>
                                <input type="text" name="origin_airport"
                                       value="{{ old('origin_airport', $shipment->mawb?->origin_airport ?: 'KTM - Tribhuvan International') }}"
                                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>

                            <!-- Destination Airport -->
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Destination Gateway Hub</label>
                                <input type="text" name="destination_airport"
                                       value="{{ old('destination_airport', $shipment->mawb?->destination_airport ?: 'DOH - Doha International Hub') }}"
                                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Last-Mile Delivery Carrier Association -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-8 rounded-xl bg-teal-100 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-sm">
                                3
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Last-Mile Courier & Forwarding Tracking</h3>
                                <p class="text-xs text-slate-500">Associate final-mile delivery partners (DHL, FedEx, UPS, Aramex, Royal Mail, etc.)</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800 px-2.5 py-1 rounded-lg">17TRACK & Direct API</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Carrier Selector / Input -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                                Delivery Courier Company <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="last_mile_carrier_name" id="carrier_name_input" list="carrierPresets"
                                   value="{{ old('last_mile_carrier_name', $shipment->last_mile_carrier_name) }}"
                                   oninput="updateCarrierPortalPreview()"
                                   placeholder="DHL Express, FedEx, UPS, Aramex, Royal Mail..."
                                   class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <datalist id="carrierPresets">
                                <option value="DHL Express">
                                <option value="FedEx">
                                <option value="UPS">
                                <option value="Aramex">
                                <option value="Royal Mail">
                                <option value="USPS">
                                <option value="Australia Post">
                                <option value="DPD">
                                <option value="Purolator">
                                <option value="Canpar">
                            </datalist>
                        </div>

                        <!-- Forwarding Tracking Number -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                                Carrier Tracking / Waybill # <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="last_mile_tracking_number" id="carrier_tracking_input"
                                   value="{{ old('last_mile_tracking_number', $shipment->last_mile_tracking_number) }}"
                                   oninput="updateCarrierPortalPreview()"
                                   placeholder="e.g. 10 digits (DHL), 12 digits (FedEx), 1Z... (UPS)"
                                   class="w-full font-mono text-xs font-bold px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <span id="carrierFormatBadge" class="hidden mt-1 text-[10px] font-bold px-2 py-0.5 rounded inline-block"></span>
                        </div>
                    </div>

                    <!-- Direct Official Carrier Link Live Preview -->
                    <div id="carrierPreviewContainer" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center font-bold text-sm">
                                <i class="fas fa-link"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Consignee Official Tracking Portal Link</p>
                                <p id="carrierPreviewUrlText" class="text-[11px] font-mono text-slate-500 dark:text-slate-400 truncate max-w-md">
                                    {{ $carrierPreviewUrl ?: 'Enter carrier name and tracking number above to preview direct portal URL.' }}
                                </p>
                            </div>
                        </div>

                        <a id="carrierPreviewLink" href="{{ $carrierPreviewUrl ?: '#' }}" target="_blank" 
                           class="{{ $carrierPreviewUrl ? '' : 'hidden' }} px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                            <span>Test Link</span>
                            <i class="fas fa-external-link-alt text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- 4. Operational Milestone & Physical Scan Logger -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4"
                     x-data="{ recordScan: false }">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-8 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm">
                                4
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Record Operational Milestone Scan</h3>
                                <p class="text-xs text-slate-500">Log an exact physical checkpoint scan or lifecycle milestone with custom timestamp</p>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="record_scan" value="1" x-model="recordScan" class="w-4 h-4 text-purple-600 rounded">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Log Milestone Now</span>
                        </label>
                    </div>

                    <div x-show="recordScan" class="space-y-4" style="display: none;">
                        <!-- Milestone Selector -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                                Milestone Event <span class="text-rose-500">*</span>
                            </label>
                            <select name="milestone_event" class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                <optgroup label="Export Preparation (Nepal)">
                                    <option value="booking_done">1. Bookings Done / Consignment Received</option>
                                    <option value="packaging_completed">2. Packaging Completed & Weighed</option>
                                    <option value="export_cleared">3. Export Clearance Done (TIA Customs)</option>
                                </optgroup>
                                <optgroup label="International Linehaul & Hub">
                                    <option value="in_transit_airline">4. In Transit to Hub by Airlines (Departed KTM)</option>
                                    <option value="arrival_notice">5. Arrival Notice (At Destination Airport / Hub)</option>
                                    <option value="import_cleared">6. Import Clearance Completed (Overseas Customs)</option>
                                </optgroup>
                                <optgroup label="Last Mile & Delivery">
                                    <option value="last_mile_handover">7. Handed Over for Last Mile Delivery</option>
                                    <option value="out_for_delivery">8. Out for Delivery to Consignee</option>
                                    <option value="delivered">9. Delivered to Consignee (Signed POD)</option>
                                    <option value="delivery_attempted">10. Delivery Attempt Exception</option>
                                    <option value="customs_hold">11. Customs Review / Regulatory Hold</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Date & Time Telemetry -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Event Date</label>
                                <input type="date" name="event_date" value="{{ date('Y-m-d') }}"
                                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Event Time</label>
                                    <button type="button" onclick="document.getElementById('event_time').value = new Date().toTimeString().slice(0,5)" 
                                            class="text-[10px] text-purple-600 font-bold hover:underline">Set to Now</button>
                                </div>
                                <input type="time" name="event_time" id="event_time" value="{{ date('H:i') }}"
                                       class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <!-- Location with Presets -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                                Exact Physical Location / Hub Checkpoint <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="location" id="location_input" list="hubLocations"
                                   placeholder="e.g. Kathmandu (KTM) Export Terminal, Dubai (DXB) Cargo Village, London Heathrow (LHR)"
                                   class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <datalist id="hubLocations">
                                <option value="Kathmandu (KTM) Export Cargo Terminal, Nepal">
                                <option value="Tribhuvan International Airport Air Cargo Customs, Nepal">
                                <option value="Dubai International Cargo Gateway (DXB), UAE">
                                <option value="Hamad International Cargo Terminal (DOH), Qatar">
                                <option value="London Heathrow (LHR) Cargo Center, United Kingdom">
                                <option value="John F. Kennedy (JFK) Import Cargo Hub, New York, USA">
                                <option value="Sydney Kingsford Smith (SYD) Air Terminal, Australia">
                                <option value="Toronto Pearson (YYZ) Cargo Depot, Canada">
                                <option value="Frankfurt Airport (FRA) CargoCity, Germany">
                                <option value="Tokyo Narita (NRT) Air Cargo Hub, Japan">
                            </datalist>
                        </div>

                        <!-- Description & Notes -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Operational Remarks / Details</label>
                            <textarea name="description" rows="2"
                                      placeholder="e.g. Consignment cleared customs under EXIM declaration. Handed over to airline cargo ramp..."
                                      class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                        </div>

                        <!-- Email Notification Toggle -->
                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" name="notify_client" id="notify_client" value="1" checked class="w-4 h-4 text-purple-600 rounded">
                            <label for="notify_client" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Dispatch branded email update to shipper and recipient automatically
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit & Trigger Actions -->
                <div class="p-6 rounded-3xl bg-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="trigger_carrier_sync" id="trigger_carrier_sync" value="1" checked class="w-4 h-4 text-teal-400 rounded">
                        <label for="trigger_carrier_sync" class="text-xs font-medium text-slate-200">
                            Trigger immediate carrier API sync after saving
                        </label>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('admin.shipments.index') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold text-center transition">
                            Cancel
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-teal-600 hover:from-indigo-500 hover:to-teal-500 text-white text-xs font-black tracking-wide shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Save & Apply Tracking Updates</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right Col: Audit Trail & Historical Scans -->
            <div class="space-y-6">

                <!-- Audit Timeline Card -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-clock-rotate-left text-indigo-500"></i>
                            <span>Tracking Checkpoints ({{ count($shipment->tracking_history ?? []) }})</span>
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 font-mono">Real-time Trail</span>
                    </div>

                    <div class="relative pl-6 space-y-5 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
                        @forelse(array_reverse($shipment->tracking_history ?? []) as $event)
                            <div class="relative">
                                <!-- Marker -->
                                <span class="absolute -left-6 top-1 h-4 w-4 rounded-full border-2 border-white dark:border-slate-900 bg-indigo-600"></span>

                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-bold text-xs text-slate-800 dark:text-slate-200">
                                            {{ $event['status_label'] ?? ucfirst(str_replace('_', ' ', $event['status'] ?? 'checkpoint')) }}
                                        </p>
                                        <span class="text-[10px] font-mono text-slate-400">
                                            {{ \Carbon\Carbon::parse($event['time'] ?? now())->format('M d, H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">{{ $event['description'] ?? '' }}</p>
                                    @if(!empty($event['location']))
                                        <span class="text-[10px] text-teal-600 dark:text-teal-400 font-semibold inline-flex items-center gap-1 mt-1">
                                            <i class="fas fa-map-marker-alt text-[9px]"></i>
                                            <span>{{ $event['location'] }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">No milestones recorded yet. Use the form to record the initial checkpoint.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Guidance & Operation Rules Pill -->
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-slate-800 dark:to-slate-800/60 rounded-3xl p-5 border border-indigo-100 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 space-y-3">
                    <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-lightbulb text-amber-500"></i>
                        <span>Operational Guidance</span>
                    </h4>
                    <ul class="space-y-2 text-[11px] list-disc pl-4 text-slate-600 dark:text-slate-400">
                        <li><strong>Airline Prefix:</strong> The 3-digit prefix automatically identifies the airline (e.g. 157 = Qatar Airways, 176 = Emirates, 285 = Nepal Airlines).</li>
                        <li><strong>Last-Mile Handover:</strong> Enter the courier name (DHL, FedEx, UPS) and their waybill number to generate the direct tracking link for the consignee.</li>
                        <li><strong>Automatic Sync:</strong> The background command <code>php artisan tracking:sync-all</code> polls all active flights and carriers automatically every 15 minutes.</li>
                    </ul>
                </div>

            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
// Airline Prefix Dictionary for Live Interactive Feedback
const AIRLINE_PREFIXES = {
    '157': { name: 'Qatar Airways Cargo', code: 'QR', hub: 'DOH - Doha Hub' },
    '176': { name: 'Emirates SkyCargo', code: 'EK', hub: 'DXB - Dubai Hub' },
    '141': { name: 'flydubai Cargo', code: 'FZ', hub: 'DXB - Dubai Hub' },
    '285': { name: 'Nepal Airlines Cargo', code: 'RA', hub: 'KTM - Kathmandu' },
    '098': { name: 'Air India Cargo', code: 'AI', hub: 'DEL - Delhi Hub' },
    '618': { name: 'Singapore Airlines Cargo', code: 'SQ', hub: 'SIN - Singapore Hub' },
    '020': { name: 'Lufthansa Cargo', code: 'LH', hub: 'FRA - Frankfurt Hub' },
    '160': { name: 'Cathay Pacific Cargo', code: 'CX', hub: 'HKG - Hong Kong Hub' },
    '235': { name: 'Turkish Cargo', code: 'TK', hub: 'IST - Istanbul Hub' },
    '086': { name: 'Air New Zealand Cargo', code: 'NZ', hub: 'AKL - Auckland Hub' },
};

function detectAirlinePrefix(value) {
    const clean = value.replace(/[^0-9]/g, '');
    const badge = document.getElementById('airlineDetectionBadge');
    const airlineNameInput = document.getElementById('airline_name');

    if (clean.length >= 3) {
        const prefix = clean.substring(0, 3);
        const match = AIRLINE_PREFIXES[prefix];
        if (match) {
            badge.className = 'mt-1.5 text-[11px] font-bold px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5 bg-amber-500/20 text-amber-800 border border-amber-500/30';
            badge.innerHTML = `<i class="fas fa-plane text-xs"></i> <span>Detected: ${match.name} (${match.code}) - ${match.hub}</span>`;
            badge.classList.remove('hidden');
            if (airlineNameInput && !airlineNameInput.value) {
                airlineNameInput.value = match.name;
            }
            return;
        }
    }
    badge.classList.add('hidden');
}

function updateCarrierPortalPreview() {
    const carrierInput = document.getElementById('carrier_name_input');
    const trackingInput = document.getElementById('carrier_tracking_input');
    const urlText = document.getElementById('carrierPreviewUrlText');
    const linkBtn = document.getElementById('carrierPreviewLink');
    const badge = document.getElementById('carrierFormatBadge');

    const carrier = (carrierInput?.value || '').toLowerCase().trim();
    const tracking = (trackingInput?.value || '').trim();

    if (!tracking) {
        urlText.innerText = 'Enter carrier name and tracking number above to preview direct portal URL.';
        linkBtn.classList.add('hidden');
        badge.classList.add('hidden');
        return;
    }

    let url = `https://www.17track.net/en/track?nums=${encodeURIComponent(tracking)}`;
    let detectedFormat = null;

    if (carrier.includes('dhl') || /^\d{10,11}$/.test(tracking)) {
        url = `https://www.dhl.com/global-en/home/tracking/tracking-express.html?submit=1&tracking-id=${encodeURIComponent(tracking)}`;
        detectedFormat = 'DHL Express (10-11 numeric digits)';
    } else if (carrier.includes('fedex') || /^\d{12,15}$/.test(tracking)) {
        url = `https://www.fedex.com/fedextrack/?trknbr=${encodeURIComponent(tracking)}`;
        detectedFormat = 'FedEx Express (12-15 numeric digits)';
    } else if (carrier.includes('ups') || /^1Z/i.test(tracking)) {
        url = `https://www.ups.com/track?tracknum=${encodeURIComponent(tracking)}`;
        detectedFormat = 'UPS (1Z barcode format)';
    } else if (carrier.includes('aramex')) {
        url = `https://www.aramex.com/track/results?mode=0&ShipmentNumber=${encodeURIComponent(tracking)}`;
        detectedFormat = 'Aramex International';
    } else if (carrier.includes('royal mail')) {
        url = `https://www.royalmail.com/track-your-item#/tracking-results/${encodeURIComponent(tracking)}`;
        detectedFormat = 'Royal Mail UK';
    } else if (carrier.includes('usps')) {
        url = `https://tools.usps.com/go/TrackConfirmAction?tLabels=${encodeURIComponent(tracking)}`;
        detectedFormat = 'USPS Priority';
    }

    urlText.innerText = url;
    linkBtn.href = url;
    linkBtn.classList.remove('hidden');

    if (detectedFormat) {
        badge.className = 'mt-1 text-[10px] font-bold px-2 py-0.5 rounded inline-block bg-teal-100 text-teal-800';
        badge.innerText = `Verified: ${detectedFormat}`;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const mawbInput = document.getElementById('new_mawb_number');
    if (mawbInput && mawbInput.value) {
        detectAirlinePrefix(mawbInput.value);
    }
    updateCarrierPortalPreview();
});
</script>
@endpush
@endsection
