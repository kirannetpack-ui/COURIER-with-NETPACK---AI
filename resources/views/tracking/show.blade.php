@extends('layouts.app')

@section('title', 'Track Shipment - ' . ($shipment->formatted_tracking_number ?? $shipment->tracking_number))

@section('styles')
<style>
    .tracking-timeline {
        position: relative;
        padding: 20px 0;
    }
    .tracking-timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #e5e7eb;
    }
    .tracking-timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 30px;
    }
    .tracking-timeline-item:last-child {
        padding-bottom: 0;
    }
    .tracking-timeline-item .timeline-icon {
        position: absolute;
        left: 8px;
        top: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        font-size: 14px;
    }
    .tracking-timeline-item.completed .timeline-icon {
        background: #10b981;
        color: white;
    }
    .tracking-timeline-item.active .timeline-icon {
        background: #3b82f6;
        color: white;
        animation: pulse 2s infinite;
    }
    .tracking-timeline-item.pending .timeline-icon {
        background: #9ca3af;
        color: white;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    .tracking-timeline-item .timeline-content {
        background: white;
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    .tracking-timeline-item .timeline-content:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .tracking-timeline-item.completed .timeline-content {
        border-left: 3px solid #10b981;
    }
    .tracking-timeline-item.active .timeline-content {
        border-left: 3px solid #3b82f6;
        background: #eff6ff;
    }
    .tracking-timeline-item.pending .timeline-content {
        border-left: 3px solid #9ca3af;
        opacity: 0.7;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .tracking-number-display {
        font-family: 'Courier New', monospace;
        font-size: 18px;
        letter-spacing: 2px;
        background: #f8fafc;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px dashed #d1d5db;
    }
    .status-card {
        transition: all 0.3s ease;
    }
    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
<div class="bg-gradient-to-r from-teal-600 to-blue-600 rounded-xl shadow-lg p-6 mb-6 text-white">
    <div class="flex flex-col md:flex-row items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <i class="fas fa-box text-2xl"></i>
                <h1 class="text-2xl font-bold">Track Your Shipment</h1>
            </div>
            <div class="tracking-number-display text-white bg-white/20 border-white/30 mt-2">
                {{ $shipment->formatted_tracking_number ?? $shipment->tracking_number }}
            </div>
            @if($shipment->hawb_number)
                <div class="mt-2 text-sm bg-white/20 px-3 py-1 rounded-lg inline-block">
                    <i class="fas fa-file-alt mr-1"></i> 
                    HAWB: {{ $shipment->hawb_number }}
                    <a href="{{ route('hawb.international', $shipment->id) }}" target="_blank" 
                       class="ml-2 text-white hover:text-teal-200 underline">
                        <i class="fas fa-external-link-alt"></i> View
                    </a>
                </div>
            @endif
        </div>
        <div class="mt-3 md:mt-0">
            <div class="flex items-center gap-4 bg-white/20 px-4 py-2 rounded-lg">
                <i class="fas fa-calendar-alt"></i>
                <span>Ordered: {{ $shipment->created_at->format('M d, Y') }}</span>
            </div>
    </div>
</div>

    <!-- AI Status Explainer Card (Maximum Communication: What, Where, Why, Next Step) -->
    @php
        $aiService = app(\App\Services\AiAssistantService::class);
        $aiExplanation = $aiService->explainShipmentStatus($shipment->status, $shipment);
    @endphp
    <div class="bg-white rounded-2xl border border-teal-200/90 p-5 shadow-xs mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600 text-lg shadow-2xs">
                    <i class="fas fa-brain"></i>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 flex items-center gap-2">
                        <span>Chanda AI &bull; Operational Status Intelligence</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-teal-50 text-teal-700 border border-teal-200 uppercase">Live Telemetry</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Real-time contextual transparency behind this milestone</p>
                </div>
            </div>

            <!-- Voice Readout Button -->
            <button type="button"
                    onclick="const a = new Audio('{{ route('ai.speech.stream') }}?accent=en-IN&text=' + encodeURIComponent('{{ addslashes($aiExplanation['speech_text']) }}')); a.play();"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-xs font-bold rounded-xl transition shadow-2xs focus:outline-none">
                <i class="fas fa-volume-high text-teal-600"></i>
                <span>Listen to Status Brief</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-xs">
            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-200/70">
                <div class="flex items-center gap-1.5 text-teal-700 font-bold uppercase tracking-wider text-[10px] mb-1">
                    <i class="fas fa-gears"></i>
                    <span>What is happening</span>
                </div>
                <p class="text-slate-800 font-medium leading-snug">{{ $aiExplanation['what'] }}</p>
            </div>

            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-200/70">
                <div class="flex items-center gap-1.5 text-sky-700 font-bold uppercase tracking-wider text-[10px] mb-1">
                    <i class="fas fa-location-dot"></i>
                    <span>Current Waypoint</span>
                </div>
                <p class="text-slate-800 font-medium leading-snug">{{ $aiExplanation['where'] }}</p>
            </div>

            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-200/70">
                <div class="flex items-center gap-1.5 text-amber-700 font-bold uppercase tracking-wider text-[10px] mb-1">
                    <i class="fas fa-circle-info"></i>
                    <span>Why (Operational Reason)</span>
                </div>
                <p class="text-slate-800 font-medium leading-snug">{{ $aiExplanation['why'] }}</p>
            </div>

            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-200/70">
                <div class="flex items-center gap-1.5 text-emerald-700 font-bold uppercase tracking-wider text-[10px] mb-1">
                    <i class="fas fa-forward-step"></i>
                    <span>Next Step & Handover</span>
                </div>
                <p class="text-slate-800 font-medium leading-snug">{{ $aiExplanation['next_step'] }}</p>
            </div>
        </div>

        @if(!empty($aiExplanation['nepali']))
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-600">
                <span class="text-xs">🇳🇵</span>
                <span class="font-medium text-slate-700 font-serif italic">{{ $aiExplanation['nepali'] }}</span>
            </div>
        @endif
    </div>

    <!-- Status Summary -->
    @php
        $statusInfo = $shipment->tracking_status ?? [
            'label' => ucfirst(str_replace('_', ' ', $shipment->status)),
            'icon' => 'fa-clock',
            'color' => 'gray',
            'description' => 'Status updated'
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 status-card">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-{{ $statusInfo['color'] }}-100 flex items-center justify-center">
                    <i class="fas {{ $statusInfo['icon'] }} text-{{ $statusInfo['color'] }}-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Current Status</p>
                    <p class="font-semibold text-gray-800">{{ $statusInfo['label'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 status-card">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-ship text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Service</p>
                    <p class="font-semibold text-gray-800">{{ ucfirst($shipment->service_type ?? 'Standard') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 status-card">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-weight-hanging text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Weight</p>
                    <p class="font-semibold text-gray-800">{{ $shipment->chargeable_weight ?? $shipment->weight ?? 0 }} kg</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 status-card">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-route text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Route</p>
                    <p class="font-semibold text-gray-800">{{ $shipment->sender_country ?? 'Nepal' }} → {{ $shipment->receiver_country ?? 'Destination' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tracking Timeline -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-map-pin text-teal-600"></i>
                Tracking Timeline
            </h3>
            
            <div class="tracking-timeline">
                @php
                    $events = $shipment->tracking_history ?? [];
                    if (empty($events)) {
                        $events = [
                            [
                                'status' => $shipment->status,
                                'status_label' => ucfirst(str_replace('_', ' ', $shipment->status)),
                                'description' => 'Shipment is ' . str_replace('_', ' ', $shipment->status),
                                'location' => $shipment->sender_city ?? 'Nepal',
                                'time' => $shipment->created_at->toDateTimeString(),
                            ]
                        ];
                    }
                @endphp

                @foreach($events as $index => $event)
                    @php
                        $isCompleted = $index < count($events) - 1;
                        $isActive = $index == count($events) - 1;
                        $isPending = !$isCompleted && !$isActive;
                    @endphp
                    <div class="tracking-timeline-item {{ $isCompleted ? 'completed' : ($isActive ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            @if($isCompleted)
                                <i class="fas fa-check"></i>
                            @elseif($isActive)
                                <i class="fas fa-spinner fa-spin"></i>
                            @else
                                <i class="fas fa-clock"></i>
                            @endif
                        </div>
                        <div class="timeline-content">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-800">{{ $event['status_label'] ?? ucfirst(str_replace('_', ' ', $event['status'])) }}</span>
                                        @if($isCompleted)
                                            <span class="status-badge bg-green-100 text-green-800">Completed</span>
                                        @elseif($isActive)
                                            <span class="status-badge bg-blue-100 text-blue-800">In Progress</span>
                                        @else
                                            <span class="status-badge bg-gray-100 text-gray-600">Pending</span>
                                        @endif
                                    </div>
                                    @if($event['description'] ?? false)
                                        <p class="text-sm text-gray-600 mt-1">{{ $event['description'] }}</p>
                                    @endif
                                    @if($event['location'] ?? false)
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $event['location'] }}
                                        </p>
                                    @endif
                                </div>
                                @if($event['time'] ?? false)
                                    <div class="text-sm text-gray-500 mt-2 md:mt-0">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ \Carbon\Carbon::parse($event['time'])->format('M d, Y h:i A') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sidebar - Shipment Details -->
        <div class="space-y-6">
            <!-- Shipment Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-teal-600"></i>
                    Shipment Details
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Tracking Number</p>
                        <p class="font-mono text-sm font-semibold">{{ $shipment->formatted_tracking_number ?? $shipment->tracking_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Service Type</p>
                        <p class="text-sm font-medium">{{ ucfirst($shipment->service_type ?? 'Standard') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Weight</p>
                        <p class="text-sm font-medium">{{ $shipment->chargeable_weight ?? $shipment->weight ?? 0 }} kg</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Estimated Delivery</p>
                        <p class="text-sm font-medium">{{ $shipment->estimated_delivery ? \Carbon\Carbon::parse($shipment->estimated_delivery)->format('M d, Y') : 'To be updated' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total Amount</p>
                        <p class="text-sm font-bold text-teal-600">${{ number_format($shipment->total_amount ?? 0, 2) }}</p>
                    </div>
<div>
    <p class="text-xs text-gray-500">HAWB Number</p>
    <p class="font-mono text-sm font-semibold">
        {{ $shipment->hawb_number ?? 'Not generated' }}
        @if($shipment->hawb_number)
            <a href="{{ route('hawb.international', $shipment->id) }}" target="_blank" 
               class="ml-2 text-teal-600 hover:text-teal-800 text-xs">
                <i class="fas fa-external-link-alt"></i> View HAWB
            </a>
        @endif
    </p>
</div>
                </div>
            </div>

            <!-- Sender Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-teal-600"></i>
                    Sender
                </h3>
                <div class="space-y-2 text-sm">
                    <p class="font-medium">{{ $shipment->sender_name ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $shipment->sender_address ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $shipment->sender_city ?? '' }} {{ $shipment->sender_country ?? '' }}</p>
                    @if($shipment->sender_phone)
                        <p class="text-gray-600"><i class="fas fa-phone mr-1"></i> {{ $shipment->sender_phone }}</p>
                    @endif
                </div>
            </div>

            <!-- Receiver Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-check text-teal-600"></i>
                    Receiver
                </h3>
                <div class="space-y-2 text-sm">
                    <p class="font-medium">{{ $shipment->receiver_name ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $shipment->receiver_address ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $shipment->receiver_city ?? '' }} {{ $shipment->receiver_country ?? '' }}</p>
                    @if($shipment->receiver_postal_code)
                        <p class="text-gray-600">Postal Code: {{ $shipment->receiver_postal_code }}</p>
                    @endif
                    @if($shipment->receiver_phone)
                        <p class="text-gray-600"><i class="fas fa-phone mr-1"></i> {{ $shipment->receiver_phone }}</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-cog text-teal-600"></i>
                    Actions
                </h3>
                <div class="space-y-2">
                    <button onclick="window.print()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-print"></i> Print Tracking
                    </button>
                    <button onclick="shareTracking()" class="w-full bg-teal-50 hover:bg-teal-100 text-teal-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-share-alt"></i> Share Tracking
                    </button>
                    <a href="{{ route('tracking.page') }}" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Track Another
                    </a>

<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fas fa-cog text-teal-600"></i>
        Actions
    </h3>
    <div class="space-y-2">
        @if($shipment->hawb_number)
            <a href="{{ route('hawb.international', $shipment->id) }}" target="_blank" 
               class="w-full bg-purple-50 hover:bg-purple-100 text-purple-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-file-alt"></i> View HAWB
            </a>
            <a href="{{ route('hawb.international', $shipment->id) }}" target="_blank" 
               onclick="window.open(this.href, 'hawb', 'width=800,height=600'); return false;"
               class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-print"></i> Print HAWB
            </a>
        @endif
        <button onclick="window.print()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
            <i class="fas fa-print"></i> Print Tracking
        </button>
        <button onclick="shareTracking()" class="w-full bg-teal-50 hover:bg-teal-100 text-teal-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
            <i class="fas fa-share-alt"></i> Share Tracking
        </button>
        <a href="{{ route('tracking.page') }}" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
            <i class="fas fa-search"></i> Track Another
        </a>
    </div>
</div>

                </div>
            </div>
        </div>
    </div>

<!-- Admin Actions (only for admin/staff) -->
@auth
    @if(auth()->user()->isAdmin() || auth()->user()->isStaff() || auth()->user()->isRider())
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-cog text-teal-600"></i>
            Update Status
        </h3>
        <form id="updateStatusForm" class="space-y-3">
            @csrf
            <input type="hidden" id="shipmentId" value="{{ $shipment->id }}">
            
            <div>
                <select id="statusSelect" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="pending" {{ $shipment->status == 'pending' ? 'selected' : '' }}>📋 Order Placed</option>
                    <option value="confirmed" {{ $shipment->status == 'confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                    <option value="processing" {{ $shipment->status == 'processing' ? 'selected' : '' }}>⚙️ Processing</option>
                    <option value="picked_up" {{ $shipment->status == 'picked_up' ? 'selected' : '' }}>📦 Picked Up</option>
                    <option value="in_transit" {{ $shipment->status == 'in_transit' ? 'selected' : '' }}>🚚 In Transit</option>
                    <option value="customs_clearance" {{ $shipment->status == 'customs_clearance' ? 'selected' : '' }}>🛃 Customs Clearance</option>
                    <option value="out_for_delivery" {{ $shipment->status == 'out_for_delivery' ? 'selected' : '' }}>🚚 Out for Delivery</option>
                    <option value="delivered" {{ $shipment->status == 'delivered' ? 'selected' : '' }}>✅ Delivered</option>
                    <option value="failed_delivery" {{ $shipment->status == 'failed_delivery' ? 'selected' : '' }}>❌ Delivery Failed</option>
                    <option value="returned" {{ $shipment->status == 'returned' ? 'selected' : '' }}>↩️ Returned</option>
                    <option value="cancelled" {{ $shipment->status == 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                </select>
            </div>
            
            <div>
                <input type="text" id="locationInput" placeholder="Location (e.g., Kathmandu Hub)" 
                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            
            <div>
                <textarea id="notesInput" placeholder="Additional notes..." rows="2"
                          class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
            </div>
            
            <button type="button" onclick="updateShipmentStatus()" 
                    class="w-full bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-sync mr-2"></i> Update Status
            </button>
        </form>
        
        <div id="statusMessage" class="mt-3 hidden"></div>
    </div>
    @endif
@endauth


    <!-- Trust Badges -->
    <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <i class="fas fa-shield-alt text-teal-600 text-2xl mb-2"></i>
            <p class="text-xs text-gray-600">100% Secure Tracking</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <i class="fas fa-clock text-teal-600 text-2xl mb-2"></i>
            <p class="text-xs text-gray-600">Real-time Updates</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <i class="fas fa-headset text-teal-600 text-2xl mb-2"></i>
            <p class="text-xs text-gray-600">24/7 Support</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <i class="fas fa-check-circle text-teal-600 text-2xl mb-2"></i>
            <p class="text-xs text-gray-600">Verified Delivery</p>
        </div>
    </div>
</div>

<script>
function shareTracking() {
    const trackingNumber = "{{ $shipment->tracking_number }}";
    const url = "{{ route('tracking.show', $shipment->tracking_number) }}";
    
    if (navigator.share) {
        navigator.share({
            title: 'Track Shipment',
            text: `Track your shipment ${trackingNumber}`,
            url: url
        }).catch(() => {});
    } else {
        // Fallback - copy to clipboard
        const dummy = document.createElement('input');
        document.body.appendChild(dummy);
        dummy.value = url;
        dummy.select();
        document.execCommand('copy');
        document.body.removeChild(dummy);
        alert('Tracking link copied to clipboard!');
    }
}
</script>

<!-- HAWB Print Button -->
<a href="{{ route('hawb.print', ['id' => $shipment->id, 'type' => 'international']) }}" 
   target="_blank" 
   class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
    <i class="fas fa-file-alt"></i> View & Print HAWB
</a>

@auth
    @if(auth()->user()->isAdmin() || auth()->user()->isStaff() || auth()->user()->isRider())
    <script>
    function updateShipmentStatus() {
        const shipmentId = document.getElementById('shipmentId').value;
        const status = document.getElementById('statusSelect').value;
        const location = document.getElementById('locationInput').value;
        const notes = document.getElementById('notesInput').value;
        const messageDiv = document.getElementById('statusMessage');
        
        // Disable button
        const btn = document.querySelector('#updateStatusForm button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
        
        fetch('/tracking/update-status/' + shipmentId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                status: status,
                location: location,
                description: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            messageDiv.className = 'mt-3 p-3 rounded-lg ' + (data.success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700');
            messageDiv.textContent = data.message || (data.success ? '✅ Status updated successfully!' : '❌ Failed to update status');
            messageDiv.classList.remove('hidden');
            
            if (data.success) {
                // Reload page after 2 seconds to show updated status
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }
            
            // Re-enable button
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync mr-2"></i> Update Status';
        })
        .catch(error => {
            messageDiv.className = 'mt-3 p-3 rounded-lg bg-red-100 text-red-700';
            messageDiv.textContent = '❌ Error: ' + error.message;
            messageDiv.classList.remove('hidden');
            
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync mr-2"></i> Update Status';
        });
    }
    </script>
    @endif
@endauth

@endsection