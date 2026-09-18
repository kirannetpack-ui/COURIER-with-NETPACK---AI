@extends('layouts.app')

@section('title', 'Doorstep Pickup & Consignment Intake - NETPACK')
@section('page-title', 'Doorstep Pickup Desk')

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
@endphp

<div class="max-w-7xl mx-auto space-y-6" 
     x-data="{
         activeTab: '{{ request()->has('status') || request()->has('search') ? 'list' : 'form' }}',
         scope: 'inside_valley',
         serviceTier: 'flash',
         calcWeight: 1.0,
         hasKnownDestination: false,
         savedAddresses: {{ Js::from($savedAddressesJson) }},
         selectedAddressId: '',
         contactPersonName: '{{ addslashes($defaultName) }}',
         contactPhone: '{{ addslashes($defaultPhone) }}',
         pickupAddress: '{{ addslashes($defaultAddress) }}',
         pickupLandmark: '',
         pickupCity: 'Kathmandu',
         saveAddress: true,
         addressLabel: '',
         isSubmitting: false,

         selectSavedAddress(addr) {
             if (!addr) return;
             this.selectedAddressId = addr.id;
             this.contactPersonName = addr.contact_person_name;
             this.contactPhone = addr.contact_phone;
             this.pickupAddress = addr.pickup_address;
             this.pickupLandmark = addr.pickup_landmark || '';
             this.pickupCity = addr.pickup_city || 'Kathmandu';
             this.addressLabel = addr.label || '';
         },

         resetToNewAddress() {
             this.selectedAddressId = 'new';
             this.contactPersonName = '{{ addslashes($defaultName) }}';
             this.contactPhone = '{{ addslashes($defaultPhone) }}';
             this.pickupAddress = '';
             this.pickupLandmark = '';
             this.pickupCity = 'Kathmandu';
             this.addressLabel = '';
         },

         setScope(newScope) {
             this.scope = newScope;
             if (newScope === 'inside_valley') {
                 this.serviceTier = 'flash'; // Default to E-Commerce instant dispatch / Flash
             } else if (newScope === 'outside_valley') {
                 this.serviceTier = 'express';
             } else {
                 this.serviceTier = 'priority_express';
             }
         },

         get estimatedEstimate() {
             let weight = Math.max(0.1, parseFloat(this.calcWeight) || 1.0);
             let base = 0;
             let perKg = 0;
             
             if (this.scope === 'inside_valley') {
                 if (this.serviceTier === 'flash') {
                     base = 120;
                     perKg = 60;
                 } else if (this.serviceTier === 'same_day') {
                     base = 100;
                     perKg = 50;
                 } else {
                     base = 80;
                     perKg = 40;
                 }
             } else if (this.scope === 'outside_valley') {
                 if (this.serviceTier === 'express') {
                     base = 220;
                     perKg = 90;
                 } else if (this.serviceTier === 'himalayan') {
                     base = 350;
                     perKg = 150;
                 } else {
                     base = 160;
                     perKg = 70;
                 }
             } else {
                 if (this.serviceTier === 'priority_express') {
                     base = 2800;
                     perKg = 1200;
                 } else if (this.serviceTier === 'document') {
                     base = 1800;
                     perKg = 800;
                 } else {
                     base = 2200;
                     perKg = 950;
                 }
             }
             
             return Math.round(base + (Math.max(0, weight - 1) * perKg));
         },

         init() {
             if (this.savedAddresses.length > 0) {
                 let def = this.savedAddresses.find(a => a.is_default) || this.savedAddresses[0];
                 if (def) {
                     this.selectSavedAddress(def);
                 }
             }
         }
     }">

    <!-- Top Hero Header Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-teal-800/40">
        <!-- Subtle background glowing accents -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold tracking-wider uppercase bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <i class="fas fa-truck-pickup text-teal-400"></i>
                        Doorstep Intake Desk
                    </span>
                    <span class="text-xs text-slate-400">&bull; On-Demand Rider Collection & Parcel Processing</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span>Doorstep Pickup Service</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1.5 max-w-2xl leading-relaxed">
                    Schedule an immediate rider to collect parcels from your doorstep, office, or warehouse. 
                    <strong class="text-teal-300 font-semibold">Destination details are completely optional</strong> — hand over packages directly or declare destination upon collection.
                </p>
            </div>

            <!-- View Switcher Tabs -->
            <div class="flex items-center gap-2 bg-slate-950/70 p-1.5 rounded-2xl border border-slate-800 self-start lg:self-auto shadow-inner flex-wrap">
                <button type="button" 
                        @click="activeTab = 'form'"
                        :class="activeTab === 'form' ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:text-white font-medium'"
                        class="px-4 py-2.5 rounded-xl text-xs transition-all duration-150 flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Schedule Pickup</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'list'"
                        :class="activeTab === 'list' ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-md font-bold' : 'text-slate-400 hover:text-white font-medium'"
                        class="px-4 py-2.5 rounded-xl text-xs transition-all duration-150 flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>My Pickups</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black {{ $statusCounts['pending'] > 0 ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-slate-300' }}">
                        {{ $statusCounts['all'] ?? 0 }}
                    </span>
                </button>

                <a href="{{ route('shipments.create') }}" 
                   class="px-4 py-2.5 rounded-xl text-xs bg-slate-800/90 hover:bg-slate-700 text-teal-300 border border-slate-700/80 font-bold transition-all duration-150 flex items-center gap-2">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Full Consignment Console</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-check"></i>
            </div>
            <div class="text-xs">
                <h4 class="font-bold text-emerald-950 text-sm">Success</h4>
                <p class="mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

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

    <!-- ========================================================================= -->
    <!-- TAB 1: DEDICATED PICKUP COMPONENT FORM -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'form'" x-transition class="bg-white rounded-3xl shadow-sm border border-slate-200/90 overflow-hidden">
        
        <!-- Form Header -->
        <div class="px-6 py-5 bg-gradient-to-r from-slate-50 to-teal-50/40 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-lg shadow-sm">
                    <i class="fas fa-box-open"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Request Doorstep Collection</h2>
                    <p class="text-xs text-slate-500">Submit New Consignment Pickup Inquiry & Doorstep Collection</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-teal-100 text-teal-800 border border-teal-200">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-ping"></span>
                    Instant Rider Dispatch
                </span>
            </div>
        </div>

        <form action="{{ route('client.inquiries.store') }}" method="POST" class="p-6 sm:p-8 space-y-8" @submit="isSubmitting = true">
            @csrf

            <!-- STEP 1: DESTINATION SCOPE SELECTION -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">1</span>
                        <span>Select Delivery Destination Scope *</span>
                    </label>
                    <span class="text-[11px] text-teal-700 font-semibold bg-teal-50 px-2 py-0.5 rounded-md border border-teal-200">
                        Default: Inside Kathmandu Valley
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                    <!-- Scope 1: Inside Valley (Default) -->
                    <div @click="setScope('inside_valley')"
                         :class="scope === 'inside_valley' ? 'border-teal-600 bg-teal-50/60 ring-2 ring-teal-500/20 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                         class="border-2 rounded-2xl p-4 cursor-pointer transition-all duration-150 relative flex items-start gap-3.5">
                        <input type="radio" name="destination_scope" value="inside_valley" :checked="scope === 'inside_valley'" class="mt-1 text-teal-600 focus:ring-teal-500">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-black text-slate-900">Inside Kathmandu Valley</span>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-teal-600 text-white uppercase tracking-wider">Fastest</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-snug">Kathmandu, Lalitpur, Bhaktapur. ⚡ Instant Dispatch default.</p>
                        </div>
                    </div>

                    <!-- Scope 2: Nepal Inter-District -->
                    <div @click="setScope('outside_valley')"
                         :class="scope === 'outside_valley' ? 'border-teal-600 bg-teal-50/60 ring-2 ring-teal-500/20 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                         class="border-2 rounded-2xl p-4 cursor-pointer transition-all duration-150 relative flex items-start gap-3.5">
                        <input type="radio" name="destination_scope" value="outside_valley" :checked="scope === 'outside_valley'" class="mt-1 text-teal-600 focus:ring-teal-500">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-black text-slate-900 block">Nepal Inter-District</span>
                            <p class="text-[11px] text-slate-500 mt-1 leading-snug">Pokhara, Biratnagar, Butwal, Chitwan & All 7 Provinces.</p>
                        </div>
                    </div>

                    <!-- Scope 3: International Air Courier -->
                    <div @click="setScope('international')"
                         :class="scope === 'international' ? 'border-teal-600 bg-teal-50/60 ring-2 ring-teal-500/20 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                         class="border-2 rounded-2xl p-4 cursor-pointer transition-all duration-150 relative flex items-start gap-3.5">
                        <input type="radio" name="destination_scope" value="international" :checked="scope === 'international'" class="mt-1 text-teal-600 focus:ring-teal-500">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-black text-slate-900 block">International Air Courier</span>
                            <p class="text-[11px] text-slate-500 mt-1 leading-snug">Worldwide Gateways (USA, UK, Australia, UAE, 220+ Countries).</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: PICKUP DETAILS WITH AUTO-SAVED ADDRESS SELECTOR -->
            <div class="p-6 rounded-3xl bg-slate-50/80 border border-slate-200/90 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">2</span>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Pickup Location & Contact Details *</h3>
                    </div>
                    <span class="text-[11px] text-slate-500">
                        <i class="fas fa-bolt text-amber-500 mr-1"></i> Addresses are auto-saved for instant 1-click reuse
                    </span>
                </div>

                <!-- Saved Address Quick Picker Chips -->
                <div class="space-y-2">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 block">
                        Saved Address Book:
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- New Address Chip -->
                        <button type="button" 
                                @click="resetToNewAddress()"
                                :class="selectedAddressId === 'new' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'"
                                class="px-3 py-1.5 rounded-xl border text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fas fa-plus text-[10px]"></i>
                            <span>+ Enter New Address</span>
                        </button>

                        <!-- Dynamic Saved Addresses Chips -->
                        <template x-for="addr in savedAddresses" :key="addr.id">
                            <button type="button" 
                                    @click="selectSavedAddress(addr)"
                                    :class="selectedAddressId === addr.id ? 'bg-teal-600 text-white border-teal-600 shadow-xs font-bold' : 'bg-white text-slate-700 border-slate-200 hover:border-teal-400 hover:bg-teal-50/30'"
                                    class="px-3 py-1.5 rounded-xl border text-xs transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-location-dot text-[11px]" :class="selectedAddressId === addr.id ? 'text-teal-200' : 'text-teal-600'"></i>
                                <span x-text="addr.label"></span>
                                <span x-show="addr.is_default" class="text-[9px] uppercase px-1 py-0.2 rounded font-mono" :class="selectedAddressId === addr.id ? 'bg-teal-800 text-teal-200' : 'bg-slate-100 text-slate-500'">Default</span>
                            </button>
                        </template>

                        <span x-show="savedAddresses.length === 0" class="text-xs text-slate-400 italic">
                            (No saved addresses yet. Address entered below will be automatically saved.)
                        </span>
                    </div>
                </div>

                <!-- Input Fields: Contact Person, Mobile, Address & Landmark -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Contact Person Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Contact Person Name *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                            <input type="text" name="contact_person_name" x-model="contactPersonName" required
                                   placeholder="Full name of person handing over package" 
                                   class="w-full text-xs pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-medium text-slate-900 shadow-2xs">
                        </div>
                    </div>

                    <!-- Contact Mobile Number -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Mobile Number / Phone *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-phone text-xs"></i>
                            </div>
                            <input type="text" name="contact_phone" x-model="contactPhone" required
                                   placeholder="e.g. 9841000000 / 9801234567" 
                                   class="w-full text-xs pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-mono font-medium text-slate-900 shadow-2xs">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Pickup Address & Street -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Pickup Address & Street *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-map-pin text-xs"></i>
                            </div>
                            <input type="text" name="pickup_address" x-model="pickupAddress" required
                                   placeholder="e.g. Ward 4, Baluwatar, Marg 2" 
                                   class="w-full text-xs pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-medium text-slate-900 shadow-2xs">
                        </div>
                    </div>

                    <!-- Landmark / Prominent Spot -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Landmark / Area (Optional)
                        </label>
                        <input type="text" name="pickup_landmark" x-model="pickupLandmark"
                               placeholder="e.g. Near Russian Embassy / Opp Bank" 
                               class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-medium text-slate-900 shadow-2xs">
                    </div>
                </div>

                <!-- Date & Slot Schedule -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Scheduled Date *
                        </label>
                        <input type="date" name="scheduled_pickup_time" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required
                               class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-mono text-slate-900 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Time Slot *
                        </label>
                        <select name="pickup_slot" class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-medium text-slate-900 shadow-2xs">
                            <option value="flash" selected>⚡ Urgent Flash (Within 60 mins)</option>
                            <option value="morning">Morning (09:00 - 12:00)</option>
                            <option value="afternoon">Afternoon (12:00 - 15:00)</option>
                            <option value="evening">Evening (15:00 - 18:00)</option>
                        </select>
                    </div>

                    <!-- Auto-Save Address Controls -->
                    <div class="sm:col-span-2 md:col-span-1 flex flex-col justify-end">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-medium text-slate-700 py-1">
                            <input type="checkbox" name="save_address" value="1" x-model="saveAddress" class="rounded text-teal-600 focus:ring-teal-500">
                            <span>Auto-save / update in address book</span>
                        </label>
                        <div x-show="saveAddress && selectedAddressId === 'new'" x-transition class="mt-1">
                            <input type="text" name="address_label" x-model="addressLabel" placeholder="Address label (e.g. Office, Home)"
                                   class="w-full text-[11px] px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-teal-500 outline-none text-slate-800">
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: SERVICE TIER & PACKAGE SPECIFICATIONS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Package Specs (2 cols) -->
                <div class="lg:col-span-2 p-6 rounded-3xl border border-slate-200/90 bg-white space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">3</span>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Package Details & Service Tier *</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Package Classification -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Package Type *</label>
                            <select name="package_type" class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-900">
                                <option value="Parcel & Commercial Goods" selected>📦 Standard Parcel / Commercial Goods</option>
                                <option value="Legal & Business Documents">📄 Documents (Contracts, Passports, Envelopes)</option>
                                <option value="Fragile & Electronics">⚡ Fragile Electronics & Instruments</option>
                                <option value="Apparel & Samples">👗 Garments & Fabric Samples</option>
                                <option value="Perishable & Grocery">🍎 Perishable Foods / Grocery</option>
                            </select>
                        </div>

                        <!-- Estimated Weight -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Estimated Weight (KG) *</label>
                            <div class="relative">
                                <input type="number" step="0.5" min="0.1" name="estimated_weight_kg" x-model="calcWeight" required
                                       class="w-full text-xs pl-3 pr-12 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-mono font-bold text-slate-900">
                                <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-mono font-bold">KG</span>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Service Tier Selector based on Scope -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700">Select Service Tier SLA *</label>
                            <span x-show="scope === 'inside_valley'" class="text-[11px] font-bold text-teal-700 flex items-center gap-1">
                                <i class="fas fa-bolt text-amber-500"></i> Default: E-Commerce Instant Dispatch (Flash)
                            </span>
                        </div>

                        <!-- Scope: Inside Kathmandu Valley -->
                        <div x-show="scope === 'inside_valley'" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <!-- Flash / Instant Dispatch (DEFAULT) -->
                            <label :class="serviceTier === 'flash' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="flash" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-black text-slate-900 flex items-center gap-1">
                                        <span>⚡ Flash Express</span>
                                    </span>
                                    <span class="block text-[10px] text-teal-700 font-semibold mt-0.5">E-Commerce Instant Dispatch (60-90 Mins)</span>
                                </div>
                            </label>

                            <!-- Same-Day Delivery -->
                            <label :class="serviceTier === 'same_day' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="same_day" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">🚀 Same-Day Valley</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">Delivered within 4–6 Hours</span>
                                </div>
                            </label>

                            <!-- Standard Valley Delivery -->
                            <label :class="serviceTier === 'standard' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="standard" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">📦 Standard Valley</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">Next Working Day (24h SLA)</span>
                                </div>
                            </label>
                        </div>

                        <!-- Scope: Nepal Inter-District -->
                        <div x-show="scope === 'outside_valley'" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label :class="serviceTier === 'express' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="express" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">✈️ Express Linehaul</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">24–48 Hours Hub-to-Hub</span>
                                </div>
                            </label>

                            <label :class="serviceTier === 'standard' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="standard" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">🚚 Standard Surface</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">2–4 Days Highway Network</span>
                                </div>
                            </label>

                            <label :class="serviceTier === 'himalayan' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="himalayan" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">🏔️ Himalayan Cargo</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">3–7 Days Mountain Transit</span>
                                </div>
                            </label>
                        </div>

                        <!-- Scope: International Air Courier -->
                        <div x-show="scope === 'international'" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label :class="serviceTier === 'priority_express' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="priority_express" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">⚡ Priority Express</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">3–5 Working Days Global</span>
                                </div>
                            </label>

                            <label :class="serviceTier === 'economy' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="economy" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">✈️ Economy Cargo</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">5–8 Working Days Air Cargo</span>
                                </div>
                            </label>

                            <label :class="serviceTier === 'document' ? 'border-teal-600 bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300'"
                                   class="border-2 rounded-xl p-3 cursor-pointer transition flex items-start gap-2.5">
                                <input type="radio" name="service_tier" value="document" x-model="serviceTier" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">📄 Document Express</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">Global Business Documents</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Special Handling Instructions -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Special Handling Instructions (Optional)</label>
                        <input type="text" name="instructions" 
                               placeholder="e.g. Call 10 mins before arrival, handle with extra care, gate passcode #102" 
                               class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-slate-800">
                    </div>
                </div>

                <!-- Estimated Tariff Card (1 col) -->
                <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 text-white shadow-lg border border-teal-800/40 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-teal-800/40">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-teal-300">Live Tariff Quote</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-teal-500/20 text-teal-300 font-mono font-bold">Standard Scale</span>
                    </div>

                    <div class="text-center py-2">
                        <span class="text-xs text-slate-300 block">Estimated Courier Freight</span>
                        <div class="text-3xl sm:text-4xl font-black text-white font-mono mt-1">
                            Rs. <span x-text="estimatedEstimate.toLocaleString()"></span>
                        </div>
                        <span class="text-[10px] text-teal-300 mt-1 block">Subject to final scale verification at hub</span>
                    </div>

                    <div class="pt-3 border-t border-teal-800/40 space-y-2 text-xs text-slate-300">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Selected Scope:</span>
                            <span class="font-bold text-white capitalize" x-text="scope.replace('_', ' ')"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Service SLA:</span>
                            <span class="font-mono font-bold text-teal-300 uppercase" x-text="serviceTier"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Est. Weight:</span>
                            <span class="font-mono font-bold text-white"><span x-text="calcWeight"></span> KG</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-teal-500/10 border border-teal-500/20 text-[11px] text-teal-200 leading-snug flex items-center gap-2">
                        <i class="fas fa-shield-check text-teal-400"></i>
                        <span>Includes automated GPS tracking & verified on-site collection receipt.</span>
                    </div>
                </div>
            </div>

            <!-- STEP 4: DESTINATION DETAILS (STRICTLY OPTIONAL) -->
            <div class="p-6 rounded-3xl border border-slate-200/90 bg-slate-50/50 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/70">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-400 text-white text-[10px] flex items-center justify-center font-bold">4</span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <span>Destination Consignee Details</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    OPTIONAL
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                You can leave this blank for open collection. Hand parcels to courier or declare upon intake.
                            </p>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-slate-700">
                        <input type="checkbox" x-model="hasKnownDestination" class="rounded text-teal-600 focus:ring-teal-500">
                        <span>I have destination consignee details</span>
                    </label>
                </div>

                <!-- Collapsible Optional Destination Fields -->
                <div x-show="hasKnownDestination" x-transition class="space-y-4 pt-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Recipient Full Name (Optional)</label>
                            <input type="text" name="recipient_name" 
                                   placeholder="e.g. Sujan Shrestha / Company Receiver" 
                                   class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Recipient Mobile / Phone (Optional)</label>
                            <input type="text" name="recipient_phone" 
                                   placeholder="e.g. 9800000000 or International number" 
                                   class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Destination City / Country (Optional)</label>
                            <input type="text" name="delivery_city" 
                                   placeholder="e.g. Pokhara, Biratnagar, or New York, USA" 
                                   class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Detailed Delivery Address (Optional)</label>
                            <input type="text" name="delivery_address" 
                                   placeholder="Street address, ward number, or drop point" 
                                   class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                    </div>
                </div>

                <div x-show="!hasKnownDestination" class="p-3 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
                    <i class="fas fa-info-circle text-teal-600"></i>
                    <span>Destination omitted: Assigned courier will collect parcels and verify final routing stickers on-site.</span>
                </div>
            </div>

            <!-- SUBMIT BUTTON & CONFIRMATION -->
            <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="text-xs text-slate-500 flex items-center gap-2">
                    <i class="fas fa-check-circle text-teal-600 text-sm"></i>
                    <span>Doorstep courier collection dispatched as per scheduled SLA. Real-time notifications sent via SMS.</span>
                </div>

                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-8 py-3.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-lg shadow-teal-900/20 transition-all duration-150 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                    <i class="fas fa-paper-plane" x-show="!isSubmitting"></i>
                    <i class="fas fa-spinner fa-spin" x-show="isSubmitting"></i>
                    <span x-text="isSubmitting ? 'Scheduling Pickup...' : 'Schedule Doorstep Pickup'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: MY PICKUPS & INQUIRIES LIST -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'list'" x-transition class="space-y-4">
        <!-- Status Filter Tabs & Search -->
        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200/90 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <a href="{{ route('client.inquiries', ['status' => 'all']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ !request('status') || request('status') === 'all' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                    All ({{ $statusCounts['all'] ?? 0 }})
                </a>
                <a href="{{ route('client.inquiries', ['status' => 'pending']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ request('status') === 'pending' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                    Pending ({{ $statusCounts['pending'] ?? 0 }})
                </a>
                <a href="{{ route('client.inquiries', ['status' => 'assigned']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ request('status') === 'assigned' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                    Assigned ({{ $statusCounts['assigned'] ?? 0 }})
                </a>
                <a href="{{ route('client.inquiries', ['status' => 'picked_up']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ request('status') === 'picked_up' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                    Picked Up ({{ $statusCounts['picked_up'] ?? 0 }})
                </a>
                <a href="{{ route('client.inquiries', ['status' => 'in_transit']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ request('status') === 'in_transit' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                    In Transit ({{ $statusCounts['in_transit'] ?? 0 }})
                </a>
                <a href="{{ route('client.inquiries', ['status' => 'delivered']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ request('status') === 'delivered' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                    Delivered ({{ $statusCounts['delivered'] ?? 0 }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('client.inquiries') }}" method="GET" class="relative max-w-xs w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search reference or pickup address..." 
                       class="w-full text-xs pl-8 pr-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
                <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
            </form>
        </div>

        <!-- Inquiries Cards Grid -->
        @if($inquiries->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($inquiries as $inquiry)
                    @php
                        $st = strtolower($inquiry->status ?? 'pending');
                        $badge = match($st) {
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'assigned' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'picked_up' => 'bg-teal-100 text-teal-800 border-teal-200',
                            'in_transit' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-slate-100 text-slate-800 border-slate-200'
                        };
                    @endphp
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/90 hover:border-teal-500/50 hover:shadow-md transition-all duration-150 flex flex-col justify-between">
                        <div>
                            <!-- Card Header -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 gap-2">
                                <div>
                                    <span class="font-mono font-black text-xs text-slate-900 flex items-center gap-1.5">
                                        <i class="fas fa-barcode text-teal-600"></i>
                                        {{ $inquiry->tracking_number ?? ('#REQ-' . $inquiry->id) }}
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $inquiry->created_at ? $inquiry->created_at->format('M d, Y (h:i A)') : '' }}
                                    </p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $badge }} flex-shrink-0">
                                    {{ str_replace('_', ' ', $inquiry->status) }}
                                </span>
                            </div>

                            <!-- Route & Contact Details -->
                            <div class="py-3 space-y-2.5 text-xs">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pickup From:</span>
                                    <p class="text-slate-800 font-semibold truncate mt-0.5">
                                        <i class="fas fa-location-dot text-teal-600 text-[10px] mr-1"></i>
                                        {{ $inquiry->pickup_address }}
                                    </p>
                                    @if($inquiry->contact_person_name || $inquiry->contact_person_phone)
                                        <p class="text-[11px] text-slate-500 mt-0.5 truncate pl-3.5">
                                            Contact: {{ $inquiry->contact_person_name }} ({{ $inquiry->contact_person_phone }})
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Destination:</span>
                                    <p class="text-slate-900 font-bold truncate mt-0.5">
                                        <i class="fas fa-arrow-right-long text-teal-600 text-[10px] mr-1"></i>
                                        @if(!empty($inquiry->delivery_address))
                                            <span>{{ $inquiry->delivery_address }}</span>
                                            @if($inquiry->customer_name)
                                                <span class="text-slate-500 font-normal">({{ $inquiry->customer_name }})</span>
                                            @endif
                                        @elseif(!empty($inquiry->delivery_city) && $inquiry->delivery_city !== 'Open Destination / Hub Intake')
                                            <span>{{ $inquiry->delivery_city }}</span>
                                        @else
                                            <span class="text-slate-500 italic font-normal">
                                                <i class="fas fa-box-open text-sky-500 text-[10px]"></i> Open Destination (Hub Declared)
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center justify-between pt-1 text-[11px] text-slate-600">
                                    <span class="truncate max-w-[180px]">
                                        <i class="fas fa-box text-slate-400 mr-1"></i>
                                        {{ $inquiry->items_description ?? 'Standard Goods' }}
                                    </span>
                                    <span class="font-mono font-bold text-slate-800 flex-shrink-0">
                                        {{ number_format($inquiry->estimated_weight_kg ?? 1.0, 1) }} KG
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Actions -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono">
                                {{ strtoupper(str_replace('_', ' ', $inquiry->service_tier ?? 'FLASH')) }}
                            </span>

                            @if($inquiry->tracking_number)
                                <a href="{{ route('tracking.show', $inquiry->tracking_number) }}" 
                                   class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-[11px] font-bold transition flex items-center gap-1.5 shadow-2xs">
                                    <i class="fas fa-satellite-dish"></i>
                                    <span>Track Pickup</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $inquiries->withQueryString()->links() }}
            </div>
        @else
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/90 shadow-sm">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-teal-50 text-teal-600 flex items-center justify-center text-3xl mb-4">
                    <i class="fas fa-truck-pickup"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">No Pickup Requests Found</h3>
                <p class="text-xs text-slate-500 mt-1.5 max-w-md mx-auto">
                    You have not scheduled any doorstep pickups under this filter. Submit a new collection request above and our assigned dispatch rider will reach your doorstep!
                </p>
                <button type="button" @click="activeTab = 'form'" 
                        class="mt-5 px-6 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white rounded-2xl text-xs font-bold transition inline-flex items-center gap-2 shadow-sm cursor-pointer">
                    <i class="fas fa-plus"></i>
                    <span>Schedule Your First Pickup</span>
                </button>
            </div>
        @endif
    </div>
</div>
@endsection
