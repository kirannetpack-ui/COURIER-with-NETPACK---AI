<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing List - {{ $shipment->tracking_number }}</title>
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
            <span class="text-xs text-slate-500 font-medium">Export Packing List &bull; Box Distribution Matrix</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                <i class="fas fa-print"></i>
                <span>Print Packing List</span>
            </button>
            <a href="{{ route('shipments.invoice', $shipment->id) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fas fa-file-invoice-dollar text-teal-600"></i>
                <span>View Commercial Invoice</span>
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
                <p class="text-[11px] text-slate-500">Air Cargo Packaging, Tally & Customs Manifest Documentation</p>
                <p class="text-[10px] text-slate-400">Kathmandu Hub, Nepal &bull; info@netpackcourier.com</p>
            </div>

            <div class="text-right space-y-1">
                <h1 class="text-xl font-black uppercase tracking-wider text-slate-900">PACKING LIST</h1>
                <p class="text-xs font-mono font-bold text-teal-800">Ref: PL-{{ $shipment->tracking_number }}</p>
                <p class="text-[11px] text-slate-500">Date: <span class="font-medium text-slate-800">{{ date('d M Y', strtotime($shipment->created_at ?? now())) }}</span></p>
            </div>
        </div>

        <!-- 2-Column Parties Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Shipper / Exporter -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-1 bg-slate-50/40 text-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-teal-800 block border-b border-slate-200 pb-1 mb-1">
                    SHIPPER / CONSIGNOR
                </span>
                <h3 class="font-bold text-slate-900">{{ $shipment->sender_name }}</h3>
                <p class="text-slate-600">{{ $shipment->sender_address }}, {{ $shipment->sender_city }}, Nepal</p>
                <p class="text-slate-600 font-mono">Tel: {{ $shipment->sender_phone }}</p>
            </div>

            <!-- Consignee / Importer -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-1 bg-slate-50/40 text-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-800 block border-b border-slate-200 pb-1 mb-1">
                    CONSIGNEE / RECIPIENT
                </span>
                <h3 class="font-bold text-slate-900">{{ $shipment->receiver_name }}</h3>
                <p class="text-slate-600">{{ $shipment->receiver_address }}, {{ $shipment->receiver_city }} {{ $shipment->receiver_postal_code }}</p>
                <p class="font-bold text-slate-900">{{ $shipment->receiver_country }}</p>
                <p class="text-slate-600 font-mono">Tel: {{ $shipment->receiver_phone }}</p>
            </div>
        </div>

        <!-- Total Cargo Metric Bar -->
        @php
            $boxes = $packingListData['boxes'] ?? [];
            $totalBoxes = count($boxes) ?: 1;
            $totalGross = (float) ($packingListData['total_gross_weight'] ?? $shipment->actual_weight);
            $totalVol = (float) ($packingListData['total_volumetric_weight'] ?? ($shipment->volumetric_weight ?? 0));
            $totalChargeable = max($totalGross, $totalVol);
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 bg-slate-900 text-white rounded-xl text-xs">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Packages</span>
                <span class="font-black font-mono text-base text-teal-300">{{ $totalBoxes }} {{ $totalBoxes === 1 ? 'Box' : 'Boxes' }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Gross Weight</span>
                <span class="font-black font-mono text-base text-white">{{ number_format($totalGross, 2) }} KG</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Volumetric Weight</span>
                <span class="font-black font-mono text-base text-white">{{ number_format($totalVol, 2) }} KG</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Chargeable Weight</span>
                <span class="font-black font-mono text-base text-emerald-300">{{ number_format($totalChargeable, 2) }} KG</span>
            </div>
        </div>

        <!-- Box-By-Box Packing Details -->
        <div class="space-y-4">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i class="fas fa-boxes-packing text-teal-700"></i>
                <span>Detailed Box Packing & Contents Allocation</span>
            </h2>

            @foreach($boxes as $idx => $box)
                @php
                    $boxNum = $box['box_number'] ?? ($idx + 1);
                    $boxL = (float) ($box['length'] ?? 0);
                    $boxW = (float) ($box['width'] ?? 0);
                    $boxH = (float) ($box['height'] ?? 0);
                    $boxGross = (float) ($box['gross_weight'] ?? 0);
                    $boxVol = (float) ($box['volumetric_weight'] ?? (($boxL * $boxW * $boxH) / 5000));
                    $boxItems = $box['items'] ?? [];
                @endphp

                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                    <!-- Box Header Banner -->
                    <div class="bg-slate-100 p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs font-black flex items-center justify-center">
                                {{ $boxNum }}
                            </span>
                            <span class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">
                                Package #{{ $boxNum }} of {{ $totalBoxes }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono">
                                Marks: {{ $shipment->tracking_number }}/BOX-{{ $boxNum }}
                            </span>
                        </div>

                        <div class="flex items-center gap-4 text-xs font-mono font-semibold text-slate-700">
                            <span>Dim: {{ $boxL }} × {{ $boxW }} × {{ $boxH }} cm</span>
                            <span class="text-slate-300">|</span>
                            <span>Gross: <strong class="text-slate-900">{{ number_format($boxGross, 2) }} KG</strong></span>
                            <span class="text-slate-300">|</span>
                            <span>Vol: {{ number_format($boxVol, 2) }} KG</span>
                        </div>
                    </div>

                    <!-- Items packed inside this Box -->
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase font-bold border-b border-slate-200">
                                <th class="p-2.5 pl-4 w-10 text-center">#</th>
                                <th class="p-2.5">Commodity / Item Description</th>
                                <th class="p-2.5 w-32 font-mono">HS Code</th>
                                <th class="p-2.5 text-center w-24">Quantity Packed</th>
                                <th class="p-2.5 text-center w-20">UOM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($boxItems as $bIdx => $bItem)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-2.5 pl-4 text-center text-slate-400 font-bold">{{ $bIdx + 1 }}</td>
                                    <td class="p-2.5 font-bold text-slate-800">{{ $bItem['item_name'] ?? ($bItem['description'] ?? 'Export Merchandise') }}</td>
                                    <td class="p-2.5 font-mono text-teal-700 font-bold">{{ $bItem['hs_code'] ?? '9803.00.00' }}</td>
                                    <td class="p-2.5 text-center font-bold text-slate-900 font-mono">{{ number_format((float) ($bItem['quantity'] ?? 1)) }}</td>
                                    <td class="p-2.5 text-center text-slate-600 font-semibold">{{ $bItem['uom'] ?? 'PCS' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-3 text-center text-slate-400 italic">No specific items allocated to this box.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        <!-- Packing Declaration & Signature -->
        <div class="pt-4 border-t-2 border-slate-900 space-y-4">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[10px] text-slate-600 leading-relaxed">
                <strong class="text-slate-900">PACKING VERIFICATION CERTIFICATE:</strong>
                "We hereby certify that the goods mentioned above have been carefully examined, counted, securely packed in accordance with international air cargo standards, and that all outer cartons bear the specified shipping marks and HAWB tracking identifiers."
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-end gap-6 pt-2">
                <!-- QR Code telemetry verification -->
                <div class="flex items-center gap-3">
                    <div class="p-1 border border-slate-200 rounded-lg bg-white">
                        {!! $qrCode !!}
                    </div>
                    <div class="text-[10px] text-slate-500 space-y-0.5">
                        <span class="font-bold text-slate-800 block">HAWB Cargo Tally Verification</span>
                        <span>Scan for box count & weight telemetry</span>
                        <span class="font-mono text-teal-700 block font-bold">{{ $shipment->tracking_number }}</span>
                    </div>
                </div>

                <!-- Signature & Stamp Box -->
                <div class="text-right space-y-2 w-64">
                    <div class="h-16 border-b border-dashed border-slate-400 flex items-end justify-end pb-1 text-slate-400 text-[10px] italic">
                        [ Warehouse Supervisor / Signatory ]
                    </div>
                    <div class="text-xs">
                        <p class="font-bold text-slate-900">{{ $shipment->sender_name }}</p>
                        <p class="text-[10px] text-slate-500">Certified Packaging Inspector</p>
                        <p class="text-[10px] text-slate-400 font-mono">Date: {{ date('d M Y', strtotime($shipment->created_at ?? now())) }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
