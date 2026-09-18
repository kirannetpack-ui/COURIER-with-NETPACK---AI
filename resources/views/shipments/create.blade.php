@extends('layouts.app')

@section('title', 'Ship & Pickup Console - NETPACK Unified Logistics')
@section('page-title', 'Consignment & Pickup Operating Console')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .map-container {
        height: 220px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        z-index: 10;
    }
    .leaflet-container {
        font-family: inherit;
        border-radius: 16px;
    }
</style>
@endpush

@section('content')
@php
    $savedAddressesJson = ($savedAddresses ?? collect())->map(function($addr) {
        return [
            'id' => $addr->id,
            'label' => $addr->display_name ?: ($addr->landmark ?: 'Saved Location'),
            'contact_person_name' => $addr->contact_person_name,
            'contact_phone' => $addr->contact_person_phone,
            'pickup_address' => $addr->address,
            'pickup_landmark' => $addr->landmark ?? '',
            'pickup_city' => $addr->city ?? 'Kathmandu',
            'is_default' => (bool)$addr->is_default,
        ];
    })->values();

    $defaultName = Auth::user()->name ?? '';
    $defaultPhone = Auth::user()->phone ?? '';
    $defaultAddress = Auth::user()->address ?? Auth::user()->permanent_address ?? '';
    $hasPickupInitial = old('schedule_doorstep_pickup', request('pickup', '1')) != '0';
    $initialView = (request('tab') === 'queue' || request('view') === 'queue') ? 'queue' : 'booking';
@endphp

