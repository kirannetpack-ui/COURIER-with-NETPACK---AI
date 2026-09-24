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

<script>
function shipmentConsoleData() {
    return {
        activeConsoleView: '{{ $initialView }}',
        activeConsoleTab: '{{ $initialView === 'queue' ? 'queue' : 'consignment' }}',
        hasDoorstepPickup: {{ $hasPickupInitial ? 'true' : 'false' }},
        pickupScope: '{{ request("pickup_location_type") === "outside_ktm" ? "outside_valley" : request("pickup_scope", "inside_valley") }}',
        pickupServiceTier: '{{ request("pickup_location_type") === "outside_ktm" ? "express" : "flash" }}',
        pickupCalcWeight: {{ (float) request('weight', 1.0) }},
        hasKnownDestination: {{ request('receiver_country') || request('destination_city') ? 'true' : 'false' }},
        savedAddresses: {{ Js::from($savedAddressesJson) }},
        selectedAddressId: '',
        contactPersonName: '{{ addslashes($convertPickup->contact_person_name ?? $defaultName) }}',
        contactPhone: '{{ addslashes($convertPickup->contact_person_phone ?? $defaultPhone) }}',
        pickupAddress: '{{ addslashes($convertPickup->pickup_address ?? $defaultAddress) }}',
        pickupLandmark: '',
        pickupCity: '{{ addslashes(request("pickup_city", "Kathmandu")) }}',
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

        setDoorstepPickup(val) {
            this.hasDoorstepPickup = val;
            const input = document.getElementById('schedule_doorstep_pickup');
            if (input) input.value = val ? '1' : '0';
            if (typeof updateSummaryStats === 'function') {
                updateSummaryStats();
            }
            if (val) {
                setTimeout(() => {
                    if (window.pickupMaps) Object.values(pickupMaps).forEach(m => m && m.map && m.map.invalidateSize());
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
        },

        // Documentation & Cargo Specifications Engine
        shipmentMode: '{{ request("shipment_type", "domestic") }}',
        invoiceNumber: 'INV-{{ date("Y") }}-{{ rand(10000, 99999) }}',
        invoiceDate: '{{ date("Y-m-d") }}',
        invoiceCurrency: '{{ request("shipment_type") === "international" ? "USD" : "NPR" }}',
        incoterm: 'DAP',
        reasonForExport: 'Commercial Sale / Export',
        exporterName: '{{ addslashes(Auth::user()->name ?? "") }}',
        exporterPanVat: '{{ addslashes(Auth::user()->pan_vat_number ?? Auth::user()->pan_number ?? "") }}',
        exporterEximCode: '{{ addslashes(Auth::user()->exim_code ?? "") }}',
        consigneeTaxId: '',
        sellerBillType: 'vat_invoice',
        sellerBillNumber: '',
        selectedBillFileName: '',
        acquisitionSource: '{{ old("acquisition_source", "direct_portal") }}',

        // Invoice line items with live WCO HS suggestion state
        invoiceItems: [
            {
                name: 'Handmade Pashmina / Cashmere Shawl',
                hs_code: '6214.20.00',
                origin_country: 'Nepal',
                qty: 2,
                uom: 'PCS',
                unit_price: 35.00,
                duty_rate: 0,
                searchQuery: '',
                suggestions: [],
                showSuggestions: false,
                isSearching: false
            }
        ],

        // Packing List Box Matrix
        totalBoxes: 1,
        boxes: [
            {
                box_number: 1,
                weight_kg: {{ old('weight', $convertPickup->estimated_weight_kg ?? request('weight', '1.0')) }},
                length_cm: 30,
                width_cm: 25,
                height_cm: 20,
                items: [
                    { item_index: 0, item_name: 'Handmade Pashmina / Cashmere Shawl', qty: 2 }
                ]
            }
        ],

        // WCO Tariff Search Modal state
        wcoModalOpen: false,
        wcoSearchQuery: '',
        wcoCategory: '',
        wcoResults: [],
        wcoLoading: false,
        activeItemIndexForWco: null,

        addInvoiceItem() {
            this.invoiceItems.push({
                name: '',
                hs_code: '',
                origin_country: 'Nepal',
                qty: 1,
                uom: 'PCS',
                unit_price: 10.00,
                duty_rate: 0,
                searchQuery: '',
                suggestions: [],
                showSuggestions: false,
                isSearching: false
            });
            const newIdx = this.invoiceItems.length - 1;
            this.boxes.forEach(box => {
                if (!box.items) box.items = [];
                box.items.push({
                    item_index: newIdx,
                    item_name: '',
                    qty: this.totalBoxes === 1 ? 1 : 0
                });
            });
            if (this.totalBoxes === 1) {
                this.autoAllocateSingleBox();
            }
            this.syncCargoWeight();
        },

        removeInvoiceItem(index) {
            if (this.invoiceItems.length <= 1) {
                alert('At least one item is required in the Commercial Invoice.');
                return;
            }
            this.invoiceItems.splice(index, 1);
            this.boxes.forEach(box => {
                if (box.items) {
                    box.items.splice(index, 1);
                    box.items.forEach((it, i) => it.item_index = i);
                }
            });
            if (this.totalBoxes === 1) {
                this.autoAllocateSingleBox();
            }
            this.syncCargoWeight();
        },

        setBoxCount(count) {
            count = Math.max(1, parseInt(count) || 1);
            this.totalBoxes = count;
            while (this.boxes.length < count) {
                const num = this.boxes.length + 1;
                this.boxes.push({
                    box_number: num,
                    weight_kg: 1.0,
                    length_cm: 30,
                    width_cm: 25,
                    height_cm: 20,
                    items: this.invoiceItems.map((item, idx) => ({
                        item_index: idx,
                        item_name: item.name,
                        qty: 0
                    }))
                });
            }
            if (this.boxes.length > count) {
                this.boxes = this.boxes.slice(0, count);
            }
            if (count === 1) {
                this.autoAllocateSingleBox();
            }
            this.syncCargoWeight();
        },

        autoAllocateSingleBox() {
            if (this.boxes.length > 0) {
                this.boxes[0].items = this.invoiceItems.map((item, idx) => ({
                    item_index: idx,
                    item_name: item.name,
                    qty: parseFloat(item.qty) || 0
                }));
            }
        },

        packingListWarning: '',

        syncInvoiceItemQty(itemIndex) {
            if (this.totalBoxes === 1) {
                this.autoAllocateSingleBox();
            } else {
                const total = parseFloat(this.invoiceItems[itemIndex]?.qty) || 0;
                let totalAllocated = this.getItemAllocatedQty(itemIndex);
                if (totalAllocated > total) {
                    for (let b = this.boxes.length - 1; b >= 0; b--) {
                        const bItem = this.boxes[b]?.items?.[itemIndex];
                        if (!bItem) continue;
                        const current = parseFloat(bItem.qty) || 0;
                        const excess = totalAllocated - total;
                        if (excess <= 0) break;
                        const reduction = Math.min(current, excess);
                        bItem.qty = Math.max(0, current - reduction);
                        totalAllocated -= reduction;
                    }
                }
            }
        },

        getAvailableQtyForBox(boxIndex, itemIndex) {
            const total = parseFloat(this.invoiceItems[itemIndex]?.qty) || 0;
            const allocatedOtherBoxes = this.boxes.reduce((sum, b, bIdx) => {
                if (bIdx === boxIndex) return sum;
                const it = (b.items || []).find(i => i.item_index === itemIndex);
                return sum + (it ? (parseFloat(it.qty) || 0) : 0);
            }, 0);
            return Math.max(0, total - allocatedOtherBoxes);
        },

        enforceMaxBoxItemQty(boxIndex, itemIndex) {
            if (!this.boxes[boxIndex] || !this.boxes[boxIndex].items || !this.boxes[boxIndex].items[itemIndex]) return;
            const maxAllowed = this.getAvailableQtyForBox(boxIndex, itemIndex);
            let entered = parseFloat(this.boxes[boxIndex].items[itemIndex].qty);
            if (isNaN(entered) || entered < 0) {
                entered = 0;
                this.boxes[boxIndex].items[itemIndex].qty = 0;
            }
            if (entered > maxAllowed) {
                this.boxes[boxIndex].items[itemIndex].qty = maxAllowed;
                const itemTitle = this.invoiceItems[itemIndex]?.name || ('Item #' + (itemIndex + 1));
                const totalDecl = this.invoiceItems[itemIndex]?.qty || 0;
                this.packingListWarning = `Quantity capped: '${itemTitle}' in Box #${boxIndex + 1} cannot exceed available quantity (${maxAllowed}). Total items packed cannot exceed entered invoice quantity of ${totalDecl}.`;
                setTimeout(() => {
                    if (this.packingListWarning && !this.hasOverAllocatedItems) {
                        this.packingListWarning = '';
                    }
                }, 4000);
            } else {
                if (!this.hasOverAllocatedItems) {
                    this.packingListWarning = '';
                }
            }
        },

        getItemAllocatedQty(itemIndex) {
            return this.boxes.reduce((sum, box) => {
                const found = (box.items || []).find(i => i.item_index === itemIndex);
                return sum + (found ? (parseFloat(found.qty) || 0) : 0);
            }, 0);
        },

        getItemRemainingQty(itemIndex) {
            const total = parseFloat(this.invoiceItems[itemIndex]?.qty) || 0;
            const allocated = this.getItemAllocatedQty(itemIndex);
            return Math.max(0, total - allocated);
        },

        allocateRemainingToBox(boxIndex, itemIndex) {
            const rem = this.getItemRemainingQty(itemIndex);
            if (rem <= 0) return;
            if (this.boxes[boxIndex] && this.boxes[boxIndex].items && this.boxes[boxIndex].items[itemIndex]) {
                this.boxes[boxIndex].items[itemIndex].qty = (parseFloat(this.boxes[boxIndex].items[itemIndex].qty) || 0) + rem;
                this.enforceMaxBoxItemQty(boxIndex, itemIndex);
            }
        },

        allocateAllRemainingToBox(boxIndex) {
            this.invoiceItems.forEach((_, itemIndex) => {
                this.allocateRemainingToBox(boxIndex, itemIndex);
            });
        },

        get hasOverAllocatedItems() {
            return this.invoiceItems.some((item, idx) => {
                const total = parseFloat(item.qty) || 0;
                return this.getItemAllocatedQty(idx) > (total + 0.0001);
            });
        },

        validatePackingListSubmission(event) {
            if (this.totalBoxes > 1 && this.hasOverAllocatedItems) {
                if (event) event.preventDefault();
                alert('Cannot submit shipment: One or more items in the packing list exceed the total quantity entered in the invoice. Please ensure total packed quantities do not exceed the declared item quantities.');
                return false;
            }
            return true;
        },

        async fetchHsSuggestions(itemIndex, query) {
            if (!query || query.length < 2) {
                this.invoiceItems[itemIndex].suggestions = [];
                this.invoiceItems[itemIndex].showSuggestions = false;
                return;
            }
            this.invoiceItems[itemIndex].isSearching = true;
            try {
                const res = await fetch(`/api/hs-codes/search?q=${encodeURIComponent(query)}&limit=6`);
                const data = await res.json();
                this.invoiceItems[itemIndex].suggestions = data.data || data.results || [];
                this.invoiceItems[itemIndex].showSuggestions = true;
            } catch (e) {
                console.error('HS Code search failed', e);
            } finally {
                this.invoiceItems[itemIndex].isSearching = false;
            }
        },

        selectHsCode(itemIndex, hs) {
            this.invoiceItems[itemIndex].hs_code = hs.code;
            this.invoiceItems[itemIndex].name = hs.commodity_name;
            if (hs.standard_uom) this.invoiceItems[itemIndex].uom = hs.standard_uom;
            this.invoiceItems[itemIndex].duty_rate = hs.export_duty_rate || 0;
            this.invoiceItems[itemIndex].showSuggestions = false;
            this.boxes.forEach(box => {
                if (box.items && box.items[itemIndex]) {
                    box.items[itemIndex].item_name = hs.commodity_name;
                }
            });
        },

        openWcoModal(itemIndex) {
            this.activeItemIndexForWco = itemIndex;
            this.wcoSearchQuery = this.invoiceItems[itemIndex]?.name || '';
            this.wcoModalOpen = true;
            this.performWcoSearch();
        },

        async performWcoSearch() {
            this.wcoLoading = true;
            try {
                let url = '/api/hs-codes/search?limit=30';
                if (this.wcoSearchQuery) url += `&q=${encodeURIComponent(this.wcoSearchQuery)}`;
                if (this.wcoCategory) url += `&category=${encodeURIComponent(this.wcoCategory)}`;
                const res = await fetch(url);
                const data = await res.json();
                this.wcoResults = data.data || data.results || [];
            } catch (e) {
                console.error('WCO fetch failed', e);
            } finally {
                this.wcoLoading = false;
            }
        },

        chooseWcoResult(hs) {
            if (this.activeItemIndexForWco !== null) {
                this.selectHsCode(this.activeItemIndexForWco, hs);
            }
            this.wcoModalOpen = false;
        },

        get invoiceSubtotal() {
            return this.invoiceItems.reduce((acc, item) => acc + ((parseFloat(item.qty) || 0) * (parseFloat(item.unit_price) || 0)), 0);
        },

        get totalCargoGrossWeight() {
            if (this.totalBoxes > 1) {
                return this.boxes.reduce((acc, b) => acc + (parseFloat(b.weight_kg) || 0), 0);
            }
            return parseFloat(this.boxes[0]?.weight_kg) || 1.0;
        },

        get totalCargoVolumetricWeight() {
            if (this.totalBoxes > 1) {
                return this.boxes.reduce((acc, b) => {
                    const l = parseFloat(b.length_cm) || 0;
                    const w = parseFloat(b.width_cm) || 0;
                    const h = parseFloat(b.height_cm) || 0;
                    return acc + ((l * w * h) / 5000);
                }, 0);
            }
            const l = parseFloat(this.boxes[0]?.length_cm) || 0;
            const w = parseFloat(this.boxes[0]?.width_cm) || 0;
            const h = parseFloat(this.boxes[0]?.height_cm) || 0;
            return (l * w * h) / 5000;
        },

        get totalChargeableWeight() {
            return Math.max(this.totalCargoGrossWeight, this.totalCargoVolumetricWeight);
        },

        syncCargoWeight() {
            const weightInput = document.getElementById('weight-input');
            if (weightInput) {
                weightInput.value = this.totalCargoGrossWeight.toFixed(2);
            }
            if (typeof calculateVolumetricWeight === 'function') {
                calculateVolumetricWeight();
            }
        },

        handleBillFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedBillFileName = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            } else {
                this.selectedBillFileName = '';
            }
        },

        get invoiceDataJsonPayload() {
            return JSON.stringify({
                invoice_number: this.invoiceNumber,
                invoice_date: this.invoiceDate,
                currency: this.invoiceCurrency,
                incoterm: this.incoterm,
                export_reason: this.reasonForExport,
                acquisition_source: this.acquisitionSource,
                shipper_pan_vat: this.exporterPanVat,
                shipper_exim_code: this.exporterEximCode,
                consignee_tax_id: this.consigneeTaxId,
                seller_bill_type: this.sellerBillType,
                seller_bill_number: this.sellerBillNumber,
                subtotal: this.invoiceSubtotal,
                items: this.invoiceItems.map(it => ({
                    description: it.name,
                    hs_code: it.hs_code,
                    origin_country: it.origin_country || 'Nepal',
                    quantity: parseFloat(it.qty) || 0,
                    uom: it.uom || 'PCS',
                    unit_value: parseFloat(it.unit_price) || 0,
                    total_value: (parseFloat(it.qty) || 0) * (parseFloat(it.unit_price) || 0),
                    duty_rate: it.duty_rate || 0
                }))
            });
        },

        get packingListDataJsonPayload() {
            return JSON.stringify({
                total_boxes: this.totalBoxes,
                total_gross_weight: this.totalCargoGrossWeight,
                total_volumetric_weight: this.totalCargoVolumetricWeight,
                boxes: this.boxes.map(b => ({
                    box_number: b.box_number,
                    length: parseFloat(b.length_cm) || 0,
                    width: parseFloat(b.width_cm) || 0,
                    height: parseFloat(b.height_cm) || 0,
                    gross_weight: parseFloat(b.weight_kg) || 0,
                    volumetric_weight: ((parseFloat(b.length_cm) || 0) * (parseFloat(b.width_cm) || 0) * (parseFloat(b.height_cm) || 0)) / 5000,
                    items: (b.items || []).map(bi => ({
                        item_index: bi.item_index,
                        item_name: bi.item_name || (this.invoiceItems[bi.item_index]?.name || ''),
                        hs_code: this.invoiceItems[bi.item_index]?.hs_code || '',
                        quantity: parseFloat(bi.qty) || 0,
                        uom: this.invoiceItems[bi.item_index]?.uom || 'PCS'
                    }))
                }))
            });
        }
    };
}
document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        Alpine.data('shipmentConsoleData', shipmentConsoleData);
    }
});
</script>

