@extends('layouts.app')

@section('title', 'Shipment History & Live Tracking - NETPACK')
@section('page-title', 'Shipment History & Tracking')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-2xl p-6 text-white shadow-sm border border-teal-900/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Consignment Registry
                    </span>
                    <span class="text-xs text-slate-400">&bull; Scoped Client Tracking</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left text-teal-400"></i>
                    <span>Shipment History & Radar Tracking</span>
                </h1>
                <p class="text-xs text-slate-300 mt-1 max-w-xl">
                    View full real-time movement history, telemetry milestones, and access official House Air Waybill (HAWB) copies for all your booked consignments.
                </p>
            </div>

            <!-- Quick Track Search Form -->
            <form action="{{ route('client.history') }}" method="GET" class="relative max-w-sm w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Filter by AWB, HAWB, Consignee..." 
                       class="w-full text-xs bg-slate-800/80 border border-slate-700 rounded-xl pl-9 pr-20 py-2.5 text-white placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 font-mono">
                <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                <button type="submit" 
                        class="absolute right-1.5 top-1.5 px-3 py-1 bg-teal-600 hover:bg-teal-500 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider transition">
                    Search
                </button>
            </form>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('client.history', ['filter' => 'all']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-teal-500/40 transition {{ $filter === 'all' ? 'ring-2 ring-teal-500/20 border-teal-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Booked</span>
                    <span class="text-2xl font-black text-slate-900 mt-1 block">{{ number_format($counts['all']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('client.history', ['filter' => 'active']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-blue-500/40 transition {{ $filter === 'active' ? 'ring-2 ring-blue-500/20 border-blue-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">In Transit</span>
                    <span class="text-2xl font-black text-blue-600 mt-1 block">{{ number_format($counts['active']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fas fa-truck-fast"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('client.history', ['filter' => 'delivered']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-emerald-500/40 transition {{ $filter === 'delivered' ? 'ring-2 ring-emerald-500/20 border-emerald-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Delivered</span>
                    <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ number_format($counts['delivered']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('client.inquiries') }}" 
           class="p-4 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-600 text-white shadow-xs hover:shadow-md transition flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-teal-100 uppercase tracking-wider block">Need Pickup?</span>
                <span class="text-sm font-bold text-white mt-1 block">New Inquiry &rarr;</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                <i class="fas fa-plus"></i>
            </div>
        </a>
    </div>

    <!-- Filter Navigation Bar -->
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200/80 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('client.history', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-xl transition {{ $filter === 'all' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                All Consignments ({{ $counts['all'] }})
            </a>
            <a href="{{ route('client.history', ['filter' => 'active']) }}" 
               class="px-4 py-2 rounded-xl transition {{ $filter === 'active' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                Active In-Transit ({{ $counts['active'] }})
            </a>
            <a href="{{ route('client.history', ['filter' => 'delivered']) }}" 
               class="px-4 py-2 rounded-xl transition {{ $filter === 'delivered' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium' }}">
                Completed History ({{ $counts['delivered'] }})
            </a>
        </div>

        @if(request('search'))
            <a href="{{ route('client.history') }}" class="text-xs text-rose-600 font-semibold hover:underline flex items-center gap-1">
                <i class="fas fa-xmark"></i>
                <span>Clear Search</span>
            </a>
        @endif
    </div>

    <!-- Shipments Table / Card Registry -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        @if($shipments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Consignment Reference</th>
                            <th class="px-5 py-3.5">Destination & Consignee</th>
                            <th class="px-5 py-3.5">Service & Weight</th>
                            <th class="px-5 py-3.5">Current Status</th>
                            <th class="px-5 py-3.5">Booked On</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($shipments as $shipment)
                            @php
                                $st = strtolower($shipment->status ?? 'pending');
                                $statusBadge = match($st) {
                                    'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'in_transit' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'out_for_delivery' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'picked_up' => 'bg-teal-100 text-teal-800 border-teal-200',
                                    'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-slate-100 text-slate-800 border-slate-200'
                                };
                                $isOngoing = !in_array($st, ['delivered', 'cancelled', 'returned']);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4">
                                    <div class="space-y-0.5">
                                        <a href="{{ route('tracking.show', $shipment->tracking_number) }}" 
                                           class="font-mono font-black text-xs text-slate-900 hover:text-teal-700 flex items-center gap-1.5">
                                            <i class="fas fa-barcode text-teal-600"></i>
                                            <span>{{ $shipment->tracking_number }}</span>
                                        </a>
                                        @if($shipment->hawb_number)
                                            <span class="inline-block px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 text-[10px] font-mono">
                                                HAWB: {{ $shipment->hawb_number }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-slate-900">
                                            {{ $shipment->destination ?? ($shipment->receiver_city ?? 'Destination Hub') }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            {{ $shipment->receiver_name ?? 'Consignee' }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="space-y-0.5">
                                        <span class="font-semibold text-slate-800">
                                            {{ ucwords(str_replace('_', ' ', $shipment->service_type ?? 'Standard')) }}
                                        </span>
                                        <p class="text-[11px] text-slate-400 font-mono">
                                            {{ number_format($shipment->chargeable_weight ?: $shipment->actual_weight ?: 1.0, 1) }} KG
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusBadge }}">
                                        @if($isOngoing)
                                            <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                                        @endif
                                        {{ str_replace('_', ' ', $shipment->status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-slate-500 text-[11px] font-mono">
                                    {{ $shipment->created_at ? $shipment->created_at->format('M d, Y') : '' }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('tracking.show', $shipment->tracking_number) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-600 text-teal-700 hover:text-white font-bold text-[11px] transition flex items-center gap-1"
                                           title="Live Telemetry Radar">
                                            <i class="fas fa-satellite-dish text-[10px]"></i>
                                            <span>Radar</span>
                                        </a>

                                        @if($shipment->id)
                                            <a href="{{ route('hawb.print', ['id' => $shipment->id, 'type' => 'international']) }}" 
                                               target="_blank"
                                               class="px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white font-bold text-[11px] transition flex items-center gap-1"
                                               title="Print HAWB Document">
                                                <i class="fas fa-print text-[10px]"></i>
                                                <span>HAWB</span>
                                            </a>
                                        @endif

                                        <!-- Report Issue Action Button -->
                                        <button type="button" 
                                                onclick="openHistoryIssueModal('{{ $shipment->tracking_number }}')" 
                                                class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white font-bold text-[11px] transition flex items-center gap-1 cursor-pointer"
                                                title="Report any issue or situation regarding this consignment">
                                            <i class="fas fa-triangle-exclamation text-[10px]"></i>
                                            <span>Issue</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $shipments->withQueryString()->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl mb-3">
                    <i class="fas fa-boxes-packing"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">No Consignments Found</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    There are no shipments matching your filter criteria. Ready to send a parcel?
                </p>
                <a href="{{ route('client.inquiries') }}" 
                   class="mt-4 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Book Shipment Pickup</span>
                </a>
            </div>
        @endif
    </div>
</div>

<!-- CLIENT ISSUE & SITUATION REPORTING MODAL -->
<div id="historyIssueModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <span class="h-9 w-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-triangle-exclamation"></i>
                </span>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Report Shipment Issue / Situation</h3>
                    <p class="text-[11px] text-slate-500">
                        Consignment Tracking: <span id="modalTrackingDisplay" class="font-mono font-bold text-slate-800"></span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeHistoryIssueModal()" class="text-slate-400 hover:text-slate-600 text-lg p-1 cursor-pointer">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs text-slate-700">
            <div class="p-3 rounded-xl bg-teal-50/70 border border-teal-200/70 text-[11px] text-teal-900 flex items-start gap-2">
                <i class="fas fa-circle-info text-teal-600 text-sm mt-0.5"></i>
                <span>Our operations team investigates all situations—package damage, transit delays, customs holds, or billing questions. Your report is linked directly to consignment telemetry.</span>
            </div>

            <form id="historyIssueForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Situation Category <span class="text-rose-500">*</span>
                    </label>
                    <select name="issue_type" required class="w-full text-xs font-semibold px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 text-slate-800">
                        <option value="damage">📦 Damaged Package or Broken Contents</option>
                        <option value="delay">⏱️ Excessive Transit Delay / Missed Delivery SLA</option>
                        <option value="lost_item">❓ Missing Items / Lost Parcel</option>
                        <option value="customs_hold">🛂 Customs Clearance Hold / Document Required</option>
                        <option value="billing_discrepancy">💵 Billing, Invoice or Tariff Discrepancy</option>
                        <option value="return_request">🔄 Return to Origin (RTO) Request</option>
                        <option value="rider_conduct">🛵 Courier / Rider Conduct Feedback</option>
                        <option value="general_inquiry">💬 General Inquiry / Other Situation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Details of Situation / Issue <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="situation_description" rows="4" required minlength="8"
                              placeholder="Please describe the exact situation or issue in detail..."
                              class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 text-slate-900"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Claim Amount (Optional)</label>
                        <div class="flex items-center gap-1">
                            <select name="claimed_currency" class="text-xs font-bold px-2 py-1.5 bg-slate-100 border border-slate-300 rounded-lg">
                                <option value="NPR">NPR (Rs.)</option>
                                <option value="USD">USD ($)</option>
                            </select>
                            <input type="number" step="0.01" min="0" name="claimed_amount" placeholder="0.00"
                                   class="w-full text-xs font-mono font-bold px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 text-slate-900">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Photo / Proof Attachment</label>
                        <input type="file" name="attachment" accept="image/*,.pdf"
                               class="w-full text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 text-slate-600 border border-slate-300 rounded-lg">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeHistoryIssueModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-md transition cursor-pointer">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit Situation Report</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openHistoryIssueModal(trackingNumber) {
    document.getElementById('modalTrackingDisplay').textContent = trackingNumber;
    const form = document.getElementById('historyIssueForm');
    form.action = '/shipments/' + encodeURIComponent(trackingNumber) + '/issues';
    document.getElementById('historyIssueModal').classList.remove('hidden');
}

function closeHistoryIssueModal() {
    document.getElementById('historyIssueModal').classList.add('hidden');
}
</script>
@endpush
@endsection
