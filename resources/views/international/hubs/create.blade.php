@extends('layouts.app')

@section('title', 'Create International Hub | Netpack')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('international.hubs.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 mb-1">
                <i class="fas fa-arrow-left"></i> Back to All Hubs
            </a>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <span>Create International Gateway Hub</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Multi-Partner Enabled
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Configure international hubs with pre-defined main delivery areas, transit corridors, and multiple operating partner agencies.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" 
                    onclick="triggerAutoCoverage()" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-indigo-600 text-white text-xs font-bold shadow-md hover:from-teal-400 hover:to-indigo-500 transition cursor-pointer">
                <i class="fas fa-wand-magic-sparkles text-amber-300"></i>
                <span>Auto-Figure Out Coverage</span>
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-600 p-4 rounded-xl text-sm space-y-1">
            <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="createHubForm" action="{{ route('international.hubs.store') }}" method="POST" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-8">
        @csrf

        <!-- SECTION 1: HUB IDENTITY & CLASSIFICATION -->
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                    <i class="fas fa-network-wired"></i> 1. Hub Identification & Gateway Parameters
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">Step 1 of 4</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Hub Code (IATA 3-Letter) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="hub_code" name="code" value="{{ old('code') }}" placeholder="e.g. DXB, LHR, FRA, SYD" required maxlength="10" 
                           class="w-full uppercase font-mono font-bold tracking-wider text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                        <span class="text-[10px] text-slate-400">Presets:</span>
                        <button type="button" onclick="loadPresetHub('DXB', 'United Arab Emirates')" class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-indigo-600 rounded font-bold">DXB</button>
                        <button type="button" onclick="loadPresetHub('LHR', 'United Kingdom')" class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-indigo-600 rounded font-bold">LHR</button>
                        <button type="button" onclick="loadPresetHub('FRA', 'Germany')" class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-indigo-600 rounded font-bold">FRA</button>
                        <button type="button" onclick="loadPresetHub('SYD', 'Australia')" class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-indigo-600 rounded font-bold">SYD</button>
                        <button type="button" onclick="loadPresetHub('AKL', 'New Zealand')" class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-indigo-600 rounded font-bold">AKL</button>
                        <button type="button" onclick="loadPresetHub('JFK', 'United States')" class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-indigo-600 rounded font-bold">JFK</button>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Hub Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="hub_name" name="name" value="{{ old('name') }}" placeholder="e.g. DUBAI GLOBAL AIR HUB & GULF GATEWAY" required 
                           class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Hub Classification <span class="text-rose-500">*</span>
                    </label>
                    <select name="hub_type" id="hub_type" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="main_hub" {{ old('hub_type', 'main_hub') === 'main_hub' ? 'selected' : '' }}>Main Gateway (Primary consolidation & customs hub)</option>
                        <option value="transit_point" {{ old('hub_type') === 'transit_point' ? 'selected' : '' }}>Transit Point (Intermediate sorting & feeder hub)</option>
                        <option value="sorting_center" {{ old('hub_type') === 'sorting_center' ? 'selected' : '' }}>Regional Sorting Center</option>
                        <option value="delivery_hub" {{ old('hub_type') === 'delivery_hub' ? 'selected' : '' }}>Last-Mile Delivery Depot</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Customs Clearance Mode <span class="text-rose-500">*</span>
                    </label>
                    <select name="mode_type" id="mode_type" class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="DDP" {{ old('mode_type') == 'DDP' ? 'selected' : '' }}>DDP (Delivered Duty Paid - Duties Pre-cleared)</option>
                        <option value="DDU" {{ old('mode_type') == 'DDU' ? 'selected' : '' }}>DDU (Delivered Duty Unpaid - Consignee Pays)</option>
                        <option value="HYBRID" {{ old('mode_type', 'HYBRID') == 'HYBRID' ? 'selected' : '' }}>HYBRID (Supports both DDP & DDU depending on destination)</option>
                        <option value="DDP & Cross Worldwide" {{ old('mode_type') == 'DDP & Cross Worldwide' ? 'selected' : '' }}>DDP & Cross Worldwide (Gulf DDP + Global Cross-Dock)</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_mandatory" id="is_mandatory" value="1" {{ old('is_mandatory') ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            Mandatory Regional Hub
                            <span class="block text-[10px] text-slate-400 font-normal">Auto-enforced for all consignments routed to this sector</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- SECTION 2: LOCATION & AIRPORT PRECINCT -->
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                    <i class="fas fa-plane-departure"></i> 2. Location & Airport Cargo Terminal
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">Step 2 of 4</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Country <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="country" name="country" value="{{ old('country') }}" placeholder="e.g. United Arab Emirates" required 
                           onblur="if(!document.getElementById('main_delivery_countries').value) triggerAutoCoverage();"
                           class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        City / Municipality <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="e.g. Dubai" required 
                           class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
                        Airport Name / Cargo Terminal
                    </label>
                    <input type="text" id="airport_name" name="airport_name" value="{{ old('airport_name') }}" placeholder="e.g. Dubai Cargo Village (DXB)" 
                           class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- SECTION 3: PRE-DEFINED MAIN DELIVERY AREAS VS TRANSIT SERVICES -->
        <div class="rounded-2xl border border-indigo-100 dark:border-indigo-950 bg-indigo-50/20 p-5 space-y-5">
            <div class="flex items-center justify-between border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                        <i class="fas fa-map-location-dot text-indigo-600"></i> 3. Pre-defined Coverage: Main Delivery Areas vs Transit Services
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Our system auto-determines whether destination shipments receive direct doorstep delivery through this hub or transit onwards via regional cross-docking.
                    </p>
                </div>
                <button type="button" onclick="triggerAutoCoverage()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    <i class="fas fa-rotate"></i> Auto-Detect
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Card A: Main Delivery Areas -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-emerald-200 dark:border-emerald-800/40 p-4 space-y-3 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                A. Main Delivery Areas (Direct Doorstep)
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                            Primary Doorstep Handover
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Countries where this hub and its operating partner agencies execute full direct customs clearance, domestic linehaul, and direct consignee delivery.
                    </p>
                    <textarea id="main_delivery_countries" name="main_delivery_countries" rows="3" placeholder="e.g. United Arab Emirates, Saudi Arabia, Qatar, Oman, Kuwait, Bahrain"
                              class="w-full text-xs font-medium px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500">{{ old('main_delivery_countries') }}</textarea>
                    
                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                        <span class="text-[10px] text-slate-400">Quick Add:</span>
                        <button type="button" onclick="appendCountry('main_delivery_countries', 'United Arab Emirates, Saudi Arabia, Qatar, Oman, Kuwait, Bahrain')" class="text-[10px] px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded font-semibold">+ GCC (6)</button>
                        <button type="button" onclick="appendCountry('main_delivery_countries', 'United Kingdom, Ireland')" class="text-[10px] px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded font-semibold">+ UK & Ireland</button>
                        <button type="button" onclick="appendCountry('main_delivery_countries', 'Germany, Poland, Austria, France, Netherlands, Belgium')" class="text-[10px] px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded font-semibold">+ Central EU</button>
                        <button type="button" onclick="appendCountry('main_delivery_countries', 'Australia, New Zealand')" class="text-[10px] px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded font-semibold">+ Australia & NZ</button>
                    </div>
                </div>

                <!-- Card B: Transit Services -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-200 dark:border-blue-800/40 p-4 space-y-3 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                B. Transit Services (Cross-Dock & Feeder)
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full">
                            Transit Corridors
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Countries reachable via intermediate sorting, flight transit, airport transfer (e.g. DXB transshipment, LHR transatlantic, FRA Schengen road feeder).
                    </p>
                    <textarea id="transit_countries" name="transit_countries" rows="3" placeholder="e.g. Canada, United States, United Kingdom, Germany, Australia, Worldwide Cross-Docking"
                              class="w-full text-xs font-medium px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">{{ old('transit_countries') }}</textarea>
                    
                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                        <span class="text-[10px] text-slate-400">Quick Add:</span>
                        <button type="button" onclick="appendCountry('transit_countries', 'Canada, United States')" class="text-[10px] px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-800 rounded font-semibold">+ North America</button>
                        <button type="button" onclick="appendCountry('transit_countries', 'Sweden, Denmark, Italy, Spain, Switzerland, Poland')" class="text-[10px] px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-800 rounded font-semibold">+ Rest of Europe</button>
                        <button type="button" onclick="appendCountry('transit_countries', 'Worldwide Cross-Docking via Tier-1 Carriers')" class="text-[10px] px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-800 rounded font-semibold">+ Global Cross-Dock</button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                    Service Corridors / Operational Routes Description
                </label>
                <input type="text" id="service_routes" name="service_routes" value="{{ old('service_routes') }}" placeholder="e.g. Gulf Express DDP, European Road Feeder, Transatlantic Air Express" 
                       class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
            </div>
        </div>

        <!-- SECTION 4: MULTIPLE PARTNERS & AGENCIES (MANY-TO-MANY) -->
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                        <i class="fas fa-handshake"></i> 4. Operating Partners & Agencies (Multiple Partners per Hub)
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Attach multiple partner agencies to this hub. The same partner can also provide clearance and delivery services across multiple hubs.
                    </p>
                </div>
                <span class="text-[11px] text-slate-400 font-medium">Step 4 of 4</span>
            </div>

            <!-- Existing Partners Multi-Selection -->
            @if(isset($allAgencies) && $allAgencies->count() > 0)
                <div class="space-y-3 mb-6">
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
                        Select Existing Partner Agencies Operating at this Hub:
                    </label>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($allAgencies as $agy)
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition">
                                <input type="checkbox" name="partner_agency_ids[]" value="{{ $agy->id }}" 
                                       {{ in_array($agy->id, (array) old('partner_agency_ids', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 mt-0.5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                                <div class="min-w-0 flex-1">
                                    <span class="font-bold block text-xs text-slate-900 dark:text-white truncate">{{ $agy->name }}</span>
                                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-mono block">{{ $agy->code }} &bull; {{ $agy->country }}</span>
                                    <span class="text-[10px] text-slate-400 block truncate">{{ $agy->city }} &bull; {{ $agy->phone }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Register New Partner Agency Inline -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-800/40 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-plus-circle text-indigo-600"></i>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Register New Partner Agency for this Hub (Optional)
                        </h4>
                    </div>
                    <span class="text-[10px] text-slate-400">Creates new credentials automatically</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Partner Agency Name</label>
                        <input type="text" id="handling_agency_name" name="handling_agency_name" value="{{ old('handling_agency_name') }}" placeholder="e.g. Gulf Express Clearance Services LLC" 
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Contact Person & Role</label>
                        <input type="text" id="agency_contact_person" name="agency_contact_person" value="{{ old('agency_contact_person') }}" placeholder="e.g. Operations Manager / Tariq Al-Mansoor" 
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Operations Phone</label>
                        <input type="text" id="agency_phone" name="agency_phone" value="{{ old('agency_phone') }}" placeholder="e.g. +971-4-1234567" 
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Dispatch / Pre-alert Email</label>
                        <input type="email" id="agency_email" name="agency_email" value="{{ old('agency_email') }}" placeholder="e.g. ops@clearance-partner.com" 
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Facility / Warehouse Address</label>
                        <input type="text" id="agency_address" name="agency_address" value="{{ old('agency_address') }}" placeholder="e.g. Freight Gate 5, Cargo Terminal 2" 
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    </div>
                </div>

                <!-- Container for dynamically added extra partners -->
                <div id="extraPartnersContainer" class="space-y-3 pt-2"></div>

                <div class="pt-2">
                    <button type="button" onclick="addExtraPartnerRow()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-plus"></i>
                        <span>+ Add Another Partner to This Hub</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- SECTION 5: DISPLAY & SORT STATUS -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                <label for="is_active" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Hub is active and immediately available for routing, tracking, and flight manifests
                </label>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase text-slate-500">Sort Order:</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" 
                       class="w-20 text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
            </div>
        </div>

        <!-- Submission Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('international.hubs.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-black tracking-wide shadow-lg shadow-indigo-600/30 transition">
                <i class="fas fa-save"></i> Save International Gateway Hub
            </button>
        </div>
    </form>
</div>

<!-- CLIENT JAVASCRIPT FOR AUTO-FIGURE OUT & DYNAMIC MULTI-PARTNERS -->
<script>
    let extraPartnerIndex = 0;

    function appendCountry(targetId, countries) {
        const el = document.getElementById(targetId);
        if (!el) return;
        const current = el.value.trim();
        if (!current) {
            el.value = countries;
        } else {
            const existing = current.split(',').map(s => s.trim().toLowerCase());
            const toAdd = countries.split(',').map(s => s.trim()).filter(s => !existing.includes(s.toLowerCase()));
            if (toAdd.length > 0) {
                el.value = current + ', ' + toAdd.join(', ');
            }
        }
    }

    function loadPresetHub(code, country) {
        document.getElementById('hub_code').value = code;
        document.getElementById('country').value = country;
        triggerAutoCoverage();
    }

    function triggerAutoCoverage() {
        const code = document.getElementById('hub_code').value.trim();
        const country = document.getElementById('country').value.trim();

        if (!code && !country) {
            alert('Please enter a Hub Code (e.g. DXB, LHR, FRA, SYD) or Country to auto-detect coverage.');
            return;
        }

        fetch(`{{ route('international.hubs.auto-coverage') }}?code=${encodeURIComponent(code)}&country=${encodeURIComponent(country)}`)
            .then(res => res.json())
            .then(data => {
                if (data.country && !document.getElementById('country').value) {
                    document.getElementById('country').value = data.country;
                }
                if (data.city && !document.getElementById('city').value) {
                    document.getElementById('city').value = data.city;
                }
                if (data.airport_name) {
                    document.getElementById('airport_name').value = data.airport_name;
                }
                if (data.hub_code && !document.getElementById('hub_code').value) {
                    document.getElementById('hub_code').value = data.hub_code;
                }
                if (data.hub_name && !document.getElementById('hub_name').value) {
                    document.getElementById('hub_name').value = data.hub_name || `${data.city} (${data.hub_code}) - Gateway Hub`;
                }
                if (data.main_delivery_countries && Array.isArray(data.main_delivery_countries)) {
                    document.getElementById('main_delivery_countries').value = data.main_delivery_countries.join(', ');
                }
                if (data.transit_countries && Array.isArray(data.transit_countries)) {
                    document.getElementById('transit_countries').value = data.transit_countries.join(', ');
                }
                if (data.service_routes) {
                    document.getElementById('service_routes').value = data.service_routes;
                }
                if (data.mode_type && document.getElementById('mode_type')) {
                    document.getElementById('mode_type').value = data.mode_type;
                }
                if (data.suggested_partners && data.suggested_partners.length > 0) {
                    const first = data.suggested_partners[0];
                    if (!document.getElementById('handling_agency_name').value) {
                        document.getElementById('handling_agency_name').value = first.name || '';
                        document.getElementById('agency_contact_person').value = first.contact || '';
                        document.getElementById('agency_phone').value = first.phone || '';
                        document.getElementById('agency_email').value = first.email || '';
                    }
                }
            })
            .catch(err => console.error('Auto coverage error:', err));
    }

    function addExtraPartnerRow() {
        extraPartnerIndex++;
        const container = document.getElementById('extraPartnersContainer');
        const row = document.createElement('div');
        row.id = `extraPartnerRow_${extraPartnerIndex}`;
        row.className = 'p-3 rounded-xl border border-indigo-200 dark:border-indigo-900 bg-indigo-50/40 dark:bg-slate-900/50 space-y-3 relative';
        row.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300">
                    Additional Partner #${extraPartnerIndex + 1}
                </span>
                <button type="button" onclick="document.getElementById('extraPartnerRow_${extraPartnerIndex}').remove()" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                    <i class="fas fa-trash-alt"></i> Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Partner Agency Name</label>
                    <input type="text" name="additional_partners[${extraPartnerIndex}][name]" placeholder="e.g. Regional Linehaul Partner" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Contact Person & Role</label>
                    <input type="text" name="additional_partners[${extraPartnerIndex}][contact_person]" placeholder="e.g. Operations Coordinator" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Phone</label>
                    <input type="text" name="additional_partners[${extraPartnerIndex}][phone]" placeholder="+000-0000" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Email</label>
                    <input type="email" name="additional_partners[${extraPartnerIndex}][email]" placeholder="ops@partner.com" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                </div>
            </div>
        `;
        container.appendChild(row);
    }
</script>
@endsection