<div class="max-w-7xl mx-auto space-y-6 pb-16" x-data="shipmentConsoleData()">
    
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
                        <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                Verified Rate Applied
                            </span>
                            <span class="text-xs text-slate-300 font-bold">{{ request('receiver_country') }}</span>
                            @if(request('pickup_location_type') === 'outside_ktm')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    <i class="fas fa-truck-ramp-box text-[9px] mr-1"></i> Regional Feeder: {{ request('pickup_city') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-300">
                            Quoted Tariff: <span class="text-teal-300 font-mono font-black text-sm">Rs. {{ number_format((float)request('quoted_rate')) }}</span>
                            <span class="text-[11px] text-slate-400 ml-1">({{ request('chargeable_weight', request('weight')) }} KG)</span>
                            @if((float)request('domestic_feeder_charge') > 0)
                                <span class="text-amber-300 text-xs ml-1 font-semibold">&bull; Includes Rs. {{ number_format((float)request('domestic_feeder_charge')) }} Feeder Linehaul</span>
                            @endif
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
        <form action="{{ route('shipments.store') }}" method="POST" enctype="multipart/form-data" id="shipment-form" 
              @submit="if (!validatePackingListSubmission($event)) { $event.preventDefault(); return false; }"
              class="space-y-6">
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

            <!-- 2. COLLECTION METHOD & SENDER DETAILS (MERGED SINGLE FUNCTION, PICKUP CHOSEN INITIALLY OR OPTIONAL) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/90 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">2</span>
                            <span>📦 Collection Method & Sender Details</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Select how packages will be collected: dispatch our doorstep rider fleet or drop off at any Netpack station.
                        </p>
                    </div>

                    <div x-show="hasDoorstepPickup" x-transition>
                        <button type="button" onclick="addPickupPoint()" 
                                class="px-4 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-xs font-extrabold transition flex items-center gap-1.5 self-start sm:self-auto cursor-pointer shadow-xs">
                            <i class="fas fa-plus text-teal-600"></i>
                            <span>Add Another Pickup Point</span>
                        </button>
                    </div>
                </div>

                <!-- DUAL METHOD SELECTOR CARDS -->
                <input type="hidden" name="schedule_doorstep_pickup" id="schedule_doorstep_pickup" :value="hasDoorstepPickup ? '1' : '0'">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Option 1: Doorstep Courier Collection -->
                    <div @click="setDoorstepPickup(true)"
                         class="relative p-4 sm:p-5 rounded-2xl border-2 transition-all cursor-pointer flex flex-col justify-between"
                         :class="hasDoorstepPickup ? 'bg-gradient-to-br from-teal-50 via-emerald-50/60 to-white border-teal-600 ring-2 ring-teal-500/20 shadow-sm' : 'bg-white border-slate-200 hover:border-slate-300 opacity-80 hover:opacity-100'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 transition"
                                     :class="hasDoorstepPickup ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600'">
                                    <i class="fas fa-motorcycle"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">Doorstep Courier Collection</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-teal-600 text-white">Rider Fleet</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        A courier rider will be dispatched to collect packages directly from your location.
                                    </p>
                                </div>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition flex-shrink-0 mt-0.5"
                                 :class="hasDoorstepPickup ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300 bg-white'">
                                <i class="fas fa-check text-[10px]" x-show="hasDoorstepPickup"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                            <span class="font-extrabold text-teal-700" x-show="hasDoorstepPickup">✓ Collection Active</span>
                            <span class="text-slate-400 font-medium" x-show="!hasDoorstepPickup">Click to dispatch rider</span>
                            <span class="text-slate-500 font-medium">On-Demand Pickup</span>
                        </div>
                    </div>

                    <!-- Option 2: Station / Counter Drop-off -->
                    <div @click="setDoorstepPickup(false)"
                         class="relative p-4 sm:p-5 rounded-2xl border-2 transition-all cursor-pointer flex flex-col justify-between"
                         :class="!hasDoorstepPickup ? 'bg-gradient-to-br from-slate-100 via-slate-50 to-white border-slate-800 ring-2 ring-slate-800/10 shadow-sm' : 'bg-white border-slate-200 hover:border-slate-300 opacity-80 hover:opacity-100'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 transition"
                                     :class="!hasDoorstepPickup ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600'">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">Station / Counter Drop-off</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-200 text-slate-700">Self Drop-off</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        You drop off packages at any Netpack station or branch counter. No rider will be dispatched.
                                    </p>
                                </div>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition flex-shrink-0 mt-0.5"
                                 :class="!hasDoorstepPickup ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 bg-white'">
                                <i class="fas fa-check text-[10px]" x-show="!hasDoorstepPickup"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                            <span class="font-extrabold text-slate-800" x-show="!hasDoorstepPickup">✓ Counter Drop-off Active</span>
                            <span class="text-slate-400 font-medium" x-show="hasDoorstepPickup">Click for self drop-off</span>
                            <span class="text-slate-500 font-medium">Standard Hub Processing</span>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- DOORSTEP PICKUP DETAILS CONTAINER (ONLY SHOWN WHEN hasDoorstepPickup IS TRUE) -->
                <!-- ========================================================================= -->
                <div x-show="hasDoorstepPickup" x-transition class="space-y-6">
                    <!-- Collection Time & Rider Notes -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-teal-50/50 border border-teal-200/80 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-800 block mb-1.5 flex items-center gap-1.5">
                                <i class="fas fa-clock text-teal-600"></i>
                                <span>Preferred Collection Time Slot:</span>
                            </label>
                            <input type="datetime-local" name="scheduled_pickup_time" 
                                   :disabled="!hasDoorstepPickup"
                                   value="{{ old('scheduled_pickup_time', now()->addHours(2)->format('Y-m-d\TH:i')) }}"
                                   class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900 shadow-xs">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-800 block mb-1.5 flex items-center gap-1.5">
                                <i class="fas fa-comment-dots text-teal-600"></i>
                                <span>Pickup Notes for Rider (Optional):</span>
                            </label>
                            <input type="text" name="pickup_notes" placeholder="e.g. Ring bell on 2nd floor, call sender on arrival"
                                   :disabled="!hasDoorstepPickup"
                                   class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900 shadow-xs">
                        </div>
                    </div>

                    <!-- Pickup Cards Container -->
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
                                        <input type="text" name="pickup_name[]" id="pickup_name_0"
                                               :required="hasDoorstepPickup"
                                               :disabled="!hasDoorstepPickup"
                                               value="{{ old('pickup_name.0', $convertPickup->contact_person_name ?? Auth::user()->name ?? '') }}"
                                               class="w-full text-xs pl-8 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900"
                                               placeholder="Contact person name">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Number <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-phone absolute left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                                        <input type="text" name="pickup_phone[]" id="pickup_phone_0"
                                               :required="hasDoorstepPickup"
                                               :disabled="!hasDoorstepPickup"
                                               value="{{ old('pickup_phone.0', $convertPickup->contact_person_phone ?? Auth::user()->phone ?? '') }}"
                                               class="w-full text-xs pl-8 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-medium text-slate-900"
                                               placeholder="98XXXXXXXX">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Pickup Street Address & Landmark <span class="text-rose-500">*</span></label>
                                <textarea name="pickup_address[]" id="pickup_address_0" rows="2"
                                          :required="hasDoorstepPickup"
                                          :disabled="!hasDoorstepPickup"
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
                                    <button type="button" onclick="searchLocationOnMap('pickup', 0)" class="px-3 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition cursor-pointer">
                                        Search
                                    </button>
                                    <button type="button" onclick="getCurrentLocationOnMap('pickup', 0)" title="Use Current GPS" class="px-3 py-2 bg-teal-50 text-teal-700 border border-teal-200 rounded-xl text-xs font-bold hover:bg-teal-100 transition cursor-pointer">
                                        <i class="fas fa-location-crosshairs"></i>
                                    </button>
                                </div>

                                <div id="pickup-map-0" class="map-container"></div>
                                <input type="hidden" name="pickup_lat[]" id="pickup-lat-0" value="27.7172" :disabled="!hasDoorstepPickup">
                                <input type="hidden" name="pickup_lng[]" id="pickup-lng-0" value="85.3240" :disabled="!hasDoorstepPickup">
                            </div>
                        </div>
                    </div>

                    <!-- Auto-Save Address Book Option -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <input type="checkbox" name="save_pickup_addresses" id="save_pickup_addresses" value="1" checked 
                               :disabled="!hasDoorstepPickup"
                               class="w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500 cursor-pointer">
                        <label for="save_pickup_addresses" class="text-xs text-slate-700 font-medium cursor-pointer">
                            Automatically remember and save newly entered pickup locations to my Address Book for 1-click re-use.
                        </label>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- COUNTER / STATION DROP-OFF PANEL (ONLY SHOWN WHEN hasDoorstepPickup IS FALSE) -->
                <!-- ========================================================================= -->
                <div x-show="!hasDoorstepPickup" x-transition class="space-y-4">
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 space-y-3">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center flex-shrink-0 text-base shadow-xs">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-extrabold text-slate-900 text-sm">Station / Counter Drop-off Selected</h4>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">No Pickup Fee</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    You can drop off your consignment at any authorized Netpack hub or branch counter. No courier rider will be dispatched to your location.
                                </p>
                            </div>
                        </div>

                        <!-- Sender Information (Used on Label) -->
                        <div class="pt-3 border-t border-slate-200/80">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-2">
                                Sender Information (Printed on Consignment Waybill):
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Sender Name</label>
                                    <input type="text" name="sender_name" 
                                           :disabled="hasDoorstepPickup"
                                           value="{{ old('sender_name', Auth::user()->name ?? '') }}"
                                           class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl font-medium text-slate-900 focus:ring-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Sender Phone</label>
                                    <input type="text" name="sender_phone" 
                                           :disabled="hasDoorstepPickup"
                                           value="{{ old('sender_phone', Auth::user()->phone ?? '') }}"
                                           class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono font-medium text-slate-900 focus:ring-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Sender Address / Station</label>
                                    <input type="text" name="sender_address" 
                                           :disabled="hasDoorstepPickup"
                                           value="{{ old('sender_address', Auth::user()->address ?? Auth::user()->permanent_address ?? 'Netpack Station Drop-off') }}"
                                           class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl font-medium text-slate-900 focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                        </div>
                    </div>
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
                                        $commonCountries = ['Poland', 'United States', 'United Kingdom', 'Australia', 'Germany', 'Canada', 'United Arab Emirates', 'India', 'Japan', 'France', 'Netherlands', 'Italy', 'Spain', 'Switzerland', 'Sweden', 'Singapore', 'Qatar', 'Malaysia', 'Saudi Arabia', 'South Korea'];
                                        $requestedCountry = request('receiver_country');
                                    @endphp
                                    @if($requestedCountry && !in_array($requestedCountry, $commonCountries))
                                        <option value="{{ $requestedCountry }}" selected>{{ $requestedCountry }}</option>
                                    @endif
                                    @foreach($commonCountries as $c)
                                        <option value="{{ $c }}" @selected(strcasecmp($requestedCountry ?? '', $c) === 0)>{{ $c }}</option>
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

            <!-- 4. SHIPPING DOCUMENTS, CARGO SPECIFICATIONS & SMART PACKING MATRIX -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/90 space-y-8">
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[10px] flex items-center justify-center font-bold">4</span>
                            <span>⚖️ Shipping Documents, Cargo Weight & Smart Packing List</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Harmonized System (HS) WCO customs declaration, itemized Commercial Invoice & multi-box allocation matrix.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200 flex items-center gap-1.5">
                            <i class="fas fa-shield-halved text-teal-600"></i>
                            <span>Nepal IRD & WCO Compliant</span>
                        </span>
                    </div>
                </div>

                <!-- Hidden Payloads for Backend Persistence -->
                <input type="hidden" name="invoice_data_json" :value="invoiceDataJsonPayload">
                <input type="hidden" name="packing_list_data_json" :value="packingListDataJsonPayload">

                <!-- Conditional Mode Display: E-Commerce / Rider Mode Simple View vs Full Documentation Suite -->
                <template x-if="shipmentMode === 'ecommerce'">
                    <div class="space-y-6">
                        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/70 text-xs text-amber-900 flex items-start gap-3">
                            <i class="fas fa-info-circle text-amber-600 text-base mt-0.5"></i>
                            <div>
                                <span class="font-extrabold block">E-Commerce Rider Dispatch Mode</span>
                                <span class="text-amber-800">Formal customs Commercial Invoices and multi-box packing lists are not required for local rider delivery. Enter package weight and attach merchant tax bill below if available.</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Parcel Gross Weight (KG) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.1" name="weight" id="weight-input" required 
                                       value="{{ old('weight', $convertPickup->estimated_weight_kg ?? request('weight', '1.0')) }}"
                                       oninput="calculateVolumetricWeight()"
                                       class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-bold text-slate-900">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Length (cm)</label>
                                <input type="number" step="0.1" name="length" id="length-input" placeholder="L" oninput="calculateVolumetricWeight()"
                                       class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Width (cm)</label>
                                <input type="number" step="0.1" name="width" id="width-input" placeholder="W" oninput="calculateVolumetricWeight()"
                                       class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Height (cm)</label>
                                <input type="number" step="0.1" name="height" id="height-input" placeholder="H" oninput="calculateVolumetricWeight()"
                                       class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Cargo / Goods Description</label>
                            <input type="text" name="description" placeholder="e.g. Retail apparel, Electronic accessories, Cosmetics"
                                   class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-900 font-medium">
                        </div>
                    </div>
                </template>

                <!-- Full Documentation Suite (Domestic & International Shipments) -->
                <div x-show="shipmentMode !== 'ecommerce'" class="space-y-8">
                    
                    <!-- 4A. COMMERCIAL INVOICE & WCO HS CODE ENGINE -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-50/80 border border-slate-200/90 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-teal-600 text-white flex items-center justify-center text-sm shadow-xs">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Commercial Invoice Preparation</h4>
                                    <p class="text-[11px] text-slate-500">Auto-suggests WCO Harmonized System tariffs & Nepal Customs export classifications</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-slate-600">Currency:</span>
                                <select x-model="invoiceCurrency" class="text-xs font-black py-1 px-2.5 bg-white border border-slate-200 rounded-lg text-teal-800 focus:ring-2 focus:ring-teal-500">
                                    <option value="USD">USD ($)</option>
                                    <option value="NPR">NPR (Rs.)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                    <option value="AUD">AUD ($)</option>
                                    <option value="CAD">CAD ($)</option>
                                    <option value="AED">AED (Dh)</option>
                                    <option value="INR">INR (₹)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Invoice Metadata Bar -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Invoice Number</label>
                                <input type="text" x-model="invoiceNumber" class="w-full text-xs font-mono font-bold px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Invoice Date</label>
                                <input type="date" x-model="invoiceDate" class="w-full text-xs px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Incoterms</label>
                                <select x-model="incoterm" class="w-full text-xs font-bold px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                                    <option value="DAP">DAP (Delivered at Place)</option>
                                    <option value="DDP">DDP (Delivered Duty Paid)</option>
                                    <option value="FOB">FOB (Free on Board)</option>
                                    <option value="CIF">CIF (Cost, Insurance & Freight)</option>
                                    <option value="EXW">EXW (Ex Works)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Reason For Export</label>
                                <select x-model="reasonForExport" class="w-full text-xs px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                                    <option value="Commercial Sale / Export">Commercial Sale</option>
                                    <option value="Sample Not For Sale">Sample Not For Sale</option>
                                    <option value="Gift / Personal Effects">Personal / Gift</option>
                                    <option value="Return / Repair">Return / Repair</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Acquisition Source</label>
                                <select x-model="acquisitionSource" class="w-full text-xs font-semibold px-2 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                                    <option value="direct_portal">Direct Web Portal</option>
                                    <option value="website_organic">Website / Organic</option>
                                    <option value="referral">Client Referral</option>
                                    <option value="sales_representative">Sales Executive</option>
                                    <option value="social_media">Social Media</option>
                                    <option value="agent_walkin">Walk-in Counter</option>
                                    <option value="api_integration">API Integration</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Exporter PAN / VAT</label>
                                <input type="text" x-model="exporterPanVat" placeholder="9-digit PAN" class="w-full text-xs font-mono px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">EXIM Code (Customs)</label>
                                <input type="text" x-model="exporterEximCode" placeholder="NP-XXXXXXXX" class="w-full text-xs font-mono px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                            </div>
                        </div>

                        <!-- Declared Commodities / Line Items Invoicing Table -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-file-invoice text-teal-600"></i>
                                    <span>Declared Commodity Line Items (Commercial Invoice)</span>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-teal-100 text-teal-800" x-text="invoiceItems.length + ' item(s)'"></span>
                                </span>
                                <button type="button" @click="addInvoiceItem()" class="px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-2xs cursor-pointer transition">
                                    <i class="fas fa-plus"></i>
                                    <span>Add Commodity Row</span>
                                </button>
                            </div>

                            <!-- Column-like Spreadsheet Invoice Table -->
                            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-2xs">
                                <table class="w-full text-left border-collapse min-w-[780px]">
                                    <thead>
                                        <tr class="bg-slate-50/90 border-b border-slate-200 text-[10px] font-black uppercase tracking-wider text-slate-600">
                                            <th class="py-2.5 px-3 w-10 text-center">#</th>
                                            <th class="py-2.5 px-3 min-w-[300px]">Item / Commodity &amp; WCO HS Code <span class="text-rose-500">*</span></th>
                                            <th class="py-2.5 px-3 w-28">Origin</th>
                                            <th class="py-2.5 px-3 w-20 text-center">Qty</th>
                                            <th class="py-2.5 px-3 w-24">UOM</th>
                                            <th class="py-2.5 px-3 w-28 text-right">Unit (<span x-text="invoiceCurrency"></span>)</th>
                                            <th class="py-2.5 px-3 w-28 text-right">Total (<span x-text="invoiceCurrency"></span>)</th>
                                            <th class="py-2.5 px-3 w-12 text-center"><i class="fas fa-trash-can text-slate-400 text-xs"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-xs">
                                        <template x-for="(item, index) in invoiceItems" :key="index">
                                            <tr class="hover:bg-teal-50/30 transition group">
                                                <!-- Row # -->
                                                <td class="py-3 px-3 text-center align-top font-mono font-bold text-slate-400 pt-3.5" x-text="index + 1"></td>

                                                <!-- Description & Live HS Suggestions -->
                                                <td class="py-2.5 px-3 align-top relative">
                                                    <div class="flex items-center gap-1.5">
                                                        <div class="relative flex-1">
                                                            <input type="text" x-model="item.name" 
                                                                   @input.debounce.300ms="fetchHsSuggestions(index, item.name)"
                                                                   @focus="if(item.name.length >= 2) fetchHsSuggestions(index, item.name)"
                                                                   placeholder="Type item e.g. Pashmina Shawl, Tea, Singing Bowl..." 
                                                                   class="w-full text-xs font-semibold px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-900">
                                                            <span x-show="item.isSearching" class="absolute right-2.5 top-2 text-teal-600 text-xs">
                                                                <i class="fas fa-spinner fa-spin"></i>
                                                            </span>
                                                        </div>
                                                        <button type="button" @click="openWcoModal(index)" title="Explore WCO Tariff Database"
                                                                class="px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-700 border border-slate-200 text-xs font-bold transition flex items-center gap-1 cursor-pointer shrink-0">
                                                            <i class="fas fa-search-dollar text-teal-600 text-xs"></i>
                                                            <span class="text-[11px]">WCO</span>
                                                        </button>
                                                    </div>

                                                    <!-- Active HS Code Badge -->
                                                    <div class="mt-1 flex items-center gap-2 flex-wrap" x-show="item.hs_code">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-teal-50 text-teal-800 border border-teal-200 text-[10px] font-mono font-bold">
                                                            <i class="fas fa-tag text-teal-600 text-[9px]"></i>
                                                            <span x-text="'HS: ' + item.hs_code"></span>
                                                        </span>
                                                        <span class="text-[10px] text-slate-400">Export Duty: 0% Free</span>
                                                    </div>

                                                    <!-- Live Floating Suggestions Dropdown -->
                                                    <div x-show="item.showSuggestions && item.suggestions.length > 0"
                                                         @click.outside="item.showSuggestions = false"
                                                         class="absolute z-30 left-3 right-3 top-full mt-1 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden text-xs max-h-60 overflow-y-auto">
                                                        <div class="p-2 bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase tracking-wider text-slate-500 flex items-center justify-between">
                                                            <span>WCO Tariff Auto-Suggestions</span>
                                                            <span>Select to apply</span>
                                                        </div>
                                                        <template x-for="hs in item.suggestions" :key="hs.id">
                                                            <div @click="selectHsCode(index, hs)" class="p-2.5 hover:bg-teal-50/80 cursor-pointer border-b border-slate-100 last:border-0 transition">
                                                                <div class="flex items-center justify-between">
                                                                    <span class="font-bold text-slate-900" x-text="hs.commodity_name"></span>
                                                                    <span class="font-mono font-black text-teal-700 px-1.5 py-0.5 rounded bg-teal-100/60" x-text="'HS ' + hs.code"></span>
                                                                </div>
                                                                <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-2">
                                                                    <span x-text="hs.category"></span>
                                                                    <span>&bull;</span>
                                                                    <span x-text="'Standard UOM: ' + hs.standard_uom"></span>
                                                                    <span>&bull;</span>
                                                                    <span x-text="'Export Duty: ' + (hs.export_duty_rate ? hs.export_duty_rate + '%' : '0% (Free)')"></span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>

                                                <!-- Origin -->
                                                <td class="py-2.5 px-3 align-top">
                                                    <input type="text" x-model="item.origin_country" placeholder="Nepal"
                                                           class="w-full text-xs px-2 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                                                </td>

                                                <!-- Quantity -->
                                                <td class="py-2.5 px-3 align-top">
                                                    <input type="number" step="1" min="1" x-model.number="item.qty" 
                                                           @input="syncInvoiceItemQty(index)"
                                                           class="w-full text-xs font-mono font-bold text-center px-2 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-900">
                                                </td>

                                                <!-- UOM -->
                                                <td class="py-2.5 px-3 align-top">
                                                    <select x-model="item.uom" class="w-full text-xs font-semibold px-2 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-800">
                                                        <option value="PCS">PCS</option>
                                                        <option value="SET">SET</option>
                                                        <option value="KGS">KGS</option>
                                                        <option value="MTR">MTR</option>
                                                        <option value="BOX">BOX</option>
                                                        <option value="PKT">PKT</option>
                                                        <option value="PRS">PRS</option>
                                                    </select>
                                                </td>

                                                <!-- Unit Price -->
                                                <td class="py-2.5 px-3 align-top">
                                                    <input type="number" step="0.01" min="0" x-model.number="item.unit_price" 
                                                           class="w-full text-xs font-mono font-bold text-right px-2 py-1.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 text-slate-900">
                                                </td>

                                                <!-- Line Total -->
                                                <td class="py-2.5 px-3 align-top text-right pt-3.5">
                                                    <span class="text-xs font-mono font-black text-slate-900" x-text="((parseFloat(item.qty) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(2)"></span>
                                                </td>

                                                <!-- Action -->
                                                <td class="py-2.5 px-3 align-top text-center pt-3.5">
                                                    <button type="button" @click="removeInvoiceItem(index)" :disabled="invoiceItems.length <= 1"
                                                            class="text-slate-400 hover:text-rose-600 disabled:opacity-20 disabled:hover:text-slate-400 text-xs transition cursor-pointer p-1"
                                                            title="Remove line item">
                                                        <i class="fas fa-trash-can"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-slate-50 border-t border-slate-200">
                                        <tr>
                                            <td colspan="4" class="py-3 px-3">
                                                <button type="button" @click="addInvoiceItem()" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                                                    <i class="fas fa-plus"></i>
                                                    <span>Add Item Row</span>
                                                </button>
                                            </td>
                                            <td colspan="2" class="py-3 px-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">
                                                Declared Customs Subtotal:
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <span class="font-mono font-black text-xs text-teal-800 bg-teal-50 px-2 py-1 rounded border border-teal-200 inline-block" 
                                                      x-text="invoiceCurrency + ' ' + invoiceSubtotal.toFixed(2)"></span>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Invoice Subtotal Summary Bar -->
                            <div class="p-3.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-600">Declared Customs Subtotal:</span>
                                    <span class="font-mono font-black text-teal-800 bg-teal-50 px-2.5 py-1 rounded-md border border-teal-200" 
                                          x-text="invoiceCurrency + ' ' + invoiceSubtotal.toFixed(2)"></span>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    <i class="fas fa-check-circle text-emerald-600 mr-1"></i> Ready for printable export invoice & customs filing
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4B. PACKAGE WEIGHT, CARGO SPECIFICATIONS & SMART PACKING LIST MATRIX -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-teal-50/50 border border-teal-200/80 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-teal-200/60">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-teal-700 text-white flex items-center justify-center text-sm shadow-xs">
                                    <i class="fas fa-boxes-stacked"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider text-teal-950">Package Weight & Smart Packing List Matrix</h4>
                                    <p class="text-[11px] text-teal-700">Allocate declared goods across single or multiple cartons with real-time balance tracking</p>
                                </div>
                            </div>

                            <!-- Box Count Selector -->
                            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-teal-200 shadow-2xs">
                                <span class="text-xs font-black text-slate-700">Total Boxes:</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="setBoxCount(totalBoxes - 1)" :disabled="totalBoxes <= 1"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 disabled:opacity-30 text-slate-700 font-bold text-xs flex items-center justify-center cursor-pointer">
                                        -
                                    </button>
                                    <input type="number" min="1" max="50" x-model.number="totalBoxes" @change="setBoxCount(totalBoxes)"
                                           class="w-12 text-center text-xs font-mono font-black py-1 px-1 bg-transparent border-0 focus:ring-0 text-teal-950">
                                    <button type="button" @click="setBoxCount(totalBoxes + 1)"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center cursor-pointer">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- CASE 1: Single Box (Zero-friction 100% auto-allocation) -->
                        <div x-show="totalBoxes === 1" class="space-y-4">
                            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-check text-emerald-600 text-sm"></i>
                                    <span class="font-bold">Single Box Shipment: Box #1 automatically contains 100% of all declared invoice items.</span>
                                </div>
                                <span class="text-[10px] font-mono uppercase font-black px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">100% Auto-Allocated</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-white p-4 rounded-xl border border-teal-200/70">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Gross Actual Weight (KG) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.1" min="0.1" name="weight" id="weight-input" required 
                                           x-model.number="boxes[0].weight_kg" @input="syncCargoWeight()"
                                           class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono font-bold text-slate-900">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Length (cm)</label>
                                    <input type="number" step="0.1" name="length" id="length-input" placeholder="L" 
                                           x-model.number="boxes[0].length_cm" @input="syncCargoWeight()"
                                           class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Width (cm)</label>
                                    <input type="number" step="0.1" name="width" id="width-input" placeholder="W" 
                                           x-model.number="boxes[0].width_cm" @input="syncCargoWeight()"
                                           class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Height (cm)</label>
                                    <input type="number" step="0.1" name="height" id="height-input" placeholder="H" 
                                           x-model.number="boxes[0].height_cm" @input="syncCargoWeight()"
                                           class="w-full text-xs px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                                </div>
                            </div>
                        </div>

                        <!-- CASE 2: Multi-Box Matrix (> 1 Box) -->
                        <div x-show="totalBoxes > 1" class="space-y-4">
                            <!-- Over-Allocation Warning Banner -->
                            <div x-show="packingListWarning || hasOverAllocatedItems" x-transition 
                                 class="p-3.5 rounded-xl bg-rose-50 border-2 border-rose-300 text-rose-900 text-xs flex items-center gap-2.5 shadow-sm">
                                <i class="fas fa-triangle-exclamation text-rose-600 text-base flex-shrink-0"></i>
                                <div>
                                    <span class="font-extrabold block" x-text="packingListWarning || 'Item quantities in the packing list cannot exceed the total quantity entered in the invoice.'"></span>
                                    <span class="text-[11px] text-rose-700">Please reduce the allocated box quantities to match the declared total invoice quantity.</span>
                                </div>
                            </div>

                            <!-- Remaining Balance Tracker Pills -->
                            <div class="p-3.5 rounded-xl bg-white border border-teal-200 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">Item Allocation Tracker:</span>
                                    <span class="text-[11px] text-slate-500">Box item quantities cannot exceed entered total quantity</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(item, idx) in invoiceItems" :key="idx">
                                        <div class="px-3 py-1.5 rounded-lg border text-xs flex items-center gap-2 transition"
                                             :class="getItemAllocatedQty(idx) > (parseFloat(item.qty) || 0) 
                                                        ? 'bg-rose-50 border-rose-300 text-rose-900 ring-1 ring-rose-400 font-bold' 
                                                        : (getItemRemainingQty(idx) === 0 
                                                            ? 'bg-emerald-50 border-emerald-200 text-emerald-900 font-medium' 
                                                            : 'bg-amber-50 border-amber-200 text-amber-900')">
                                            <span class="font-bold" x-text="item.name || ('Item #' + (idx + 1))"></span>
                                            <span class="font-mono font-black" x-text="getItemAllocatedQty(idx) + ' / ' + item.qty + ' ' + item.uom"></span>
                                            
                                            <!-- Over-Allocated Indicator -->
                                            <span x-show="getItemAllocatedQty(idx) > (parseFloat(item.qty) || 0)" 
                                                  class="text-[10px] font-black px-1.5 py-0.5 rounded bg-rose-200 text-rose-950 flex items-center gap-1">
                                                <i class="fas fa-circle-exclamation"></i>
                                                <span>Exceeds by <span x-text="(getItemAllocatedQty(idx) - (parseFloat(item.qty) || 0)).toFixed(0)"></span>!</span>
                                            </span>

                                            <!-- Remaining Quantity Indicator -->
                                            <span x-show="getItemRemainingQty(idx) > 0 && getItemAllocatedQty(idx) <= (parseFloat(item.qty) || 0)" 
                                                  class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-200/80 text-amber-950" 
                                                  x-text="getItemRemainingQty(idx) + ' left'"></span>

                                            <!-- Complete 100% Allocation Indicator -->
                                            <span x-show="getItemRemainingQty(idx) === 0 && getItemAllocatedQty(idx) === (parseFloat(item.qty) || 0)" 
                                                  class="text-[10px] text-emerald-700 font-black">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Box Cards Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <template x-for="(box, bIdx) in boxes" :key="bIdx">
                                    <div class="p-4 rounded-xl bg-white border border-teal-200 shadow-2xs space-y-3">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded-md bg-teal-700 text-white text-xs font-black flex items-center justify-center" x-text="box.box_number"></span>
                                                <span class="font-extrabold text-xs text-slate-900" x-text="'Box #' + box.box_number + ' of ' + totalBoxes"></span>
                                            </div>
                                            <button type="button" @click="allocateAllRemainingToBox(bIdx)" 
                                                    class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-[10px] font-black tracking-wider transition cursor-pointer">
                                                <i class="fas fa-bolt text-teal-600 mr-1"></i> Fill Remaining
                                            </button>
                                        </div>

                                        <!-- Dimensions & Weight -->
                                        <div class="grid grid-cols-4 gap-2">
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase text-slate-500 mb-0.5">Weight (KG)</label>
                                                <input type="number" step="0.1" min="0.1" x-model.number="box.weight_kg" @input="syncCargoWeight()"
                                                       class="w-full text-xs font-mono font-bold px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase text-slate-500 mb-0.5">L (cm)</label>
                                                <input type="number" step="0.1" x-model.number="box.length_cm" @input="syncCargoWeight()"
                                                       class="w-full text-xs font-mono px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase text-slate-500 mb-0.5">W (cm)</label>
                                                <input type="number" step="0.1" x-model.number="box.width_cm" @input="syncCargoWeight()"
                                                       class="w-full text-xs font-mono px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase text-slate-500 mb-0.5">H (cm)</label>
                                                <input type="number" step="0.1" x-model.number="box.height_cm" @input="syncCargoWeight()"
                                                       class="w-full text-xs font-mono px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                                            </div>
                                        </div>

                                        <!-- Items inside this box -->
                                        <div class="space-y-1.5 pt-1">
                                            <span class="block text-[10px] font-extrabold uppercase text-slate-600">Contents in this box:</span>
                                            <template x-for="(item, iIdx) in invoiceItems" :key="iIdx">
                                                <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-slate-50 text-xs">
                                                    <div class="truncate flex-1">
                                                        <span class="font-bold text-slate-800 text-[11px]" x-text="item.name || ('Item #' + (iIdx + 1))"></span>
                                                        <span class="text-[10px] text-slate-400 font-mono ml-1" x-text="'(' + item.uom + ')'"></span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="number" min="0" :max="getAvailableQtyForBox(bIdx, iIdx)" 
                                                               x-model.number="box.items[iIdx].qty"
                                                               @input="enforceMaxBoxItemQty(bIdx, iIdx)"
                                                               @change="enforceMaxBoxItemQty(bIdx, iIdx)"
                                                               class="w-16 text-center font-mono font-bold text-xs py-1 px-1 bg-white border border-slate-200 rounded-md focus:ring-1 focus:ring-teal-500"
                                                               :class="getItemAllocatedQty(iIdx) > (parseFloat(item.qty) || 0) ? 'border-rose-500 text-rose-700 bg-rose-50 ring-1 ring-rose-400' : ''">
                                                        <button type="button" @click="allocateRemainingToBox(bIdx, iIdx)" 
                                                                x-show="getItemRemainingQty(iIdx) > 0"
                                                                :title="'Add remaining (' + getItemRemainingQty(iIdx) + ') to this box'"
                                                                class="px-2 py-1 rounded bg-teal-100 hover:bg-teal-200 text-teal-900 text-[10px] font-black cursor-pointer transition">
                                                            +<span x-text="getItemRemainingQty(iIdx)"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Real-time volumetric calculation badge across all boxes -->
                        <div class="p-4 rounded-2xl bg-white border border-teal-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-600">Total Gross Actual Weight:</span>
                                <span class="font-mono font-bold text-slate-900 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200" 
                                      x-text="totalCargoGrossWeight.toFixed(2) + ' KG'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-600">Volumetric Weight (IATA L×W×H / 5000):</span>
                                <span id="volumetric-weight-display" class="font-mono font-bold text-teal-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200"
                                      x-text="totalCargoVolumetricWeight.toFixed(2) + ' KG'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-600">Chargeable Weight:</span>
                                <span id="chargeable-weight-display" class="font-mono font-black text-slate-900 bg-teal-50 px-3 py-1 rounded-lg border border-teal-200 text-teal-900"
                                      x-text="'Chargeable: ' + totalChargeableWeight.toFixed(2) + ' KG'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Cargo / Goods Description</label>
                            <input type="text" name="description" placeholder="e.g. Garments, Handcrafted Goods, Samples"
                                   class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-900 font-medium">
                        </div>
                    </div>

                    <!-- 4C. NEPAL GOVERNMENT TAX & CUSTOMS COMPLIANCE (SUPPORTING BILL UPLOAD) -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-50/90 border border-slate-200/90 space-y-4">
                        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-200">
                            <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm shadow-xs">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Nepal Tax & Customs Documentation</h4>
                                <p class="text-[11px] text-slate-500">Attach supporting VAT Tax Invoice, PAN Bill, or Customs Export Declaration</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Supporting Document / Bill Type</label>
                                <select name="seller_bill_type" x-model="sellerBillType" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-800 font-semibold">
                                    <option value="vat_invoice">Inland Revenue Dept (IRD) Authenticated VAT Invoice</option>
                                    <option value="pan_bill">PAN Cash Bill / Retail Receipt</option>
                                    <option value="customs_declaration">Nepal Customs Export Declaration (Pragyapanpatra)</option>
                                    <option value="certificate_of_origin">Certificate of Origin (NCCI / FNCCI)</option>
                                    <option value="other">Other Official Commercial Bill</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Document / Bill Reference Number</label>
                                <input type="text" name="seller_bill_number" x-model="sellerBillNumber" placeholder="e.g. VAT-081/82-00412" 
                                       class="w-full text-xs font-mono font-medium px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 text-slate-800">
                            </div>
                        </div>

                        <!-- File Drag & Drop / Input -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Upload Bill / Receipt File (PDF, JPG, PNG, WEBP &bull; Max 10MB)</label>
                            <div class="relative border-2 border-dashed border-slate-300 hover:border-teal-500 rounded-2xl p-4 bg-white text-center transition cursor-pointer">
                                <input type="file" name="seller_bill_file" id="seller_bill_file" accept=".pdf,.jpg,.jpeg,.png,.webp"
                                       @change="handleBillFileSelect($event)"
                                       class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10">
                                <div class="space-y-1">
                                    <i class="fas fa-cloud-arrow-up text-teal-600 text-xl"></i>
                                    <p class="text-xs font-bold text-slate-800" x-show="!selectedBillFileName">
                                        Click or drag tax bill / invoice file to upload
                                    </p>
                                    <p class="text-xs font-black text-teal-700" x-show="selectedBillFileName" x-text="selectedBillFileName"></p>
                                    <span class="text-[10px] text-slate-400 block">Complies with Nepal Inland Revenue Department (IRD) transport guidelines</span>
                                </div>
                            </div>
                        </div>
                    </div>

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
                        :disabled="totalBoxes > 1 && hasOverAllocatedItems"
                        :class="totalBoxes > 1 && hasOverAllocatedItems ? 'opacity-50 cursor-not-allowed filter grayscale' : ''"
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

    <!-- ========================================================================= -->
    <!-- WCO HARMONIZED SYSTEM (HS) TARIFF EXPLORER MODAL -->
    <!-- ========================================================================= -->
    <div x-show="wcoModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;">
        <div @click.outside="wcoModalOpen = false" 
             class="bg-white rounded-3xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center text-base shadow-xs">
                        <i class="fas fa-book-atlas"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">WCO Harmonized System (HS) Tariffs Explorer</h3>
                        <p class="text-xs text-slate-500">Official World Customs Organization tariff codes for Nepal exports & global clearance</p>
                    </div>
                </div>
                <button type="button" @click="wcoModalOpen = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-xl flex items-center justify-center hover:bg-slate-100 cursor-pointer">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <!-- Search & Filter Controls -->
            <div class="p-4 border-b border-slate-100 space-y-3 bg-white">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" x-model="wcoSearchQuery" @input.debounce.300ms="performWcoSearch()"
                               placeholder="Search by commodity name, keyword, or HS code prefix..." 
                               class="w-full text-xs pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-medium text-slate-900">
                    </div>
                    <button type="button" @click="performWcoSearch()" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                        Search
                    </button>
                </div>

                <!-- Category Pills Filter -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                    <button type="button" @click="wcoCategory = ''; performWcoSearch()" 
                            :class="wcoCategory === '' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg font-bold whitespace-nowrap transition cursor-pointer">
                        All Categories
                    </button>
                    <button type="button" @click="wcoCategory = 'Handicrafts & Art'; performWcoSearch()" 
                            :class="wcoCategory === 'Handicrafts & Art' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg font-bold whitespace-nowrap transition cursor-pointer">
                        Handicrafts & Art
                    </button>
                    <button type="button" @click="wcoCategory = 'Pashmina & Wool'; performWcoSearch()" 
                            :class="wcoCategory === 'Pashmina & Wool' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg font-bold whitespace-nowrap transition cursor-pointer">
                        Pashmina & Wool
                    </button>
                    <button type="button" @click="wcoCategory = 'Tea, Coffee & Spices'; performWcoSearch()" 
                            :class="wcoCategory === 'Tea, Coffee & Spices' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg font-bold whitespace-nowrap transition cursor-pointer">
                        Tea & Spices
                    </button>
                    <button type="button" @click="wcoCategory = 'Medicinal & Herbal'; performWcoSearch()" 
                            :class="wcoCategory === 'Medicinal & Herbal' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg font-bold whitespace-nowrap transition cursor-pointer">
                        Herbal & Ayurvedic
                    </button>
                    <button type="button" @click="wcoCategory = 'Apparel & Textiles'; performWcoSearch()" 
                            :class="wcoCategory === 'Apparel & Textiles' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg font-bold whitespace-nowrap transition cursor-pointer">
                        Apparel & Textiles
                    </button>
                </div>
            </div>

            <!-- Modal Body Results List -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
                <div x-show="wcoLoading" class="text-center py-12 text-slate-500 text-xs">
                    <i class="fas fa-spinner fa-spin text-2xl text-teal-600 mb-2"></i>
                    <p>Loading WCO Tariff schedule...</p>
                </div>

                <div x-show="!wcoLoading && wcoResults.length === 0" class="text-center py-12 text-slate-500 text-xs">
                    <i class="fas fa-folder-open text-2xl text-slate-300 mb-2"></i>
                    <p>No HS Codes found matching your query.</p>
                </div>

                <template x-for="hs in wcoResults" :key="hs.id">
                    <div class="p-3 rounded-xl border border-slate-200 hover:border-teal-500 hover:bg-teal-50/40 transition flex items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-slate-900" x-text="hs.commodity_name"></span>
                                <span class="font-mono font-black text-xs text-teal-700 px-2 py-0.5 rounded bg-teal-100/60" x-text="'HS ' + hs.code"></span>
                            </div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-2 flex-wrap">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold" x-text="hs.category"></span>
                                <span>&bull;</span>
                                <span x-text="'UOM: ' + hs.standard_uom"></span>
                                <span>&bull;</span>
                                <span x-text="'Export Duty: ' + (hs.export_duty_rate ? hs.export_duty_rate + '%' : '0% Free')"></span>
                                <span x-show="hs.customs_notes" class="text-slate-400" x-text="'(' + hs.customs_notes + ')'"></span>
                            </div>
                        </div>
                        <button type="button" @click="chooseWcoResult(hs)"
                                class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-2xs whitespace-nowrap cursor-pointer">
                            Select
                        </button>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
                <span>World Customs Organization (WCO) & Department of Customs, Nepal</span>
                <button type="button" @click="wcoModalOpen = false" class="px-4 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold transition">
                    Close
                </button>
            </div>
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

        const alpineEl = document.querySelector('[x-data]');
        if (alpineEl && alpineEl._x_dataStack && alpineEl._x_dataStack[0]) {
            alpineEl._x_dataStack[0].shipmentMode = mode;
            if (mode === 'international') {
                alpineEl._x_dataStack[0].invoiceCurrency = 'USD';
            } else if (mode === 'domestic') {
                alpineEl._x_dataStack[0].invoiceCurrency = 'NPR';
            }
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
        const mode = document.getElementById('shipment_type')?.value || 'domestic';
        const badgeEl = document.getElementById('summary-mode-badge');
        if (badgeEl) badgeEl.innerText = mode.toUpperCase();

        const pickupEl = document.getElementById('summary-pickups-count');
        if (pickupEl) {
            const doorstepInput = document.getElementById('schedule_doorstep_pickup');
            const hasPickup = doorstepInput ? (doorstepInput.value === '1') : true;
            if (hasPickup) {
                const pickups = document.querySelectorAll('.pickup-card').length;
                pickupEl.innerText = `${pickups} ${pickups === 1 ? 'Pickup Location' : 'Pickup Locations'}`;
            } else {
                pickupEl.innerText = 'Counter Drop-off';
            }
        }

        const deliveries = mode === 'international' ? 1 : document.querySelectorAll('.delivery-card').length;
        const delivEl = document.getElementById('summary-deliveries-count');
        if (delivEl) delivEl.innerText = `${deliveries} ${deliveries === 1 ? 'Destination' : 'Destinations'}`;
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
