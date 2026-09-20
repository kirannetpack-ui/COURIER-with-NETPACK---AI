<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Invoice - {{ $shipment->tracking_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { 
                background: white !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                font-size: 11px !important; 
            }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
            @page { 
                size: A4 portrait; 
                margin: 10mm; 
            }
        }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 py-6 px-3 sm:px-6 text-slate-800 text-xs">

    <!-- Action Toolbar (Hidden during print) -->
    <div class="max-w-4xl mx-auto mb-4 no-print flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 font-mono font-bold text-xs border border-teal-200">
                HAWB: {{ $shipment->tracking_number }}
            </span>
            <span class="text-xs text-slate-500 font-medium">Commercial Invoice &bull; WCO & Nepal Customs Compliant</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                <i class="fas fa-print"></i>
                <span>Print Commercial Invoice</span>
            </button>
            <a href="{{ route('shipments.packing-list', $shipment->id) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fas fa-boxes-stacked text-teal-600"></i>
                <span>View Packing List</span>
            </a>
            @if(!empty($shipment->seller_bill_file) || !empty($shipment->invoice_file))
                <a href="{{ route('shipments.seller-bill', $shipment->id) }}" target="_blank" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-paperclip text-amber-600"></i>
                    <span>Attached Bill</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Official Printable Document Container -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-10 rounded-2xl shadow-md border border-slate-200 print-shadow-none space-y-6">
        
        <!-- Header Banner -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-5 gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xl font-black tracking-tight text-teal-900 uppercase">NETPACK</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-slate-900 text-white uppercase tracking-wider">Logistics Worldwide</span>
                </div>
                <p class="text-[11px] text-slate-500">Air Cargo, Express Courier & Customs Clearance Operations</p>
                <p class="text-[10px] text-slate-400">Kathmandu Hub, Nepal &bull; info@netpackcourier.com &bull; +977-1-4400000</p>
            </div>

            <div class="text-right space-y-1">
                <h1 class="text-xl font-black uppercase tracking-wider text-slate-900">COMMERCIAL INVOICE</h1>
                <p class="text-xs font-mono font-bold text-teal-800">No: {{ $invoiceData['invoice_number'] ?? ('INV-' . $shipment->tracking_number) }}</p>
                <p class="text-[11px] text-slate-500">Date: <span class="font-medium text-slate-800">{{ $invoiceData['invoice_date'] ?? date('d M Y') }}</span></p>
            </div>
        </div>

        <!-- Metadata Bar: Incoterms, Currency, Reason for Export -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 block">Currency</span>
                <span class="font-black text-slate-900 font-mono text-sm">{{ $invoiceData['currency'] ?? 'USD' }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 block">Terms of Sale (Incoterms)</span>
                <span class="font-bold text-slate-800">{{ $invoiceData['incoterm'] ?? 'DAP' }} (Delivered at Place)</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 block">Reason for Export</span>
                <span class="font-bold text-slate-800">{{ $invoiceData['export_reason'] ?? 'Commercial Sale / Trade' }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 block">House Air Waybill (HAWB)</span>
                <span class="font-mono font-bold text-teal-700">{{ $shipment->hawb_number ?: $shipment->tracking_number }}</span>
                @if($shipment->hawb_number && $shipment->tracking_number)
                    <span class="text-[9px] font-mono text-slate-400 block">Ref: {{ $shipment->tracking_number }}</span>
                @endif
            </div>
        </div>

        <!-- 2-Column Parties Grid: Shipper / Exporter vs Consignee / Importer -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-1">
            <!-- Shipper / Exporter -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2 bg-slate-50/40">
                <div class="flex items-center justify-between border-b border-slate-200 pb-1.5 mb-1.5">
                    <span class="text-[10px] font-black uppercase tracking-wider text-teal-800">1. SHIPPER / EXPORTER (NEPAL)</span>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-900">Origin: NP</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">{{ $shipment->sender_name }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $shipment->sender_address }}</p>
                    <p class="text-xs text-slate-600">{{ $shipment->sender_city }}, Nepal</p>
                    <p class="text-xs text-slate-600 font-mono mt-1">Tel: {{ $shipment->sender_phone }}</p>
                </div>
                <div class="pt-2 border-t border-slate-200 grid grid-cols-2 gap-2 text-[10px]">
                    <div>
                        <span class="text-slate-400 font-semibold block">EXPORTER PAN/VAT:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $invoiceData['shipper_pan_vat'] ?? '100234567' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block">NEPAL EXIM CODE:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $invoiceData['shipper_exim_code'] ?? 'NP9800012345' }}</span>
                    </div>
                </div>
            </div>

            <!-- Consignee / Importer -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2 bg-slate-50/40">
                <div class="flex items-center justify-between border-b border-slate-200 pb-1.5 mb-1.5">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-800">2. CONSIGNEE / IMPORTER (DESTINATION)</span>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-800">Dest: {{ $shipment->receiver_country }}</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">{{ $shipment->receiver_name }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $shipment->receiver_address }}</p>
                    <p class="text-xs text-slate-600">{{ $shipment->receiver_city }} {{ $shipment->receiver_state ? ', ' . $shipment->receiver_state : '' }} {{ $shipment->receiver_postal_code }}</p>
                    <p class="text-xs font-bold text-slate-900">{{ $shipment->receiver_country }}</p>
                    <p class="text-xs text-slate-600 font-mono mt-1">Tel: {{ $shipment->receiver_phone }}</p>
                </div>
                <div class="pt-2 border-t border-slate-200 text-[10px]">
                    <span class="text-slate-400 font-semibold block">CONSIGNEE TAX ID / EORI / VAT:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $invoiceData['consignee_tax_id'] ?? ($shipment->receiver_tax_id ?: 'N/A') }}</span>
                </div>
            </div>
        </div>

        <!-- Itemized Commodities & HS Codes Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white font-bold uppercase text-[10px] tracking-wider">
                        <th class="p-3 text-center w-10">#</th>
                        <th class="p-3">Commodity Description</th>
                        <th class="p-3 w-28">HS Code (WCO)</th>
                        <th class="p-3 text-center w-20">Origin</th>
                        <th class="p-3 text-center w-16">Qty</th>
                        <th class="p-3 text-center w-14">UOM</th>
                        <th class="p-3 text-right w-24">Unit ({{ $invoiceData['currency'] ?? 'USD' }})</th>
                        <th class="p-3 text-right w-28">Total ({{ $invoiceData['currency'] ?? 'USD' }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @php
                        $items = $invoiceData['items'] ?? [];
                        $calcSubtotal = 0;
                        $calcQty = 0;
                    @endphp

                    @forelse($items as $idx => $item)
                        @php
                            $lineQty = (float) ($item['quantity'] ?? 1);
                            $unitVal = (float) ($item['unit_value'] ?? 0);
                            $lineTotal = (float) ($item['total_value'] ?? ($lineQty * $unitVal));
                            $calcSubtotal += $lineTotal;
                            $calcQty += $lineQty;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="p-3">
                                <span class="font-bold text-slate-900">{{ $item['description'] ?? 'Export Merchandise' }}</span>
                                @if(!empty($item['notes']))
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $item['notes'] }}</span>
                                @endif
                            </td>
                            <td class="p-3 font-mono font-bold text-teal-800">{{ $item['hs_code'] ?? '9803.00.00' }}</td>
                            <td class="p-3 text-center text-slate-600 font-semibold">{{ $item['origin_country'] ?? 'Nepal' }}</td>
                            <td class="p-3 text-center font-bold text-slate-900 font-mono">{{ number_format($lineQty) }}</td>
                            <td class="p-3 text-center text-slate-600 font-semibold">{{ $item['uom'] ?? 'PCS' }}</td>
                            <td class="p-3 text-right font-mono text-slate-800">{{ number_format($unitVal, 2) }}</td>
                            <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400 italic">No item details recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals and Financial Summary -->
        <div class="flex flex-col sm:flex-row justify-between gap-6 pt-2">
            <div class="space-y-3 flex-1">
                <!-- Package specs summary -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] space-y-1">
                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block mb-1">Cargo Specifications:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-slate-400">Total Pieces:</span>
                            <span class="font-bold text-slate-800 font-mono">{{ $invoiceData['total_boxes'] ?? (count($shipment->boxes ?? []) ?: 1) }} Box(es)</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Gross Weight:</span>
                            <span class="font-bold text-slate-800 font-mono">{{ number_format((float) $shipment->actual_weight, 2) }} KG</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Total Quantity:</span>
                            <span class="font-bold text-slate-800 font-mono">{{ number_format($calcQty) }} Units</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Chargeable Weight:</span>
                            <span class="font-bold text-slate-800 font-mono">{{ number_format((float) $shipment->chargeable_weight, 2) }} KG</span>
                        </div>
                    </div>
                </div>

                <!-- Attached Seller Bill Notification if present -->
                @if(!empty($shipment->seller_bill_file))
                    <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] flex items-center gap-2">
                        <i class="fas fa-check-circle text-emerald-600"></i>
                        <span>Original Seller Tax Invoice / VAT Bill attached to this consignment.</span>
                    </div>
                @endif
            </div>

            <!-- Financial Totals Block -->
            <div class="w-full sm:w-80 border border-slate-300 rounded-xl overflow-hidden font-medium text-xs">
                <div class="flex justify-between p-2.5 border-b border-slate-200 bg-slate-50">
                    <span class="text-slate-600 font-bold">Subtotal Goods:</span>
                    <span class="font-mono font-bold text-slate-900">{{ $invoiceData['currency'] ?? 'USD' }} {{ number_format($calcSubtotal, 2) }}</span>
                </div>
                <div class="flex justify-between p-2.5 border-b border-slate-200">
                    <span class="text-slate-500">Freight Charge:</span>
                    <span class="font-mono text-slate-700">{{ $invoiceData['currency'] ?? 'USD' }} {{ number_format((float) ($invoiceData['freight_charge'] ?? $shipment->shipping_cost), 2) }}</span>
                </div>
                <div class="flex justify-between p-2.5 border-b border-slate-200">
                    <span class="text-slate-500">Insurance & Surcharges:</span>
                    <span class="font-mono text-slate-700">{{ $invoiceData['currency'] ?? 'USD' }} {{ number_format((float) ($invoiceData['insurance_charge'] ?? $shipment->insurance_fee), 2) }}</span>
                </div>
                @php
                    $finalGrandTotal = (float) ($invoiceData['grand_total'] ?? ($calcSubtotal + (float)($shipment->shipping_cost ?? 0)));
                @endphp
                <div class="flex justify-between p-3 bg-slate-900 text-white font-black text-sm">
                    <span>GRAND TOTAL:</span>
                    <span class="font-mono text-teal-300">{{ $invoiceData['currency'] ?? 'USD' }} {{ number_format($finalGrandTotal, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Official Exporter Declaration & Sign-off Block -->
        <div class="pt-4 border-t-2 border-slate-900 space-y-4">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[10px] text-slate-600 leading-relaxed">
                <strong class="text-slate-900">EXPORTER LEGAL DECLARATION:</strong>
                "I/We hereby certify that this commercial invoice shows the full and actual price of the goods described, that no other invoice has been or will be issued for this consignment, that the contents and quantities are true and correct, and that the products originate from Nepal in accordance with applicable WCO and national customs regulations."
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-end gap-6 pt-2">
                <!-- QR Code telemetry verification -->
                <div class="flex items-center gap-3">
                    <div class="p-1 border border-slate-200 rounded-lg bg-white">
                        {!! $qrCode !!}
                    </div>
                    <div class="text-[10px] text-slate-500 space-y-0.5">
                        <span class="font-bold text-slate-800 block">Verified Digital Air Cargo HAWB</span>
                        <span>Scan for live customs telemetry & tracking</span>
                        <span class="font-mono text-teal-700 block font-bold">{{ $shipment->tracking_number }}</span>
                    </div>
                </div>

                <!-- Signature & Stamp Box -->
                <div class="text-right space-y-2 w-64">
                    <div class="h-16 border-b border-dashed border-slate-400 flex items-end justify-end pb-1 text-slate-400 text-[10px] italic">
                        [ Authorized Signature & Company Stamp ]
                    </div>
                    <div class="text-xs">
                        <p class="font-bold text-slate-900">{{ $shipment->sender_name }}</p>
                        <p class="text-[10px] text-slate-500">Authorized Exporter Representative</p>
                        <p class="text-[10px] text-slate-400 font-mono">Date: {{ $invoiceData['invoice_date'] ?? date('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
