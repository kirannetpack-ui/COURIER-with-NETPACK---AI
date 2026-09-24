@extends('layouts.app')

@section('title', 'Super Admin Operations Dashboard - COURIER with NETPACK')
@section('page-title', 'Operations Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Pending Users Approval Alert -->
    @php
        $pendingCount = \App\Models\User::where('verification_status', 'pending')->count();
    @endphp

    @if($pendingCount > 0)
        <div class="bg-amber-50 border border-amber-300 text-amber-900 px-5 py-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-sm flex-shrink-0">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold">Pending Registrations Require Review</h3>
                    <p class="text-xs text-amber-700"><strong>{{ $pendingCount }}</strong> client/partner account(s) are awaiting administrative approval.</p>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}?status=pending" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition shadow-xs text-center flex-shrink-0">
                Review Accounts &rarr;
            </a>
        </div>
    @endif

    <!-- 3 Core Logistics Network Command Center (Monitoring & Rate Feeding) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Service 1: International Air Freight -->
        <a href="{{ route('admin.international-rates.index') }}" 
           class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-5 text-white shadow-md border border-indigo-500/30 hover:border-indigo-400 hover:shadow-indigo-500/20 hover:shadow-lg transition-all duration-300">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-indigo-500/10 rounded-full blur-xl pointer-events-none group-hover:scale-150 transition duration-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-indigo-500/30 text-indigo-200 border border-indigo-400/40 font-mono">
                        <i class="fas fa-plane-departure text-[9px]"></i> Rate Feeding & Tariffs
                    </span>
                    <h3 class="text-base font-black text-white mt-2 group-hover:text-indigo-200 transition flex items-center gap-2">
                        <span>International Tariffs</span>
                        <i class="fas fa-arrow-right text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition"></i>
                    </h3>
                    <p class="text-xs text-slate-300 mt-1">Feed 0.5kg slab matrices, dynamic tariff surcharges, and oversee global air cargo flows.</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-indigo-500 group-hover:text-white transition">
                    <i class="fas fa-table-cells"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-indigo-800/50 flex items-center justify-between text-[11px] text-indigo-300 font-medium">
                <span>Tariff Matrices &bull; Packaging Rules</span>
                <span class="font-bold flex items-center gap-1 text-white">Feed Rates &rarr;</span>
            </div>
        </a>

        <!-- Service 2: Nepal Domestic Logistics Oversight -->
        <a href="{{ route('admin.shipments.index', ['shipment_type' => 'domestic']) }}" 
           class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 p-5 text-white shadow-md border border-teal-500/30 hover:border-teal-400 hover:shadow-teal-500/20 hover:shadow-lg transition-all duration-300">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-teal-500/10 rounded-full blur-xl pointer-events-none group-hover:scale-150 transition duration-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-teal-500/30 text-teal-200 border border-teal-400/40 font-mono">
                        <i class="fas fa-mountain-sun text-[9px]"></i> Ground Oversight
                    </span>
                    <h3 class="text-base font-black text-white mt-2 group-hover:text-teal-200 transition flex items-center gap-2">
                        <span>Domestic Logistics Flow</span>
                        <i class="fas fa-arrow-right text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition"></i>
                    </h3>
                    <p class="text-xs text-slate-300 mt-1">Monitor Nepal-wide inter-district corridors, manifest volumes, and delivery SLA compliance.</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-500/20 border border-teal-500/40 text-teal-300 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-teal-500 group-hover:text-white transition">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-teal-800/50 flex items-center justify-between text-[11px] text-teal-300 font-medium">
                <span>7 Provinces &bull; Handled by Domestic Admin</span>
                <span class="font-bold flex items-center gap-1 text-white">Monitor Registry &rarr;</span>
            </div>
        </a>

        <!-- Service 3: E-Commerce & Rider Fleet -->
        <a href="{{ route('admin.riders.dashboard') }}" 
           class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-amber-950 to-slate-900 p-5 text-white shadow-md border border-amber-500/30 hover:border-amber-400 hover:shadow-amber-500/20 hover:shadow-lg transition-all duration-300">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-amber-500/10 rounded-full blur-xl pointer-events-none group-hover:scale-150 transition duration-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500/30 text-amber-200 border border-amber-400/40 font-mono">
                        <i class="fas fa-motorcycle text-[9px]"></i> Fleet Telemetry
                    </span>
                    <h3 class="text-base font-black text-white mt-2 group-hover:text-amber-200 transition flex items-center gap-2">
                        <span>Rider Fleet & COD Radar</span>
                        <i class="fas fa-arrow-right text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition"></i>
                    </h3>
                    <p class="text-xs text-slate-300 mt-1">Live GPS fleet movement, rider deposit balances, and COD settlement integrity.</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-amber-500 group-hover:text-white transition">
                    <i class="fas fa-satellite-dish"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-amber-800/50 flex items-center justify-between text-[11px] text-amber-300 font-medium">
                <span>Rider GPS Radar &bull; COD Audit</span>
                <span class="font-bold flex items-center gap-1 text-white">Live Fleet Console &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Operational KPI Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 hover:border-teal-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Consignments</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format(\App\Models\Shipment::count()) }}</p>
                    <p class="text-[11px] text-teal-600 font-medium mt-1"><i class="fas fa-arrow-trend-up"></i> Master Registry</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-box-archive"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 hover:border-blue-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active In-Transit</p>
                    <p class="text-2xl sm:text-3xl font-black text-blue-600 mt-1">{{ number_format(\App\Models\Shipment::whereIn('status', ['in_transit', 'out_for_delivery', 'picked_up'])->count()) }}</p>
                    <p class="text-[11px] text-blue-600 font-medium mt-1"><i class="fas fa-route"></i> Moving On Ground</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-truck-fast"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Successfully Delivered</p>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1">{{ number_format(\App\Models\Shipment::where('status', 'delivered')->count()) }}</p>
                    <p class="text-[11px] text-emerald-600 font-medium mt-1"><i class="fas fa-circle-check"></i> Completed Cycles</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 hover:border-amber-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Registered Network</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format(\App\Models\User::count()) }}</p>
                    <p class="text-[11px] text-amber-600 font-medium mt-1"><i class="fas fa-users"></i> Clients, Partners, Riders</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="fas fa-network-wired"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- AI OPERATIONAL INTELLIGENCE & WIN-WIN-WIN RESOLUTION HUB -->
    <!-- ============================================================= -->
    <div x-data="adminAiHub()" x-init="initHub()" class="bg-white rounded-3xl shadow-lg border border-slate-200/90 overflow-hidden transition-all duration-300">
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-950 via-teal-950 to-slate-900 px-6 py-5 text-white flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-teal-800/40">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-400 p-0.5 shadow-md flex-shrink-0">
                    <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-xl text-teal-300">
                        <i class="fas fa-brain-circuit" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin text-teal-400" x-show="loading" style="display: none;"></i>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base font-black tracking-tight text-white flex items-center gap-2">
                            <span>AI Operational Intelligence & Win-Win-Win Hub</span>
                        </h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30 flex items-center gap-1.5 font-mono">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Diagnostics
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center gap-1">
                            <span>🇳🇵</span> Nepalese Orientation
                        </span>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">
                        Proactive bottleneck detector across all 7 provinces & TIA air cargo gateway. Recommends verified tripartite solutions for Client, Operations, and NETPACK.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap self-end md:self-center flex-shrink-0">
                <!-- Voice Briefing Button -->
                <button type="button" 
                        @click="toggleVoiceBriefing()"
                        :class="speechPlaying ? 'bg-rose-500 hover:bg-rose-600 text-white animate-pulse' : 'bg-teal-500/20 hover:bg-teal-500/30 text-teal-200 border border-teal-500/40'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                    <i class="fas" :class="speechPlaying ? 'fa-stop' : 'fa-volume-high'"></i>
                    <span x-text="speechPlaying ? 'Stop Briefing' : 'Voice Briefing (Nepali Cadence)'"></span>
                </button>

                <!-- Refresh Button -->
                <button type="button" 
                        @click="fetchOperationalIntelligence()"
                        :disabled="loading"
                        title="Re-scan network exceptions"
                        class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition border border-slate-700/60 disabled:opacity-50">
                    <i class="fas fa-arrows-rotate" :class="{ 'fa-spin': loading }"></i>
                </button>
            </div>
        </div>

        <!-- Metric Badges & Filter Tabs -->
        <div class="px-6 py-3.5 bg-slate-50 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                <button type="button" 
                        @click="selectedFilter = 'all'"
                        :class="selectedFilter === 'all' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 flex-shrink-0">
                    <span>All Issues</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono" :class="selectedFilter === 'all' ? 'bg-slate-800 text-slate-200' : 'bg-slate-100 text-slate-700'" x-text="issues.length">0</span>
                </button>
                <button type="button" 
                        @click="selectedFilter = 'critical'"
                        :class="selectedFilter === 'critical' ? 'bg-rose-600 text-white font-bold shadow-xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 flex-shrink-0">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Critical Holds</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono" :class="selectedFilter === 'critical' ? 'bg-rose-700 text-white' : 'bg-rose-100 text-rose-800'" x-text="criticalCount">0</span>
                </button>
                <button type="button" 
                        @click="selectedFilter = 'warning'"
                        :class="selectedFilter === 'warning' ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 flex-shrink-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Operational Attention</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono" :class="selectedFilter === 'warning' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800'" x-text="warningCount">0</span>
                </button>
            </div>

            <div class="flex items-center gap-2 text-slate-500 text-[11px]">
                <i class="fas fa-handshake-angle text-teal-600"></i>
                <span>Every solution guarantees a <strong>Win for Client</strong>, <strong>Win for Operations</strong>, and <strong>Win for NETPACK</strong>.</span>
            </div>
        </div>

        <!-- Issues Cards Container -->
        <div class="p-6">
            <template x-if="loading && issues.length === 0">
                <div class="py-12 text-center text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl text-teal-600 mb-2"></i>
                    <p class="text-xs font-semibold">Scanning 7 provinces & air cargo gateways for operational bottlenecks...</p>
                </div>
            </template>

            <template x-if="!loading && filteredIssues.length === 0">
                <div class="p-8 rounded-2xl bg-emerald-50/60 border border-emerald-200 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h3 class="text-sm font-black text-emerald-950">Logistics Corridors Operating at Peak Health</h3>
                    <p class="text-xs text-emerald-800 mt-1 max-w-md mx-auto">
                        No active bottlenecks matching current filters. All domestic highway linehauls and TIA export consignments are moving on SLA.
                    </p>
                </div>
            </template>

            <div class="space-y-4" x-show="filteredIssues.length > 0">
                <template x-for="(iss, idx) in filteredIssues" :key="iss.id">
                    <div class="p-5 rounded-2xl border transition-all duration-300"
                         :class="{
                             'bg-rose-50/40 border-rose-200/90 shadow-xs hover:border-rose-300': iss.severity === 'critical' && !isResolved(iss.id),
                             'bg-amber-50/40 border-amber-200/90 shadow-xs hover:border-amber-300': iss.severity === 'warning' && !isResolved(iss.id),
                             'bg-emerald-50/40 border-emerald-200/90 opacity-90': isResolved(iss.id)
                         }">
                        
                        <!-- Top Row: Badges, Title, Action Route -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b"
                             :class="isResolved(iss.id) ? 'border-emerald-200' : 'border-slate-200/70'">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                      :class="{
                                          'bg-rose-600 text-white': iss.severity === 'critical' && !isResolved(iss.id),
                                          'bg-amber-600 text-white': iss.severity === 'warning' && !isResolved(iss.id),
                                          'bg-emerald-600 text-white': isResolved(iss.id)
                                      }"
                                      x-text="isResolved(iss.id) ? 'RESOLVED' : (iss.severity === 'critical' ? 'CRITICAL EXCEPTION' : 'ATTENTION')">
                                </span>

                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-slate-700 border border-slate-200 font-mono" x-text="iss.category">
                                </span>

                                <template x-if="iss.affected_entity">
                                    <span class="text-xs font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200" x-text="iss.affected_entity"></span>
                                </template>
                            </div>

                            <template x-if="iss.action_route">
                                <a :href="iss.action_route" class="text-xs font-bold text-teal-700 hover:text-teal-900 flex items-center gap-1 self-start sm:self-auto">
                                    <span x-text="iss.action_label || 'View Entity'"></span>
                                    <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            </template>
                        </div>

                        <!-- Title & Root Cause -->
                        <div class="mt-3">
                            <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2" x-text="iss.title"></h4>
                            <p class="text-xs text-slate-600 mt-1 flex items-start gap-1.5">
                                <strong class="text-slate-800 font-semibold flex-shrink-0">Root Cause:</strong>
                                <span x-text="iss.root_cause"></span>
                            </p>
                        </div>

                        <!-- TRI-PARTITE WIN-WIN-WIN RESOLUTION MATRIX -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Win 1: Client -->
                            <div class="p-3.5 rounded-xl bg-white border border-emerald-200/90 shadow-2xs">
                                <div class="flex items-center gap-1.5 text-xs font-black text-emerald-800 uppercase tracking-wide mb-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>🟢 Win for Client</span>
                                </div>
                                <p class="text-[11px] text-slate-700 leading-relaxed" x-text="iss.win_win_win ? iss.win_win_win.client : ''"></p>
                            </div>

                            <!-- Win 2: Operations / Partners -->
                            <div class="p-3.5 rounded-xl bg-white border border-blue-200/90 shadow-2xs">
                                <div class="flex items-center gap-1.5 text-xs font-black text-blue-800 uppercase tracking-wide mb-1">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span>🔵 Win for Operations</span>
                                </div>
                                <p class="text-[11px] text-slate-700 leading-relaxed" x-text="iss.win_win_win ? iss.win_win_win.operations : ''"></p>
                            </div>

                            <!-- Win 3: NETPACK Company -->
                            <div class="p-3.5 rounded-xl bg-white border border-purple-200/90 shadow-2xs">
                                <div class="flex items-center gap-1.5 text-xs font-black text-purple-800 uppercase tracking-wide mb-1">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    <span>🟣 Win for NETPACK</span>
                                </div>
                                <p class="text-[11px] text-slate-700 leading-relaxed" x-text="iss.win_win_win ? iss.win_win_win.company : ''"></p>
                            </div>
                        </div>

                        <!-- Action Bar -->
                        <div class="mt-4 pt-3 border-t border-slate-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="text-xs text-slate-700 flex items-center gap-2">
                                <span class="px-1.5 py-0.5 rounded bg-teal-100 text-teal-900 font-bold font-mono text-[10px]">AI Action</span>
                                <span x-text="iss.recommended_action"></span>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-auto flex-shrink-0">
                                <template x-if="!isResolved(iss.id)">
                                    <button type="button" 
                                            @click="executeWinWin(iss)"
                                            :disabled="executingId === iss.id"
                                            class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white text-xs font-black tracking-wide shadow-sm hover:shadow-teal-500/20 transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                        <i class="fas fa-bolt" x-show="executingId !== iss.id"></i>
                                        <i class="fas fa-spinner fa-spin" x-show="executingId === iss.id" style="display: none;"></i>
                                        <span x-text="executingId === iss.id ? 'Applying Win-Win...' : '⚡ Apply Win-Win Protocol'"></span>
                                    </button>
                                </template>

                                <template x-if="isResolved(iss.id)">
                                    <div class="px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center gap-1.5">
                                        <i class="fas fa-circle-check text-emerald-600"></i>
                                        <span>Tripartite Protocol Executed</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- ACTIVE SHIPMENTS NETWORK TRACKING RADAR (ALWAYS VISIBLE) -->
    <!-- ============================================================= -->

    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-black uppercase tracking-wider text-white">Active Consignment Network Tracking Radar</h2>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            LIVE FLEET & HUBS
                        </span>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">Live tracking of active shipments in-transit across domestic and international corridors.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                    {{ $activeShipments->count() }} In-Transit
                </span>
                <a href="{{ route('tracking.page') }}" target="_blank" 
                   class="px-3.5 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/10">
                    <i class="fas fa-search-location text-teal-400"></i>
                    <span>Master Radar &rarr;</span>
                </a>
            </div>
        </div>

        @if($activeShipments->isNotEmpty())
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($activeShipments as $s)
                        @php
                            $st = strtolower($s->status ?? 'pending');
                            $statusBadgeClass = match($st) {
                                'in_transit' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'out_for_delivery' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'picked_up' => 'bg-teal-100 text-teal-800 border-teal-200',
                                'manifested', 'created', 'confirmed' => 'bg-purple-100 text-purple-800 border-purple-200',
                                default => 'bg-slate-100 text-slate-800 border-slate-200',
                            };
                        @endphp
                        <a href="{{ route('tracking.show', $s->tracking_number) }}" 
                           class="block p-4 rounded-xl border border-slate-200/90 hover:border-teal-500 hover:shadow-md hover:bg-teal-50/20 transition group">
                            <div class="flex items-start justify-between gap-3 pb-2.5 border-b border-slate-100">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono font-black text-sm text-slate-900 group-hover:text-teal-700 flex items-center gap-1.5">
                                            <i class="fas fa-barcode text-teal-600 text-xs"></i>
                                            {{ $s->tracking_number }}
                                        </span>
                                        @if($s->hawb_number)
                                            <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 text-[10px] font-mono">
                                                HAWB: {{ $s->hawb_number }}
                                            </span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                                            {{ ucwords(str_replace('_', ' ', $s->service_type ?? 'Standard')) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1 flex items-center gap-1.5 font-medium truncate">
                                        <span>{{ $s->origin ?? 'Kathmandu' }}</span>
                                        <i class="fas fa-arrow-right-long text-teal-600 text-[10px]"></i>
                                        <span class="font-bold text-slate-900">{{ $s->destination ?? 'Destination' }}</span>
                                    </p>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusBadgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                                        {{ str_replace('_', ' ', $s->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="pt-2.5 flex items-center justify-between text-xs">
                                <div class="space-y-0.5">
                                    <p class="text-[11px] text-slate-500">
                                        <i class="fas fa-location-dot text-teal-600 mr-1"></i>
                                        <span class="font-semibold text-slate-700">Hub / Telemetry:</span> {{ $s->current_location ?? 'Corridor In-Transit' }}
                                    </p>
                                    <p class="text-[10px] text-slate-400">
                                        Sender: <strong class="text-slate-600">{{ $s->sender_name ?? 'Client' }}</strong> &bull; Consignee: <strong class="text-slate-600">{{ $s->receiver_name ?? 'Receiver' }}</strong>
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-[11px] font-bold text-teal-600 group-hover:text-teal-700 flex items-center gap-1">
                                        <span>Full Tracking</span>
                                        <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $s->updated_at ? $s->updated_at->diffForHumans() : '' }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="p-8 text-center space-y-2">
                <i class="fas fa-satellite text-3xl text-slate-300 block mb-2"></i>
                <h3 class="text-sm font-bold text-slate-800">No Active Moving Consignments</h3>
                <p class="text-xs text-slate-500">All registered consignments are either delivered or pending initial pickup dispatch.</p>
            </div>
        @endif
    </div>

    <!-- ============================================================= -->
    <!-- FORM WORKBENCH: OPERATIONAL INTERACTIVE FORMS EMBEDDED IN PAGE -->
    <!-- ============================================================= -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden" x-data="{ activeTab: 'dispatch' }">
        <!-- Tab Navigation Bar -->
        <div class="border-b border-slate-200 bg-slate-50/60 px-5 pt-3 flex flex-wrap gap-2">
            <button type="button" 
                    @click="activeTab = 'dispatch'" 
                    :class="activeTab === 'dispatch' ? 'border-teal-600 text-teal-800 bg-white font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-white/50 font-medium'"
                    class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition flex items-center gap-2">
                <i class="fas fa-paper-plane text-teal-600"></i>
                <span>Quick Consignment Booking Form</span>
            </button>

            <button type="button" 
                    @click="activeTab = 'telemetry'" 
                    :class="activeTab === 'telemetry' ? 'border-teal-600 text-teal-800 bg-white font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-white/50 font-medium'"
                    class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition flex items-center gap-2">
                <i class="fas fa-satellite-dish text-blue-600"></i>
                <span>Live Checkpoint & Status Telemetry</span>
            </button>

            <button type="button" 
                    @click="activeTab = 'calculator'" 
                    :class="activeTab === 'calculator' ? 'border-teal-600 text-teal-800 bg-white font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-white/50 font-medium'"
                    class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition flex items-center gap-2">
                <i class="fas fa-calculator text-amber-600"></i>
                <span>Rate & Freight Calculator</span>
            </button>

            <button type="button" 
                    @click="activeTab = 'delay'" 
                    :class="activeTab === 'delay' ? 'border-teal-600 text-teal-800 bg-white font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-white/50 font-medium'"
                    class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition flex items-center gap-2">
                <i class="fas fa-triangle-exclamation text-rose-600"></i>
                <span>Delay & Reason Logger</span>
            </button>
        </div>

        <!-- FORM 1: Express Consignment Booking & Dispatch -->
        <div x-show="activeTab === 'dispatch'" class="p-6 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Direct Consignment Manifest & Booking</h3>
                    <p class="text-xs text-slate-500">Create, validate, and issue official Air Waybill / HAWB directly from the operations desk.</p>
                </div>
                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200">
                    ⚡ Express Intake
                </span>
            </div>

            <form action="{{ route('shipments.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Sender Details -->
                    <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200/80 space-y-3">
                        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-user-arrow-up-long text-teal-600"></i> 1. Consignor / Sender
                        </p>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sender Name / Company *</label>
                            <input type="text" name="sender_name" required placeholder="Full Name or Business Name" 
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sender Phone *</label>
                            <input type="text" name="sender_phone" required placeholder="e.g. 9851000000" 
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Origin City / Hub *</label>
                            <select name="origin" required class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="Kathmandu (KTM Hub)" selected>Kathmandu (KTM Central Hub)</option>
                                <option value="Pokhara Central Hub">Pokhara Central Hub</option>
                                <option value="Biratnagar Express Hub">Biratnagar Express Hub</option>
                                <option value="Birgunj Cargo Terminal">Birgunj Cargo Terminal</option>
                                <option value="Nepalgunj Western Gateway">Nepalgunj Western Gateway</option>
                                <option value="Bhairahawa Station">Bhairahawa Station</option>
                            </select>
                        </div>
                    </div>

                    <!-- Receiver Details -->
                    <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200/80 space-y-3">
                        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-user-arrow-down-long text-blue-600"></i> 2. Consignee / Receiver
                        </p>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Receiver Name *</label>
                            <input type="text" name="receiver_name" required placeholder="Consignee Full Name" 
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Receiver Phone *</label>
                            <input type="text" name="receiver_phone" required placeholder="e.g. 9841000000" 
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Destination City & Ward *</label>
                            <input type="text" name="destination" required placeholder="e.g. Pokhara-08, Srijana Chowk or London, UK" 
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                    </div>

                    <!-- Service Level & Package Specs -->
                    <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200/80 space-y-3">
                        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-box-open text-amber-600"></i> 3. Specifications & Service
                        </p>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Service Class *</label>
                            <select name="service_type" required class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none font-medium">
                                <option value="flash">⚡ Flash Priority (2-4 hrs Kathmandu Corridor)</option>
                                <option value="standard" selected>Standard Express (1-3 Business Days)</option>
                                <option value="same_day">Same Day Inter-District Express</option>
                                <option value="himalayan">Himalayan Rugged Express (Mountain Wards)</option>
                                <option value="international">✈️ International Air Courier (Worldwide)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Weight (KG) *</label>
                                <input type="number" step="0.1" min="0.1" name="weight" value="1.0" required 
                                       class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">COD Amount (NPR)</label>
                                <input type="number" step="1" min="0" name="cod_amount" value="0" 
                                       class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Item Contents / Description *</label>
                            <input type="text" name="description" required placeholder="e.g. Legal documents, apparel, electronic accessories" 
                                   class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <p class="text-xs text-slate-500">
                        <i class="fas fa-circle-info text-teal-600 mr-1"></i> A unique tracking number and official barcode will be automatically generated upon submission.
                    </p>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        <span>Generate Consignment & HAWB</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- FORM 2: Live Checkpoint & Status Telemetry Broadcast -->
        <div x-show="activeTab === 'telemetry'" class="p-6 space-y-5" style="display: none;">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Broadcast Checkpoint & Telemetry Milestone</h3>
                    <p class="text-xs text-slate-500">Push real-time location and milestone updates to live public trackers and customer notifications.</p>
                </div>
                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                    🛰️ Radar Stream
                </span>
            </div>

            <form action="{{ route('tracking.page') }}" method="GET" class="space-y-4 max-w-2xl">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Consignment / HAWB Number *</label>
                    <div class="relative">
                        <i class="fas fa-barcode absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" name="tracking" placeholder="Enter Tracking Number (e.g. NP-DOM-98214)" required
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Milestone Status *</label>
                        <select class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                            <option value="MANIFESTED">MANIFESTED — Intake Confirmed</option>
                            <option value="PICKED_UP">PICKED UP — Collected by Rider / Courier</option>
                            <option value="IN_TRANSIT" selected>IN TRANSIT — Corridor Movement</option>
                            <option value="OUT_FOR_DELIVERY">OUT FOR DELIVERY — Assigned to Last-Mile Rider</option>
                            <option value="CUSTOMS_CLEARED">CUSTOMS CLEARED — TIA Cargo Export</option>
                            <option value="DELIVERED">DELIVERED — Consignee Signature Acquired</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Checkpoint / Hub Name *</label>
                        <input type="text" placeholder="e.g. Mugling Checkpoint, Tribhuvan Cargo Terminal" 
                               class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Checkpoint Remark</label>
                    <input type="text" placeholder="e.g. Package cleared primary sorting scan; loaded onto Pokhara Express line haul vehicle."
                           class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('tracking.update') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition inline-flex items-center gap-2">
                        <i class="fas fa-satellite"></i> Open Dedicated Telemetry Console
                    </a>
                    <a href="{{ route('hawb.scanner') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-2">
                        <i class="fas fa-qrcode"></i> Launch Optical Barcode Scanner
                    </a>
                </div>
            </form>
        </div>

        <!-- FORM 3: Instant Multi-Service Rate & Surcharge Estimator -->
        <div x-show="activeTab === 'calculator'" class="p-6 space-y-5" style="display: none;" 
             x-data="{
                mode: 'domestic',
                weight: 1.5,
                l: 20, w: 15, h: 10,
                serviceTier: 'standard',
                get volumetric() {
                    return ((this.l * this.w * this.h) / 5000).toFixed(2);
                },
                get billableWeight() {
                    return Math.max(parseFloat(this.weight || 0), parseFloat(this.volumetric || 0)).toFixed(2);
                },
                get estimatedCost() {
                    let base = this.mode === 'international' ? 2400 : 120;
                    let ratePerKg = this.mode === 'international' ? 950 : 80;
                    let tierMultiplier = this.serviceTier === 'flash' ? 1.6 : (this.serviceTier === 'himalayan' ? 1.4 : 1.0);
                    let subtotal = (base + (this.billableWeight * ratePerKg)) * tierMultiplier;
                    return Math.round(subtotal);
                }
             }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Instant Shipping Tariff & Volumetric Calculator</h3>
                    <p class="text-xs text-slate-500">Compute billable dimensional weight, zone rates, and surcharges instantly on the counter.</p>
                </div>
                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                    🧮 Tariff Engine
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Logistics Corridor</label>
                            <select x-model="mode" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="domestic">Domestic (Nepal 7 Provinces)</option>
                                <option value="international">International Air Cargo (Global)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Service Priority</label>
                            <select x-model="serviceTier" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="standard">Standard Corridor (1-3 Days)</option>
                                <option value="flash">⚡ Flash Priority (2-4 hrs Express)</option>
                                <option value="himalayan">Himalayan Rugged Terrain Route</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Actual (KG)</label>
                            <input type="number" step="0.1" min="0.1" x-model="weight" 
                                   class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Length (CM)</label>
                            <input type="number" step="1" min="1" x-model="l" 
                                   class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Width (CM)</label>
                            <input type="number" step="1" min="1" x-model="w" 
                                   class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Height (CM)</label>
                            <input type="number" step="1" min="1" x-model="h" 
                                   class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 font-mono">
                        </div>
                    </div>
                </div>

                <!-- Estimated Cost Card -->
                <div class="bg-gradient-to-br from-slate-900 to-teal-950 p-5 rounded-xl text-white flex flex-col justify-between shadow-sm">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-teal-300 font-bold">Estimated Tariff Summary</p>
                        <div class="mt-3 space-y-1 text-xs">
                            <div class="flex justify-between text-slate-300">
                                <span>Volumetric Weight:</span>
                                <span class="font-mono font-bold" x-text="volumetric + ' kg'"></span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>Billable Chargeable:</span>
                                <span class="font-mono font-bold text-teal-300" x-text="billableWeight + ' kg'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 mt-4">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Estimated Total</span>
                        <p class="text-3xl font-black text-white mt-0.5">
                            Rs. <span x-text="estimatedCost.toLocaleString()"></span>
                        </p>
                        <span class="text-[10px] text-teal-300 block mt-1">*Excluding VAT / Remote Area Surcharge</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM 4: Delay & Reason Logger -->
        <div x-show="activeTab === 'delay'" class="p-6 space-y-5" style="display: none;">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Broadcast Transit Delay Exception</h3>
                    <p class="text-xs text-slate-500">Record uncontrollable obstructions (weather, landslides, customs hold) with mandatory ETA.</p>
                </div>
                <a href="{{ route('admin.communications') }}" class="px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded text-xs font-bold hover:bg-rose-100 transition">
                    View Delay Hub &rarr;
                </a>
            </div>

            <form action="{{ route('admin.communications.send-delay') }}" method="POST" class="max-w-2xl space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tracking Number *</label>
                        <input type="text" name="tracking_number" required placeholder="e.g. NP-DOM-98214"
                               class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 font-mono outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reason Code *</label>
                        <select name="reason_code" required class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="weather">Adverse Weather (Monsoon / Fog / Heavy Snow)</option>
                            <option value="highway_blocked">Highway Landslide / Road Obstruction</option>
                            <option value="customs_hold">Customs Clearance / Document Check</option>
                            <option value="recipient_unreachable">Consignee Phone Unreachable</option>
                            <option value="address_incomplete">Incomplete Locality / Ward Verification</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Expected Delivery Reschedule</label>
                        <input type="datetime-local" name="expected_resolution" 
                               class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Channel *</label>
                        <select name="channel" required class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="all" selected>All (SMS & Email)</option>
                            <option value="email">Email Notification Only</option>
                            <option value="sms">SMS Priority Dispatch Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Delay Explanation Note</label>
                    <textarea name="custom_note" rows="2" placeholder="Detail reason (e.g. Mugling road closed due to dry landslide, re-route via Hetauda)..."
                              class="w-full text-xs border border-slate-200 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>Log Delay & Notify Parties</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- RECENT ACTIVITY & MONITORING TABLES -->
    <!-- ============================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Shipments Registry -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fas fa-truck-fast text-teal-600"></i>
                    <span>Recent Consignments</span>
                </h3>
                <a href="{{ route('admin.shipments.index') }}" class="text-xs font-semibold text-teal-700 hover:underline">
                    View All &rarr;
                </a>
            </div>

            @php
                $recentShipments = \App\Models\Shipment::orderBy('created_at', 'desc')->take(6)->get();
            @endphp

            <div class="divide-y divide-slate-100">
                @forelse($recentShipments as $s)
                    <div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition px-2 rounded-lg">
                        <div class="min-w-0">
                            <a href="{{ route('tracking.show', $s->tracking_number) }}" 
                               class="font-mono font-bold text-xs text-slate-900 hover:text-teal-700 flex items-center gap-1.5">
                                <span>{{ $s->tracking_number }}</span>
                                <i class="fas fa-arrow-up-right-from-square text-[9px] text-teal-600"></i>
                            </a>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                {{ $s->origin ?? 'Kathmandu' }} &rarr; {{ $s->destination ?? 'Destination' }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                                {{ str_replace('_', ' ', $s->status ?? 'pending') }}
                            </span>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $s->created_at ? $s->created_at->diffForHumans() : '' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No recent consignments logged.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Registered Users / Portals -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fas fa-users text-blue-600"></i>
                    <span>Recent Accounts & Approvals</span>
                </h3>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-teal-700 hover:underline">
                    Manage &rarr;
                </a>
            </div>

            @php
                $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->take(6)->get();
            @endphp

            <div class="divide-y divide-slate-100">
                @forelse($recentUsers as $u)
                    <div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition px-2 rounded-lg">
                        <div class="min-w-0">
                            <p class="font-bold text-xs text-slate-900 truncate">{{ $u->name }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ $u->email }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200">
                                {{ (in_array($u->user_type, ['customer', 'client'])) ? 'Client' : ucfirst($u->user_type) }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $u->verification_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $u->verification_status ?? 'approved' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No recent registered users found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminAiHub() {
    return {
        loading: false,
        speechPlaying: false,
        selectedFilter: 'all',
        issues: [],
        criticalCount: 0,
        warningCount: 0,
        reportSpeechText: '',
        resolvedIssueIds: [],
        executingId: null,

        get filteredIssues() {
            if (this.selectedFilter === 'critical') {
                return this.issues.filter(i => (i.severity === 'critical'));
            } else if (this.selectedFilter === 'warning') {
                return this.issues.filter(i => (i.severity === 'warning'));
            }
            return this.issues;
        },

        isResolved(id) {
            return this.resolvedIssueIds.includes(id);
        },

        initHub() {
            this.fetchOperationalIntelligence();
        },

        fetchOperationalIntelligence() {
            this.loading = true;
            fetch('/admin/ai/operational-intelligence')
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data && data.success) {
                        this.issues = data.issues || [];
                        this.criticalCount = data.critical_count || 0;
                        this.warningCount = data.warning_count || 0;
                        if (data.report && data.report.speech_text) {
                            this.reportSpeechText = data.report.speech_text;
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                    console.error('Error fetching admin operational intelligence:', err);
                });
        },

        executeWinWin(issue) {
            this.executingId = issue.id;
            fetch('/admin/ai/resolve-issue-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    issue_id: issue.id,
                    action_type: issue.action_type || 'notify_and_reroute',
                    shipment_id: issue.shipment_id || null
                })
            })
            .then(res => res.json())
            .then(data => {
                this.executingId = null;
                if (data && data.success) {
                    this.resolvedIssueIds.push(issue.id);
                    // Announce voice confirmation in polite Nepalese cadence
                    if ('speechSynthesis' in window) {
                        const utterance = new SpeechSynthesisUtterance("Win-win operational resolution applied successfully.");
                        utterance.rate = 0.94;
                        utterance.pitch = 1.04;
                        const voices = window.speechSynthesis.getVoices();
                        const nepaliVoice = voices.find(v => v.lang.startsWith('ne'));
                        const southAsianVoice = voices.find(v => (v.lang === 'en-IN' || v.lang === 'hi-IN') && (v.name.includes('India') || v.name.includes('Hindi') || v.name.includes('Heera') || v.name.includes('Ravi')));
                        if (nepaliVoice || southAsianVoice) utterance.voice = nepaliVoice || southAsianVoice;
                        window.speechSynthesis.speak(utterance);
                    }
                }
            })
            .catch(err => {
                this.executingId = null;
                console.error('Error executing win-win action:', err);
            });
        },

        toggleVoiceBriefing() {
            if (this.speechPlaying) {
                this.stopVoiceBriefing();
            } else {
                this.playVoiceBriefing();
            }
        },

        playVoiceBriefing() {
            if (!('speechSynthesis' in window)) {
                alert('Voice speech synthesis is not supported on this browser.');
                return;
            }

            const textToSpeak = this.reportSpeechText || 
                `Namaste! We currently have ${this.criticalCount} critical exceptions and ${this.warningCount} operational items requiring attention. All win-win-win protocols are ready for one click execution.`;

            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(textToSpeak);
            // Nepalese English cadence: slightly measured tempo with warm respectful pitch
            utterance.rate = 0.94;
            utterance.pitch = 1.04;

            const voices = window.speechSynthesis.getVoices();
            const nepaliVoice = voices.find(v => v.lang.startsWith('ne'));
            const southAsianVoice = voices.find(v => (v.lang === 'en-IN' || v.lang === 'hi-IN') && (v.name.includes('India') || v.name.includes('Hindi') || v.name.includes('Heera') || v.name.includes('Ravi') || v.name.includes('Neerja')));
            const naturalVoice = voices.find(v => v.lang.startsWith('en') && (v.name.includes('Natural') || v.name.includes('Google') || v.name.includes('Samantha')));

            utterance.voice = nepaliVoice || southAsianVoice || naturalVoice || null;

            utterance.onstart = () => { this.speechPlaying = true; };
            utterance.onend = () => { this.speechPlaying = false; };
            utterance.onerror = () => { this.speechPlaying = false; };

            this.speechPlaying = true;
            window.speechSynthesis.speak(utterance);
        },

        stopVoiceBriefing() {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
            this.speechPlaying = false;
        }
    };
}
</script>
@endpush

