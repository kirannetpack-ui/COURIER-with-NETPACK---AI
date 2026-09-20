@extends('layouts.app')

@section('title', 'Shipment Details - ' . ($shipment->tracking_number ?? 'N/A'))

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-600 to-blue-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-box text-2xl"></i>
                    <h1 class="text-2xl font-bold">Shipment Details</h1>
                </div>
                <div class="mt-2 font-mono text-lg bg-white/20 px-4 py-2 rounded-lg inline-block">
                    {{ $shipment->formatted_tracking_number ?? $shipment->tracking_number }}
                </div>
            </div>
            <div class="mt-3 md:mt-0 flex gap-2">
                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'staff'], true))
                <a href="{{ route('admin.shipments.tracking', $shipment->id) }}" 
                   class="bg-indigo-700 hover:bg-indigo-800 text-white px-4 py-2 rounded-lg transition flex items-center gap-2 font-bold shadow-sm">
                    <i class="fas fa-satellite-dish"></i> Tracking & MAWB Console
                </a>
                <button onclick="openTrackingModal('{{ $shipment->id }}', '{{ $shipment->tracking_number }}')" 
                        class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Update Tracking
                </button>
                @endif
                <a href="{{ route('admin.shipments.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-{{ $shipment->tracking_status['color'] ?? 'gray' }}-100 flex items-center justify-center">
                    <i class="fas {{ $shipment->tracking_status['icon'] ?? 'fa-clock' }} text-{{ $shipment->tracking_status['color'] ?? 'gray' }}-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Status</p>
                    <p class="font-semibold text-gray-800">{{ $shipment->tracking_status['label'] ?? ucfirst($shipment->status) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-truck text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Service</p>
                    <p class="font-semibold text-gray-800">{{ ucfirst($shipment->service_type ?? 'Standard') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
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
        <div class="bg-white rounded-xl shadow-sm p-4">
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
                        <p class="text-xs text-gray-500">HAWB Number</p>
                        <p class="font-mono text-sm">{{ $shipment->hawb_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Service Type</p>
                        <p class="text-sm font-medium">{{ ucfirst($shipment->service_type ?? 'Standard') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Shipment Type</p>
                        <p class="text-sm font-medium">{{ ucfirst($shipment->shipment_type ?? 'Parcel') }}</p>
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
                        <p class="text-xs text-gray-500">Payment Status</p>
                        <p class="text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $shipment->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($shipment->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($shipment->payment_status ?? 'Pending') }}
                            </span>
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
                        <i class="fas fa-print"></i> Print Details
                    </button>
                    <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank" class="w-full bg-teal-50 hover:bg-teal-100 text-teal-700 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-external-link-alt"></i> View Public Tracking
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

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
</style>
@include('partials.tracking-update-modal')
@endsection