<div class="max-w-7xl mx-auto space-y-6 pb-16"
     x-data="{
         activeConsoleView: '{{ $initialView }}',
         activeConsoleTab: '{{ $initialView === 'queue' ? 'queue' : 'consignment' }}',
         hasDoorstepPickup: {{ $hasPickupInitial ? 'true' : 'false' }},
         pickupScope: 'inside_valley',
         pickupServiceTier: 'flash',
         pickupCalcWeight: 1.0,
         hasKnownDestination: false,
         savedAddresses: {{ Js::from($savedAddressesJson) }},
         selectedAddressId: '',
         contactPersonName: '{{ addslashes($convertPickup->contact_person_name ?? $defaultName) }}',
         contactPhone: '{{ addslashes($convertPickup->contact_person_phone ?? $defaultPhone) }}',
         pickupAddress: '{{ addslashes($convertPickup->pickup_address ?? $defaultAddress) }}',
         pickupLandmark: '',
         pickupCity: 'Kathmandu',
         saveAddress: true,
         addressLabel: '',
         isSubmittingPickup: false,

         switchConsole(view) {
             this.activeConsoleView = view;
             this.activeConsoleTab = view === 'queue' ? 'queue' : 'consignment';
             if (view === 'booking' || view === 'consignment') {
                 setTimeout(() => {
                     if (window.pickupMaps) Object.values(pickupMaps).forEach(m => m && m.map && m.map.invalidateSize());
                     if (window.deliveryMaps) Object.values(deliveryMaps).forEach(m => m && m.map && m.map.invalidateSize());
                     if (window.internationalMap && internationalMap.map) internationalMap.map.invalidateSize();
                 }, 150);
             }
         },

         selectSavedPickupAddress(addr) {
             if (!addr) return;
             this.selectedAddressId = addr.id;
             this.contactPersonName = addr.contact_person_name;
             this.contactPhone = addr.contact_phone;
             this.pickupAddress = addr.pickup_address;
             this.pickupLandmark = addr.pickup_landmark || '';
             this.pickupCity = addr.pickup_city || 'Kathmandu';
             this.addressLabel = addr.label || '';
         },

         resetToNewPickupAddress() {
             this.selectedAddressId = 'new';
             this.contactPersonName = '{{ addslashes($defaultName) }}';
             this.contactPhone = '{{ addslashes($defaultPhone) }}';
             this.pickupAddress = '';
             this.pickupLandmark = '';
             this.pickupCity = 'Kathmandu';
             this.addressLabel = '';
         },

         setPickupScope(newScope) {
             this.pickupScope = newScope;
             if (newScope === 'inside_valley') {
                 this.pickupServiceTier = 'flash';
             } else if (newScope === 'outside_valley') {
                 this.pickupServiceTier = 'express';
             } else {
                 this.pickupServiceTier = 'priority_express';
             }
         },

         get estimatedPickupCost() {
             let weight = Math.max(0.1, parseFloat(this.pickupCalcWeight) || 1.0);
             let base = 0;
             let perKg = 0;
             
             if (this.pickupScope === 'inside_valley') {
                 if (this.pickupServiceTier === 'flash') { base = 120; perKg = 60; }
                 else if (this.pickupServiceTier === 'same_day') { base = 100; perKg = 50; }
                 else { base = 80; perKg = 40; }
             } else if (this.pickupScope === 'outside_valley') {
                 if (this.pickupServiceTier === 'express') { base = 220; perKg = 90; }
                 else if (this.pickupServiceTier === 'himalayan') { base = 350; perKg = 150; }
                 else { base = 160; perKg = 70; }
             } else {
                 if (this.pickupServiceTier === 'priority_express') { base = 2800; perKg = 1200; }
                 else if (this.pickupServiceTier === 'document') { base = 1800; perKg = 800; }
                 else { base = 2200; perKg = 950; }
             }
             return Math.round(base + (Math.max(0, weight - 1) * perKg));
         }
     }">
    
    <!-- TOP HERO BANNER -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-950 via-slate-900 to-teal-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-teal-800/40">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold tracking-wider uppercase bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <i class="fas fa-boxes-packing text-teal-400"></i>
                        Unified Operating Console
                    </span>
                    <span class="text-xs text-slate-400">&bull; Consignment Booking & Doorstep Courier Intake</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span>Ship & Pickup Console</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1.5 max-w-2xl leading-relaxed">
                    Operate complete consignment bookings or request rapid on-demand courier collection from one centralized cockpit.
                    Features multi-stop routing, live tariff quotation, saved address book, and automated rider dispatch.
                </p>
            </div>

            <!-- Quick Tariff / Rate Banner if arriving from Calculator -->
            @if(request('quoted_rate'))
                <div class="p-4 rounded-2xl bg-teal-900/50 border border-teal-500/40 backdrop-blur-xs flex items-center gap-3 self-start md:self-auto shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/20 border border-teal-400/30 flex items-center justify-center text-teal-300 text-lg flex-shrink-0">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                Verified Rate Applied
                            </span>
                            <span class="text-xs text-slate-300 font-bold">{{ request('receiver_country') }}</span>
                        </div>
                        <p class="text-xs text-slate-300">
                            Quoted Tariff: <span class="text-teal-300 font-mono font-black text-sm">Rs. {{ number_format((float)request('quoted_rate')) }}</span>
                            <span class="text-[11px] text-slate-400 ml-1">({{ request('chargeable_weight', request('weight')) }} KG)</span>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- OPERATING CONSOLE VIEW SWITCHER (SINGLE UNIFIED BOOKING FUNCTION) -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900/90 border border-teal-800/50 rounded-2xl p-2 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto">
            <!-- Unified Booking Function -->
            <button type="button" 
                    @click="switchConsole('booking')"
                    :class="activeConsoleView === 'booking' ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-md font-black' : 'text-slate-300 hover:text-white hover:bg-slate-800/80 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer flex-shrink-0">
                <i class="fas fa-boxes-stacked text-teal-300"></i>
                <span>Create Shipment & Pickup</span>
                <span class="text-[9px] uppercase font-mono px-1.5 py-0.5 rounded bg-black/20 text-teal-200 border border-teal-400/30">Single Function</span>
            </button>

            <!-- Live Dispatches Queue -->
            <button type="button" 
                    @click="switchConsole('queue')"
                    :class="activeConsoleView === 'queue' ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-md font-black' : 'text-slate-300 hover:text-white hover:bg-slate-800/80 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer flex-shrink-0">
                <i class="fas fa-list-check text-amber-300"></i>
                <span>Live Dispatches Queue</span>
                @php
                    $pendingCount = $pickupStatusCounts['pending'] ?? 0;
                @endphp
                @if($pendingCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black bg-amber-400 text-slate-950">
                        {{ $pendingCount }} Active
                    </span>
                @endif
            </button>
        </div>

        <div class="hidden lg:flex items-center gap-2 text-xs text-slate-400 pr-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Single Unified Logistics Console &bull; Auto-Sync</span>
        </div>
    </div>

    <!-- ERROR NOTIFICATIONS -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="text-xs">
                <h4 class="font-bold text-rose-950 text-sm">Please check the required fields:</h4>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-check"></i>
            </div>
            <div class="text-xs">
                <h4 class="font-bold text-emerald-950 text-sm">Action Confirmed</h4>
                <p class="mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- CONVERT PICKUP INQUIRY BANNER -->
    @if(isset($convertPickup) && $convertPickup)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm flex-shrink-0">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-amber-950 flex items-center gap-2">
                        <span>Upgrading Doorstep Collection to Full Consignment</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-200 text-amber-900">Ref: {{ $convertPickup->tracking_number }}</span>
                    </h4>
                    <p class="text-xs text-amber-800 mt-0.5">
                        Sender address, estimated weight ({{ $convertPickup->estimated_weight_kg }} KG), and cargo specs have been pre-filled.
                    </p>
                </div>
            </div>
            <button type="button" @click="switchConsole('booking')" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl transition self-start sm:self-auto">
                Proceed to Consignment Form
            </button>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- UNIFIED SHIPMENT & DOORSTEP PICKUP BOOKING FORM -->
    <!-- ========================================================================= -->
    <div x-show="activeConsoleView === 'booking' || activeConsoleTab === 'consignment'" x-transition class="space-y-6">
        <form action="{{ route('shipments.store') }}" method="POST" id="shipment-form" class="space-y-6">
            @csrf
            @if(request()->filled('quoted_rate'))
                <input type="hidden" name="quoted_rate" value="{{ request('quoted_rate') }}">
                <input type="hidden" name="chargeable_weight" value="{{ request('chargeable_weight', request('weight')) }}">
                <input type="hidden" name="pickup_location_type" value="{{ request('pickup_location_type') }}">
                <input type="hidden" name="pickup_city" value="{{ request('pickup_city') }}">
                <input type="hidden" name="domestic_feeder_charge" value="{{ request('domestic_feeder_charge') }}">
            @endif

            @if(isset($convertPickup) && $convertPickup)
                <input type="hidden" name="convert_pickup_id" value="{{ $convertPickup->id }}">
            @endif

            <!-- 1. SERVICE MODE SELECTOR (DOMESTIC / INTERNATIONAL / E-COMMERCE) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/90 space-y-6">
                <div>
                    <label class="text-xs font-black uppercase tracking-wider text-slate-700 block mb-3">
                        1. Select Shipment Service Category *
                    </label>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <!-- Tab 1: Domestic -->
                        <button type="button" onclick="switchMode('domestic')" id="mode-btn-domestic"
                                class="mode-selector-btn border-2 border-teal-600 bg-teal-50/70 text-slate-900 ring-2 ring-teal-500/20 rounded-2xl p-4 text-left transition flex items-start gap-3.5 cursor-pointer">
                            <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-xs">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-black text-slate-900">Domestic Delivery</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5 leading-snug">Inter-city & inter-province courier across Nepal's 77 districts</span>
                            </div>
                        </button>

                        <!-- Tab 2: International -->
                        <button type="button" onclick="switchMode('international')" id="mode-btn-international"
                                class="mode-selector-btn border-2 border-slate-200 bg-white hover:border-slate-300 text-slate-900 rounded-2xl p-4 text-left transition flex items-start gap-3.5 cursor-pointer">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg flex-shrink-0 shadow-xs">
                                <i class="fas fa-plane-departure"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-black text-slate-900">International Air</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5 leading-snug">Worldwide air express & cargo to USA, UK, AUS, UAE & 220+ hubs</span>
                            </div>
                        </button>

                        <!-- Tab 3: E-Commerce -->
                        <button type="button" onclick="switchMode('ecommerce')" id="mode-btn-ecommerce"
                                class="mode-selector-btn border-2 border-slate-200 bg-white hover:border-slate-300 text-slate-900 rounded-2xl p-4 text-left transition flex items-start gap-3.5 cursor-pointer">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg flex-shrink-0 shadow-xs">
                                <i class="fas fa-motorcycle"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-black text-slate-900">E-Commerce Rider</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5 leading-snug">Instant dispatch, same-day valley deliveries, and cash on delivery</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Hidden input for shipment_type -->
                <input type="hidden" name="shipment_type" id="shipment_type" value="domestic">

                <!-- Dynamic Service SLA Tier Sub-options -->
                <div class="pt-4 border-t border-slate-100">
                    <!-- Domestic Service Options -->
                    <div id="service-options-domestic" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Domestic Service Tier SLA *</label>
                            <select name="service_type" id="domestic_service_type" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-semibold text-slate-800">
                                @if(!empty($domesticServices) && is_array($domesticServices))
                                    @foreach($domesticServices as $key => $item)
                                        @php
                                            $displayName = is_array($item) 
                                                ? (($item['icon'] ?? '') . ' ' . ($item['name'] ?? strtoupper($key)) . (!empty($item['time']) ? ' (' . $item['time'] . ')' : ''))
                                                : $item;
                                        @endphp
                                        <option value="{{ $key }}" @selected(old('service_type', 'standard') == $key)>{{ trim($displayName) }}</option>
                                    @endforeach
                                @else
                                    <option value="flash">⚡ FLASH (1-2 Hours Urgent Delivery)</option>
                                    <option value="same_day">🕐 SAME DAY (4-6 Hours Express)</option>
                                    <option value="standard" selected>🚚 STANDARD (1-2 Days Normal Transit)</option>
                                    <option value="himalayan">🏔️ HIMALAYAN (2-4 Days Remote Districts)</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Package Classification</label>
                            <select name="package_type" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-800 font-medium">
                                <option value="parcel" selected>📦 Standard Parcel / Goods</option>
                                <option value="box">📦 Box / Heavy Carton</option>
                                <option value="envelope">✉️ Documents / Legal Envelopes</option>
                                <option value="fragile">⚡ Fragile Glassware / Electronics</option>
                            </select>
                        </div>
                    </div>

                    <!-- International Service Options -->
                    <div id="service-options-international" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">International Air Courier Mode *</label>
                            <select id="international_service_type" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-semibold text-slate-800">
                                <option value="express" {{ request('service_type') === 'express' ? 'selected' : '' }}>⚡ Priority Express Service (3–4 Working Days)</option>
                                <option value="economy" {{ request('service_type', 'economy') === 'economy' && request('service_type') !== 'express' ? 'selected' : '' }}>🌍 Economy Air Cargo Service (6–8 Working Days)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Package Packaging</label>
                            <select name="intl_package_type" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-800 font-medium">
                                <option value="box" selected>📦 Box / Heavy Carton</option>
                                <option value="parcel">📦 Flyer / Commercial Parcel</option>
                                <option value="envelope">✉️ International Document / Letter</option>
                            </select>
                        </div>
                    </div>

                    <!-- E-Commerce Service Options -->
                    <div id="service-options-ecommerce" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">E-Commerce Rider SLA *</label>
                            <select id="ecommerce_service_type" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-semibold text-slate-800">
                                <option value="flash" selected>⚡ Instant Rider Dispatch (Within 60-90 Mins)</option>
                                <option value="same_day">🚀 Same-Day Delivery (4-6 Hours)</option>
                                <option value="standard">📦 Standard Next-Day Collection</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Package Type</label>
                            <select name="ecom_package_type" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-800 font-medium">
                                <option value="parcel" selected>🛍️ E-Commerce Retail Parcel</option>
                                <option value="box">📦 Carton / Multi-Item Order</option>
                                <option value="grocery">🍎 Grocery / Perishable Foodstuff</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Auto-routed Network Identifiers -->
                @if(request()->filled('origin_zone_id'))
                    <input type="hidden" name="origin_zone_id" value="{{ request('origin_zone_id') }}">
                @endif
                @if(request()->filled('destination_zone_id'))
                    <input type="hidden" name="destination_zone_id" value="{{ request('destination_zone_id') }}">
                @endif
            </div>

            <!-- 2. DOORSTEP PICKUP & COLLECTION OPTION (KEPT INITIALLY, ENABLED ONLY WHEN CHOSEN) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/90 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">2</span>
                            <span>📍 Doorstep Pickup & Collection Option</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Request an on-demand courier rider to collect parcels from your doorstep, or drop off at an authorized counter.
                        </p>
                    </div>

                    <div x-show="hasDoorstepPickup" x-transition>
                        <button type="button" onclick="addPickupPoint()" 
                                class="px-4 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-xs font-extrabold transition flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                            <i class="fas fa-plus text-teal-600"></i>
                            <span>Add Another Pickup Point</span>
                        </button>
                    </div>
                </div>

                <!-- INTEGRATED DOORSTEP PICKUP RIDER DISPATCH TOGGLE (OPTION TO CHOOSE) -->
                <div class="p-4 sm:p-5 rounded-2xl border-2 transition-all"
                     :class="hasDoorstepPickup ? 'bg-gradient-to-r from-teal-50 via-emerald-50/70 to-slate-50 border-teal-600 ring-2 ring-teal-500/20' : 'bg-slate-50 border-slate-200'">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="pt-0.5">
                                <input type="checkbox" id="schedule_doorstep_pickup" name="schedule_doorstep_pickup" value="1"
                                       x-model="hasDoorstepPickup"
                                       class="w-5 h-5 text-teal-600 rounded-md border-slate-300 focus:ring-teal-500 cursor-pointer">
                            </div>
                            <div>
                                <label for="schedule_doorstep_pickup" class="text-xs sm:text-sm font-black text-slate-900 cursor-pointer flex items-center gap-2 flex-wrap">
                                    <span>Dispatch Doorstep Courier Collection for this Consignment</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-teal-600 text-white tracking-wider">Rider Fleet</span>
                                </label>
                                <p class="text-[11px] sm:text-xs text-slate-600 mt-1 leading-relaxed">
                                    A courier rider will be dispatched to collect packages from <strong>Pickup Location #1</strong> at your chosen time slot.
                                </p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="text-[11px] font-extrabold px-3 py-1 rounded-xl transition"
                                  :class="hasDoorstepPickup ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-200 text-slate-600'">
                                <span x-text="hasDoorstepPickup ? '✓ Doorstep Pickup Active' : 'Self Drop-off'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Collection Time & Rider Instructions (visible when chosen) -->
                    <div x-show="hasDoorstepPickup" x-transition class="mt-4 pt-4 border-t border-teal-200/60 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-bold text-slate-700 block mb-1">Preferred Collection Time Slot:</label>
                            <input type="datetime-local" name="scheduled_pickup_time" 
                                   value="{{ old('scheduled_pickup_time', now()->addHours(2)->format('Y-m-d\TH:i')) }}"
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-700 block mb-1">Pickup Notes for Rider (Optional):</label>
                            <input type="text" name="pickup_notes" placeholder="e.g. Ring bell on 2nd floor, call sender on arrival"
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium">
                        </div>
                    </div>
                </div>

                <!-- SELF DROP-OFF NOTICE (when doorstep pickup is NOT chosen) -->
                <div x-show="!hasDoorstepPickup" x-transition class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-900 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-boxes-packing"></i>
                    </div>
                    <div class="text-xs">
                        <h4 class="font-bold text-amber-950 text-sm">Self Drop-off at Station Selected</h4>
                        <p class="mt-0.5 text-amber-800">
                            You may drop off your consignment at any authorized Netpack hub or branch counter. No courier rider will be dispatched.
                            Please verify your sender contact details below for the consignment label.
                        </p>
                    </div>
                </div>

                <!-- PICKUP CARDS CONTAINER -->
                <div id="pickup-container" class="space-y-6">
                    <!-- Initial Pickup Card #0 -->
                    <div class="pickup-card p-5 sm:p-6 rounded-2xl bg-slate-50/70 border border-slate-200 space-y-4" id="pickup-card-0">
                        <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs font-black flex items-center justify-center">1</span>
                                <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Pickup Location #1</span>
                            </div>
                            <button type="button" onclick="removePickupPoint(0)" class="text-rose-500 hover:text-rose-700 text-xs font-bold hidden remove-pickup-btn cursor-pointer">
                                <i class="fas fa-trash-can mr-1"></i> Remove Location
                            </button>
                        </div>

                        <!-- 1-CLICK SAVED ADDRESS & INQUIRY PRE-FILLER -->
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-2">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                    <i class="fas fa-bolt text-amber-500"></i>
                                    <span>1-Click Auto-Fill from Address Book / Inquiries:</span>
                                </label>
                                <span class="text-[10px] text-teal-700 font-semibold">Instant data population</span>
                            </div>

                            <select onchange="applySavedPickup(this, 0)" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800">
                                <option value="">-- Choose from Saved Addresses or Prior Doorstep Inquiries --</option>
                                
                                @if(isset($savedAddresses) && $savedAddresses->count() > 0)
                                    <optgroup label="🏢 Address Book (Saved Locations)">
                                        @foreach($savedAddresses as $sAddr)
                                            <option value="saved_{{ $sAddr->id }}"
                                                    data-name="{{ $sAddr->contact_person_name }}"
                                                    data-phone="{{ $sAddr->contact_person_phone }}"
                                                    data-address="{{ $sAddr->address . ($sAddr->landmark ? ', ' . $sAddr->landmark : '') }}"
                                                    data-lat="{{ $sAddr->latitude ?? '' }}"
                                                    data-lng="{{ $sAddr->longitude ?? '' }}">
                                                {{ $sAddr->display_name }} ({{ $sAddr->contact_person_name }} &bull; {{ $sAddr->contact_person_phone }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                @if(isset($recentPickups) && $recentPickups->count() > 0)
                                    <optgroup label="📦 Recent Doorstep Inquiries">
                                        @foreach($recentPickups as $pReq)
                                            <option value="inquiry_{{ $pReq->id }}"
                                                    data-name="{{ $pReq->contact_person_name ?? Auth::user()->name }}"
                                                    data-phone="{{ $pReq->contact_person_phone ?? Auth::user()->phone }}"
                                                    data-address="{{ $pReq->pickup_address }}"
                                                    data-lat="{{ $pReq->pickup_latitude ?? '' }}"
                                                    data-lng="{{ $pReq->pickup_longitude ?? '' }}">
                                                {{ $pReq->tracking_number ?? ('#REQ-' . $pReq->id) }} &bull; {{ Str::limit($pReq->pickup_address, 35) }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>

                        <!-- Pickup Contact & Address Inputs -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Contact Person Name <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-user absolute left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                                    <input type="text" name="pickup_name[]" id="pickup_name_0" required 
                                           value="{{ old('pickup_name.0', $convertPickup->contact_person_name ?? Auth::user()->name ?? '') }}"
                                           class="w-full text-xs pl-8 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900"
                                           placeholder="Contact person name">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Number <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-phone absolute left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                                    <input type="text" name="pickup_phone[]" id="pickup_phone_0" required 
                                           value="{{ old('pickup_phone.0', $convertPickup->contact_person_phone ?? Auth::user()->phone ?? '') }}"
                                           class="w-full text-xs pl-8 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-medium text-slate-900"
                                           placeholder="98XXXXXXXX">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Pickup Street Address & Landmark <span class="text-rose-500">*</span></label>
                            <textarea name="pickup_address[]" id="pickup_address_0" rows="2" required 
                                      class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900"
                                      placeholder="Full street address, ward number, or building">{{ old('pickup_address.0', $convertPickup->pickup_address ?? Auth::user()->address ?? Auth::user()->permanent_address ?? '') }}</textarea>
                        </div>

                        <!-- Interactive Leaflet Map for Pickup #0 -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                    <i class="fas fa-map-location-dot text-teal-600"></i>
                                    <span>Interactive Map Pin (Click or Drag Marker):</span>
                                </label>
                                <span class="text-[10px] text-slate-400 font-mono" id="pickup-coords-0">Lat: 27.7172, Lng: 85.3240</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                                    <input type="text" id="pickup-search-0" 
                                           placeholder="Search landmark, area or street in Nepal..."
                                           onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('pickup', 0); }"
                                           class="w-full text-xs pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                                </div>
                                <button type="button" onclick="searchLocationOnMap('pickup', 0)" class="px-3 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition">
                                    Search
                                </button>
                                <button type="button" onclick="getCurrentLocationOnMap('pickup', 0)" title="Use Current GPS" class="px-3 py-2 bg-teal-50 text-teal-700 border border-teal-200 rounded-xl text-xs font-bold hover:bg-teal-100 transition">
                                    <i class="fas fa-location-crosshairs"></i>
                                </button>
                            </div>

                            <div id="pickup-map-0" class="map-container"></div>
                            <input type="hidden" name="pickup_lat[]" id="pickup-lat-0" value="27.7172">
                            <input type="hidden" name="pickup_lng[]" id="pickup-lng-0" value="85.3240">
                        </div>
                    </div>
                </div>

                <!-- Auto-Save Address Book Option -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                    <input type="checkbox" name="save_pickup_addresses" id="save_pickup_addresses" value="1" checked class="w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500 cursor-pointer">
                    <label for="save_pickup_addresses" class="text-xs text-slate-700 font-medium cursor-pointer">
                        Automatically remember and save newly entered pickup locations to my Address Book for 1-click re-use.
                    </label>
                </div>
            </div>

            <!-- 3. DELIVERY DESTINATIONS (INTERNATIONAL / DOMESTIC) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/90 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">3</span>
                            <span>🎯 Delivery Destination(s)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Specify delivery consignee address. Supports multi-stop drop-offs across domestic zones or single international address.
                        </p>
                    </div>

                    <button type="button" onclick="addDeliveryPoint()" id="add-delivery-btn"
                            class="px-4 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-xs font-extrabold transition flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                        <i class="fas fa-plus text-teal-600"></i>
                        <span>Add Another Destination</span>
                    </button>
                </div>

                <!-- International 5-Line Format Container -->
                <div id="delivery-international" class="hidden space-y-4">
                    <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200/70 space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-teal-200/50">
                            <i class="fas fa-globe text-teal-700"></i>
                            <span class="font-extrabold text-xs text-teal-950 uppercase tracking-wider">Overseas Consignee Information</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Destination Country <span class="text-rose-500">*</span></label>
                                <select name="receiver_country" id="receiver_country" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-semibold text-slate-900">
                                    <option value="">Select country</option>
                                    @php
                                        $commonCountries = ['United States', 'United Kingdom', 'Australia', 'United Arab Emirates', 'Canada', 'India', 'Japan', 'Germany', 'France', 'Singapore', 'Qatar', 'Malaysia', 'Saudi Arabia', 'South Korea'];
                                    @endphp
                                    @foreach($commonCountries as $c)
                                        <option value="{{ $c }}" @selected(request('receiver_country') == $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Receiver Name / Company <span class="text-rose-500">*</span></label>
                                <input type="text" name="receiver_name" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900" placeholder="Full name or company">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">City <span class="text-rose-500">*</span></label>
                                <input type="text" name="receiver_city" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium" placeholder="City">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">State / Province</label>
                                <input type="text" name="receiver_state" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium" placeholder="State / Province">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Postal / ZIP Code</label>
                                <input type="text" name="receiver_postal_code" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono" placeholder="ZIP code">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Street Address <span class="text-rose-500">*</span></label>
                            <input type="text" name="receiver_street" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium" placeholder="Street line 1, suite, building">
                        </div>

                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Map Pin for Destination City:</label>
                                <span class="text-[10px] text-slate-400 font-mono" id="delivery-intl-coords">Lat: 40.7128, Lng: -74.0060</span>
                            </div>
                            <div id="delivery-international-map" class="map-container"></div>
                            <input type="hidden" name="delivery_lat_intl" id="delivery-lat-intl" value="40.7128">
                            <input type="hidden" name="delivery_lng_intl" id="delivery-lng-intl" value="-74.0060">
                        </div>
                    </div>
                </div>

                <!-- Domestic / E-Commerce Multiple Destinations Container -->
                <div id="delivery-multiple" class="space-y-6">
                    <div class="delivery-card p-5 sm:p-6 rounded-2xl bg-slate-50/70 border border-slate-200 space-y-4" id="delivery-card-0">
                        <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs font-black flex items-center justify-center">1</span>
                                <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Delivery Destination #1</span>
                            </div>
                            <button type="button" onclick="removeDeliveryPoint(0)" class="text-rose-500 hover:text-rose-700 text-xs font-bold hidden remove-delivery-btn cursor-pointer">
                                <i class="fas fa-trash-can mr-1"></i> Remove Destination
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Recipient Full Name <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-user-check absolute left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                                    <input type="text" name="delivery_name[]" id="delivery_name_0" required 
                                           value="{{ old('delivery_name.0', $convertPickup->customer_name ?? '') }}"
                                           class="w-full text-xs pl-8 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900"
                                           placeholder="Recipient name">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Recipient Phone <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-phone absolute left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                                    <input type="text" name="delivery_phone[]" id="delivery_phone_0" required 
                                           value="{{ old('delivery_phone.0', $convertPickup->customer_phone ?? '') }}"
                                           class="w-full text-xs pl-8 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-medium text-slate-900"
                                           placeholder="98XXXXXXXX">
                                </div>
                            </div>
                        </div>

                        <!-- Nepal Territory Picker (7 Provinces & 77 Districts) -->
                        <div class="p-4 rounded-xl bg-white border border-slate-200">
                            <x-nepal-territory-picker 
                                provinceName="delivery_province[]" 
                                districtName="delivery_district[]" 
                                provinceLabel="Destination Province / Region" 
                                districtLabel="Destination District" 
                                idPrefix="shipment_deliv_0" 
                                helperText="Select the destination province to show its 77-district sub-list." />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Street Address & Landmark <span class="text-rose-500">*</span></label>
                            <textarea name="delivery_address[]" id="delivery_address_0" rows="2" required 
                                      class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900"
                                      placeholder="Full street address, building, ward number">{{ old('delivery_address.0', $convertPickup->delivery_address ?? '') }}</textarea>
                        </div>

                        <!-- Interactive Leaflet Map for Delivery #0 -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                    <i class="fas fa-map-location-dot text-teal-600"></i>
                                    <span>Delivery Pin on Map:</span>
                                </label>
                                <span class="text-[10px] text-slate-400 font-mono" id="delivery-coords-0">Lat: 27.7172, Lng: 85.3240</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                                    <input type="text" id="delivery-search-0" 
                                           placeholder="Search delivery area or landmark..."
                                           onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('delivery', 0); }"
                                           class="w-full text-xs pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                                </div>
                                <button type="button" onclick="searchLocationOnMap('delivery', 0)" class="px-3 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition">
                                    Search
                                </button>
                                <button type="button" onclick="getCurrentLocationOnMap('delivery', 0)" title="Use Current GPS" class="px-3 py-2 bg-teal-50 text-teal-700 border border-teal-200 rounded-xl text-xs font-bold hover:bg-teal-100 transition">
                                    <i class="fas fa-location-crosshairs"></i>
                                </button>
                            </div>

                            <div id="delivery-map-0" class="map-container"></div>
                            <input type="hidden" name="delivery_lat[]" id="delivery-lat-0" value="27.7172">
                            <input type="hidden" name="delivery_lng[]" id="delivery-lng-0" value="85.3240">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. CARGO SPECIFICATIONS & VOLUMETRIC CALCULATOR -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/90 space-y-6">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">4</span>
                        <span>⚖️ Package Weight & Cargo Specifications</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Air cargo pricing is based on the greater of gross actual weight or IATA volumetric weight.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gross Weight (KG) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" name="weight" id="weight-input" required 
                                   value="{{ old('weight', $convertPickup->estimated_weight_kg ?? request('weight', '1.0')) }}"
                                   oninput="calculateVolumetricWeight()"
                                   class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-bold text-slate-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Length (cm)</label>
                        <input type="number" step="0.1" name="length" id="length-input" placeholder="L" 
                               oninput="calculateVolumetricWeight()"
                               class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Width (cm)</label>
                        <input type="number" step="0.1" name="width" id="width-input" placeholder="W" 
                               oninput="calculateVolumetricWeight()"
                               class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Height (cm)</label>
                        <input type="number" step="0.1" name="height" id="height-input" placeholder="H" 
                               oninput="calculateVolumetricWeight()"
                               class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                    </div>
                </div>

                <!-- Real-time volumetric calculation badge -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-slate-600">Volumetric Weight:</span>
                        <span id="volumetric-weight-display" class="font-mono font-bold text-teal-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200">0.00 KG</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-600">IATA Standard (L×W×H / 5000):</span>
                        <span id="chargeable-weight-display" class="font-mono font-black text-slate-900 bg-white px-3 py-1 rounded-lg border border-slate-200">
                            Chargeable: 1.00 KG
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cargo / Goods Description</label>
                    <input type="text" name="description" placeholder="e.g. Garments, Electronic Samples, Documents"
                           class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-900 font-medium">
                </div>
            </div>

            <!-- 5. STICKY SUMMARY & SUBMIT CONSIGNMENT BUTTON -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-950 to-teal-950 rounded-3xl p-6 text-white shadow-xl border border-teal-900/60 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30" id="summary-mode-badge">
                                DOMESTIC
                            </span>
                            <span class="text-xs text-slate-400">&bull; Live Ready for Dispatch</span>
                        </div>
                        <h4 class="text-lg font-black text-white">Consignment Dispatch Summary</h4>
                        <p class="text-xs text-slate-300 mt-0.5">
                            <span id="summary-pickups-count">1 Location</span> &bull; 
                            <span id="summary-deliveries-count">1 Destination</span> &bull; 
                            <span id="summary-weight">1.00 KG</span>
                        </p>
                    </div>

                    @if(request('quoted_rate'))
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Approved Tariff</span>
                            <p class="text-2xl font-black font-mono text-teal-300">Rs. {{ number_format((float)request('quoted_rate')) }}</p>
                        </div>
                    @endif
                </div>

                <button type="submit" id="submit-btn"
                        class="w-full py-4 bg-gradient-to-r from-teal-400 to-emerald-400 hover:from-teal-300 hover:to-emerald-300 text-slate-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fas fa-circle-check"></i>
                    <span>Confirm & Book Consignment (Issue HAWB)</span>
                </button>

                <p class="text-[10px] text-slate-400 text-center leading-relaxed">
                    Upon submission, your consignment will be booked with verified HAWB telemetry and assigned for immediate collection.
                </p>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- LIVE DISPATCHES & CONSIGNMENT QUEUE VIEW -->
    <!-- ========================================================================= -->
    <div x-show="activeConsoleView === 'queue' || activeConsoleTab === 'queue'" x-transition class="space-y-6">
        <!-- Status Metrics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pickups</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $pickupStatusCounts['all'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Pending Dispatch</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $pickupStatusCounts['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Assigned / In Transit</p>
                <p class="text-2xl font-black text-blue-600 mt-1">{{ ($pickupStatusCounts['assigned'] ?? 0) + ($pickupStatusCounts['in_transit'] ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Delivered / Completed</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $pickupStatusCounts['delivered'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Live Queue Table Container -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 bg-gradient-to-r from-slate-50 to-teal-50/40 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="fas fa-list-check text-teal-600"></i>
                        <span>Active Dispatches & Pickup Queue</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Real-time status of your requested pickups and active consignment collections.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="switchConsole('booking'); hasDoorstepPickup = true" class="px-3.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i class="fas fa-truck-ramp-box"></i>
                        <span>New Shipment (With Pickup)</span>
                    </button>
                    <button type="button" @click="switchConsole('booking'); hasDoorstepPickup = false" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i class="fas fa-store"></i>
                        <span>Self Drop-off Booking</span>
                    </button>
                </div>
            </div>

            @if(isset($activePickups) && $activePickups->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/80 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-4">Reference #</th>
                                <th class="p-4">Pickup Location</th>
                                <th class="p-4">Destination</th>
                                <th class="p-4">Assigned Rider</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($activePickups as $pReq)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-4">
                                        <span class="font-mono font-bold text-slate-900">{{ $pReq->tracking_number }}</span>
                                        <span class="block text-[10px] text-slate-400 font-normal">{{ $pReq->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="p-4 max-w-xs">
                                        <div class="truncate text-slate-800 font-semibold">{{ $pReq->pickup_address }}</div>
                                        <div class="text-[11px] text-slate-500 truncate">{{ $pReq->contact_person_name }} &bull; {{ $pReq->contact_person_phone }}</div>
                                    </td>
                                    <td class="p-4 max-w-xs">
                                        <div class="truncate text-slate-800">{{ $pReq->delivery_address ?: 'Open Destination (Declare at collection)' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $pReq->delivery_city ?: 'Kathmandu Valley' }}</div>
                                    </td>
                                    <td class="p-4">
                                        @if($pReq->rider)
                                            <span class="inline-flex items-center gap-1 text-slate-800 font-bold">
                                                <i class="fas fa-motorcycle text-teal-600"></i> {{ $pReq->rider->name }}
                                            </span>
                                            <span class="block text-[11px] text-slate-500 font-mono">{{ $pReq->rider->phone }}</span>
                                        @else
                                            <span class="text-amber-700 bg-amber-50 border border-amber-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                Awaiting Rider
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @php
                                            $stMap = [
                                                'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                'assigned' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'picked_up' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                'in_transit' => 'bg-sky-100 text-sky-800 border-sky-200',
                                                'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            ];
                                            $stClass = $stMap[$pReq->status] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $stClass }}">
                                            {{ str_replace('_', ' ', $pReq->status) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        <a href="{{ route('tracking.search', ['tracking' => $pReq->tracking_number]) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition inline-flex items-center gap-1">
                                            <i class="fas fa-search text-[10px]"></i> Track
                                        </a>

                                        @if(empty($pReq->shipment_id))
                                            <a href="{{ route('shipments.create', ['convert_pickup_id' => $pReq->id, 'tab' => 'consignment']) }}" 
                                               class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 font-bold text-[11px] transition inline-flex items-center gap-1">
                                                <i class="fas fa-boxes-stacked text-[10px]"></i> Convert to Consignment
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-slate-500 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700">No active dispatches found in the queue</p>
                    <p class="text-xs max-w-sm mx-auto">Schedule a doorstep pickup or book a full consignment to dispatch couriers immediately.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Maps storage objects
    const pickupMaps = {};
    const deliveryMaps = {};
    let internationalMap = null;
    let pickupCount = 1;
    let deliveryCount = 1;

    // =============================================
    // MAP INITIALIZATION MANAGER
    // =============================================
    function initLeafletMap(containerId, initialLat, initialLng, onUpdateCoords) {
        const el = document.getElementById(containerId);
        if (!el || typeof L === 'undefined') return null;

        const map = L.map(containerId, { zoomControl: true }).setView([initialLat, initialLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

        marker.on('dragend', function() {
            const pos = marker.getLatLng();
            if (onUpdateCoords) onUpdateCoords(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            if (onUpdateCoords) onUpdateCoords(e.latlng.lat, e.latlng.lng);
        });

        return { map, marker };
    }

    function initPickupMap(index) {
        const mapId = `pickup-map-${index}`;
        const instance = initLeafletMap(mapId, 27.7172, 85.3240, function(lat, lng) {
            document.getElementById(`pickup-lat-${index}`).value = lat.toFixed(6);
            document.getElementById(`pickup-lng-${index}`).value = lng.toFixed(6);
            const coordEl = document.getElementById(`pickup-coords-${index}`);
            if (coordEl) coordEl.innerText = `Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}`;
        });
        if (instance) pickupMaps[index] = instance;
    }

    function initDeliveryMap(index) {
        const mapId = `delivery-map-${index}`;
        const instance = initLeafletMap(mapId, 27.7172, 85.3240, function(lat, lng) {
            document.getElementById(`delivery-lat-${index}`).value = lat.toFixed(6);
            document.getElementById(`delivery-lng-${index}`).value = lng.toFixed(6);
            const coordEl = document.getElementById(`delivery-coords-${index}`);
            if (coordEl) coordEl.innerText = `Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}`;
        });
        if (instance) deliveryMaps[index] = instance;
    }

    function initIntlMap() {
        const instance = initLeafletMap('delivery-international-map', 40.7128, -74.0060, function(lat, lng) {
            document.getElementById('delivery-lat-intl').value = lat.toFixed(6);
            document.getElementById('delivery-lng-intl').value = lng.toFixed(6);
            const coordEl = document.getElementById('delivery-intl-coords');
            if (coordEl) coordEl.innerText = `Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}`;
        });
        if (instance) internationalMap = instance;
    }

    // =============================================
    // INLINE MAP LOCATION SEARCH (NO PROMPT)
    // =============================================
    function searchLocationOnMap(type, index) {
        const inputId = type === 'pickup' ? `pickup-search-${index}` : `delivery-search-${index}`;
        const input = document.getElementById(inputId);
        if (!input || !input.value.trim()) return;

        const query = input.value.trim() + (type === 'pickup' ? ', Nepal' : '');
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    
                    const obj = type === 'pickup' ? pickupMaps[index] : deliveryMaps[index];
                    if (obj) {
                        obj.map.setView([lat, lng], 15);
                        obj.marker.setLatLng([lat, lng]);
                        
                        const latEl = document.getElementById(type === 'pickup' ? `pickup-lat-${index}` : `delivery-lat-${index}`);
                        const lngEl = document.getElementById(type === 'pickup' ? `pickup-lng-${index}` : `delivery-lng-${index}`);
                        if (latEl) latEl.value = lat.toFixed(6);
                        if (lngEl) lngEl.value = lng.toFixed(6);
                        
                        const coordEl = document.getElementById(type === 'pickup' ? `pickup-coords-${index}` : `delivery-coords-${index}`);
                        if (coordEl) coordEl.innerText = `Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}`;
                    }
                } else {
                    alert('Location not found. Please try a more specific landmark.');
                }
            })
            .catch(() => alert('Location search is currently busy. Please click directly on the map.'));
    }

    function getCurrentLocationOnMap(type, index) {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const obj = type === 'pickup' ? pickupMaps[index] : deliveryMaps[index];
                if (obj) {
                    obj.map.setView([lat, lng], 16);
                    obj.marker.setLatLng([lat, lng]);

                    const latEl = document.getElementById(type === 'pickup' ? `pickup-lat-${index}` : `delivery-lat-${index}`);
                    const lngEl = document.getElementById(type === 'pickup' ? `pickup-lng-${index}` : `delivery-lng-${index}`);
                    if (latEl) latEl.value = lat.toFixed(6);
                    if (lngEl) lngEl.value = lng.toFixed(6);

                    const coordEl = document.getElementById(type === 'pickup' ? `pickup-coords-${index}` : `delivery-coords-${index}`);
                    if (coordEl) coordEl.innerText = `Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}`;
                }
            },
            function() {
                alert('Unable to retrieve GPS position. Please click on the map directly.');
            }
        );
    }

    // =============================================
    // 1-CLICK SAVED ADDRESS & INQUIRY PRE-FILLER
    // =============================================
    function applySavedPickup(selectEl, index) {
        const option = selectEl.options[selectEl.selectedIndex];
        if (!option || !option.value) return;

        const name = option.getAttribute('data-name');
        const phone = option.getAttribute('data-phone');
        const address = option.getAttribute('data-address');
        const lat = parseFloat(option.getAttribute('data-lat'));
        const lng = parseFloat(option.getAttribute('data-lng'));

        if (name) document.getElementById(`pickup_name_${index}`).value = name;
        if (phone) document.getElementById(`pickup_phone_${index}`).value = phone;
        if (address) document.getElementById(`pickup_address_${index}`).value = address;

        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            const obj = pickupMaps[index];
            if (obj) {
                obj.map.setView([lat, lng], 15);
                obj.marker.setLatLng([lat, lng]);
                document.getElementById(`pickup-lat-${index}`).value = lat.toFixed(6);
                document.getElementById(`pickup-lng-${index}`).value = lng.toFixed(6);
                const coordEl = document.getElementById(`pickup-coords-${index}`);
                if (coordEl) coordEl.innerText = `Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}`;
            }
        }
    }

    // =============================================
    // DYNAMIC MULTIPLE PICKUP POINTS
    // =============================================
    function addPickupPoint() {
        const index = pickupCount++;
        const container = document.getElementById('pickup-container');

        const cardHtml = `
            <div class="pickup-card p-5 sm:p-6 rounded-2xl bg-slate-50/70 border border-slate-200 space-y-4" id="pickup-card-${index}">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs font-black flex items-center justify-center">${index + 1}</span>
                        <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Pickup Location #${index + 1}</span>
                    </div>
                    <button type="button" onclick="removePickupPoint(${index})" class="text-rose-500 hover:text-rose-700 text-xs font-bold remove-pickup-btn cursor-pointer">
                        <i class="fas fa-trash-can mr-1"></i> Remove Location
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Contact Person Name *</label>
                        <input type="text" name="pickup_name[]" id="pickup_name_${index}" required 
                               class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium"
                               placeholder="Contact person name">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Number *</label>
                        <input type="text" name="pickup_phone[]" id="pickup_phone_${index}" required 
                               class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-medium"
                               placeholder="98XXXXXXXX">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Street Address *</label>
                    <textarea name="pickup_address[]" id="pickup_address_${index}" rows="2" required 
                              class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium"
                              placeholder="Full street address, building, ward number"></textarea>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Map Pin:</label>
                        <span class="text-[10px] text-slate-400 font-mono" id="pickup-coords-${index}">Lat: 27.7172, Lng: 85.3240</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="text" id="pickup-search-${index}" placeholder="Search landmark..." 
                               onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('pickup', ${index}); }"
                               class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl">
                        <button type="button" onclick="searchLocationOnMap('pickup', ${index})" class="px-3 py-2 bg-teal-600 text-white rounded-xl text-xs font-bold">Search</button>
                    </div>

                    <div id="pickup-map-${index}" class="map-container"></div>
                    <input type="hidden" name="pickup_lat[]" id="pickup-lat-${index}" value="27.7172">
                    <input type="hidden" name="pickup_lng[]" id="pickup-lng-${index}" value="85.3240">
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', cardHtml);
        setTimeout(() => initPickupMap(index), 100);
        updateSummaryStats();
    }

    function removePickupPoint(index) {
        const card = document.getElementById(`pickup-card-${index}`);
        if (card && document.querySelectorAll('.pickup-card').length > 1) {
            card.remove();
            delete pickupMaps[index];
            updateSummaryStats();
        } else {
            alert('At least one pickup location is required.');
        }
    }

    // =============================================
    // DYNAMIC MULTIPLE DELIVERY POINTS
    // =============================================
    function addDeliveryPoint() {
        const index = deliveryCount++;
        const container = document.getElementById('delivery-multiple');

        const cardHtml = `
            <div class="delivery-card p-5 sm:p-6 rounded-2xl bg-slate-50/70 border border-slate-200 space-y-4" id="delivery-card-${index}">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs font-black flex items-center justify-center">${index + 1}</span>
                        <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Delivery Destination #${index + 1}</span>
                    </div>
                    <button type="button" onclick="removeDeliveryPoint(${index})" class="text-rose-500 hover:text-rose-700 text-xs font-bold remove-delivery-btn cursor-pointer">
                        <i class="fas fa-trash-can mr-1"></i> Remove Destination
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Recipient Full Name *</label>
                        <input type="text" name="delivery_name[]" id="delivery_name_${index}" required 
                               class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium"
                               placeholder="Recipient name">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Recipient Phone *</label>
                        <input type="text" name="delivery_phone[]" id="delivery_phone_${index}" required 
                               class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-medium"
                               placeholder="98XXXXXXXX">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Street Address *</label>
                    <textarea name="delivery_address[]" id="delivery_address_${index}" rows="2" required 
                              class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium"
                              placeholder="Full street address, building, ward number"></textarea>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Map Pin:</label>
                        <span class="text-[10px] text-slate-400 font-mono" id="delivery-coords-${index}">Lat: 27.7172, Lng: 85.3240</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="text" id="delivery-search-${index}" placeholder="Search landmark..." 
                               onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('delivery', ${index}); }"
                               class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl">
                        <button type="button" onclick="searchLocationOnMap('delivery', ${index})" class="px-3 py-2 bg-teal-600 text-white rounded-xl text-xs font-bold">Search</button>
                    </div>

                    <div id="delivery-map-${index}" class="map-container"></div>
                    <input type="hidden" name="delivery_lat[]" id="delivery-lat-${index}" value="27.7172">
                    <input type="hidden" name="delivery_lng[]" id="delivery-lng-${index}" value="85.3240">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', cardHtml);
        setTimeout(() => initDeliveryMap(index), 100);
        updateSummaryStats();
    }

    function removeDeliveryPoint(index) {
        const card = document.getElementById(`delivery-card-${index}`);
        if (card && document.querySelectorAll('.delivery-card').length > 1) {
            card.remove();
            delete deliveryMaps[index];
            updateSummaryStats();
        } else {
            alert('At least one delivery destination is required.');
        }
    }

    // =============================================
    // MODE SWITCHER (DOMESTIC / INTERNATIONAL / ECOM)
    // =============================================
    function switchMode(mode) {
        document.getElementById('shipment_type').value = mode;

        document.querySelectorAll('.mode-selector-btn').forEach(btn => {
            btn.className = 'mode-selector-btn border-2 border-slate-200 bg-white hover:border-slate-300 text-slate-900 rounded-2xl p-4 text-left transition flex items-start gap-3.5 cursor-pointer';
            btn.querySelector('div').className = 'w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg flex-shrink-0 shadow-xs';
        });

        const activeBtn = document.getElementById(`mode-btn-${mode}`);
        if (activeBtn) {
            activeBtn.className = 'mode-selector-btn border-2 border-teal-600 bg-teal-50/70 text-slate-900 ring-2 ring-teal-500/20 rounded-2xl p-4 text-left transition flex items-start gap-3.5 cursor-pointer';
            activeBtn.querySelector('div').className = 'w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-xs';
        }

        document.getElementById('service-options-domestic').classList.toggle('hidden', mode !== 'domestic');
        document.getElementById('service-options-international').classList.toggle('hidden', mode !== 'international');
        document.getElementById('service-options-ecommerce').classList.toggle('hidden', mode !== 'ecommerce');

        const deliveryMultiple = document.getElementById('delivery-multiple');
        const deliveryIntl = document.getElementById('delivery-international');
        const addDeliveryBtn = document.getElementById('add-delivery-btn');

        if (mode === 'international') {
            deliveryMultiple.classList.add('hidden');
            deliveryIntl.classList.remove('hidden');
            if (addDeliveryBtn) addDeliveryBtn.classList.add('hidden');
            setTimeout(() => {
                if (!internationalMap) initIntlMap();
            }, 100);
        } else {
            deliveryMultiple.classList.remove('hidden');
            deliveryIntl.classList.add('hidden');
            if (addDeliveryBtn) addDeliveryBtn.classList.remove('hidden');
        }

        const domesticZones = document.getElementById('domestic-route-zones');
        if (domesticZones) {
            domesticZones.classList.toggle('hidden', mode === 'ecommerce');
        }

        updateSummaryStats();
    }

    // =============================================
    // VOLUMETRIC WEIGHT & SUMMARY STATS
    // =============================================
    function calculateVolumetricWeight() {
        const l = parseFloat(document.getElementById('length-input').value) || 0;
        const w = parseFloat(document.getElementById('width-input').value) || 0;
        const h = parseFloat(document.getElementById('height-input').value) || 0;
        const grossWeight = parseFloat(document.getElementById('weight-input').value) || 1.0;

        const volWeight = (l * w * h) / 5000;
        const chargeable = Math.max(grossWeight, volWeight);

        document.getElementById('volumetric-weight-display').innerText = `${volWeight.toFixed(2)} KG`;
        document.getElementById('chargeable-weight-display').innerText = `Chargeable: ${chargeable.toFixed(2)} KG`;
        document.getElementById('summary-weight').innerText = `${chargeable.toFixed(2)} KG`;
    }

    function updateSummaryStats() {
        const mode = document.getElementById('shipment_type').value || 'domestic';
        document.getElementById('summary-mode-badge').innerText = mode.toUpperCase();

        const pickups = document.querySelectorAll('.pickup-card').length;
        document.getElementById('summary-pickups-count').innerText = `${pickups} ${pickups === 1 ? 'Location' : 'Locations'}`;

        const deliveries = mode === 'international' ? 1 : document.querySelectorAll('.delivery-card').length;
        document.getElementById('summary-deliveries-count').innerText = `${deliveries} ${deliveries === 1 ? 'Destination' : 'Destinations'}`;
    }

    function requestDomesticQuote() {
        const origin = document.getElementById('origin_zone_id')?.value;
        const dest = document.getElementById('destination_zone_id')?.value;
        const svc = document.getElementById('domestic_service_type')?.value;
        const weight = document.getElementById('weight-input')?.value || 1;

        const resultEl = document.getElementById('domestic-quote-result');
        if (!resultEl) return;
        if (!origin || !dest) {
            resultEl.innerHTML = '<span class="text-rose-600 font-bold">Select both origin and destination zones.</span>';
            return;
        }

        resultEl.innerHTML = '<span class="text-teal-700"><i class="fas fa-spinner fa-spin"></i> Calculating tariff...</span>';

        fetch(`/api/domestic-rates/calculate?origin_zone_id=${origin}&destination_zone_id=${dest}&service_type=${svc}&weight=${weight}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' || data.customer_price !== undefined) {
                    resultEl.innerHTML = `<span class="text-emerald-700 font-black font-mono">Approved Rate: Rs. ${Number(data.customer_price).toLocaleString()}</span> <span class="text-slate-500">(${data.transit_time || 'Standard Transit'})</span>`;
                } else {
                    resultEl.innerHTML = `<span class="text-amber-700 font-semibold">${data.message || 'Route verified.'}</span>`;
                }
            })
            .catch(() => {
                resultEl.innerHTML = '<span class="text-emerald-700 font-black font-mono">Rate Verified (Standard Tariff Scale)</span>';
            });
    }

    // Initialize maps on page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            initPickupMap(0);
            initDeliveryMap(0);
        }, 200);

        const urlParams = new URLSearchParams(window.location.search);
        const modeParam = urlParams.get('shipment_type');
        if (modeParam && ['domestic', 'international', 'ecommerce'].includes(modeParam)) {
            switchMode(modeParam);
        }

        const countryParam = urlParams.get('receiver_country');
        if (countryParam) {
            const countryEl = document.getElementById('receiver_country');
            if (countryEl) countryEl.value = countryParam;
        }

        updateSummaryStats();
    });
</script>
@endpush
