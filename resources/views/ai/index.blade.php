@extends('layouts.app')

@section('title', 'NETPACK AI Logistics Copilot & Voice Command Center')
@section('page-title', 'AI Logistics Copilot')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto pb-12">
    
    <!-- HERO HEADER WITH REAL-TIME AI AVATAR & CLIENT GREETING -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-teal-950 to-slate-900 border border-teal-500/30 p-6 sm:p-8 text-white shadow-2xl">
        <!-- Background Grid Pattern -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#14b8a6_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        Voice & Written AI Engine &bull; Version 2.6
                    </span>
                    <span class="text-xs text-slate-400 font-medium">
                        🇳🇵 Nepal Standard Time (UTC+5:45): <span class="text-teal-200 font-mono">{{ $greetingData['nepal_time'] ?? now()->format('h:i A, l') }}</span>
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white flex items-center gap-3">
                    <span class="text-teal-400">Namaste, {{ $greetingData['client_name'] }}!</span>
                    <span class="text-2xl animate-bounce">🙏</span>
                </h1>

                <p class="text-sm text-slate-300 leading-relaxed">
                    {{ $greetingData['greeting'] }}
                </p>

                <!-- Status Pills -->
                <div class="flex items-center gap-3 pt-2 text-xs flex-wrap">
                    <div class="flex items-center gap-1.5 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-800 text-slate-300">
                        <i class="fas fa-microchip text-teal-400"></i>
                        <span>Role: <strong class="text-white">{{ $greetingData['role_title'] }}</strong></span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-800 text-slate-300">
                        <i class="fas fa-shield-halved text-emerald-400"></i>
                        <span>Security: <strong class="text-white">Dual-OTP Enabled</strong></span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-800 text-slate-300">
                        <i class="fas fa-network-wired text-blue-400"></i>
                        <span>Network: <strong class="text-white">7 Provinces & TIA Cargo Hub</strong></span>
                    </div>
                </div>
            </div>

            <!-- Interactive AI Hologram Avatar Box -->
            <div class="lg:w-80 flex-shrink-0 bg-slate-900/80 rounded-2xl border border-teal-500/40 p-5 backdrop-blur-md shadow-xl flex flex-col items-center text-center">
                <div class="relative w-24 h-24 mb-3">
                    <!-- Pulsing Glow Ring -->
                    <div class="absolute inset-0 rounded-full bg-teal-400/20 blur-xl animate-pulse"></div>
                    <div class="relative w-full h-full rounded-full bg-gradient-to-tr from-teal-600 via-emerald-500 to-cyan-400 p-1 flex items-center justify-center shadow-lg">
                        <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center text-4xl">
                            <span>🤖</span>
                        </div>
                    </div>
                    <span class="absolute bottom-1 right-1 w-5 h-5 bg-emerald-500 border-2 border-slate-950 rounded-full flex items-center justify-center text-[10px] text-white">
                        <i class="fas fa-check"></i>
                    </span>
                </div>

                <h3 class="text-sm font-bold text-white">NETPACK AI Copilot</h3>
                <p class="text-xs text-teal-300 font-medium">Logistics & Door-to-Door Voice Expert</p>

                <!-- Voice Equalizer Animation Preview -->
                <div class="flex items-center gap-1 my-3 h-5">
                    <span class="w-1 h-3 bg-teal-400 rounded-full animate-pulse"></span>
                    <span class="w-1 h-5 bg-emerald-400 rounded-full animate-bounce"></span>
                    <span class="w-1 h-2 bg-teal-300 rounded-full animate-pulse"></span>
                    <span class="w-1 h-4 bg-teal-500 rounded-full animate-bounce"></span>
                    <span class="w-1 h-3 bg-cyan-400 rounded-full animate-pulse"></span>
                </div>

                <button onclick="document.querySelector('#netpack-ai-copilot button[aria-label=\'Open AI Logistics Copilot\']')?.click()"
                        class="w-full py-2 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-comments"></i>
                    <span>Launch Voice Assistant</span>
                </button>
            </div>
        </div>
    </div>

    <!-- PROACTIVE UPCOMING OCCASIONS & FESTIVAL LOGISTICS SCHEDULES -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <span>🗓️ Important Occasions, Festivals & Logistics Cutoffs</span>
                </h2>
                <p class="text-xs text-slate-500">
                    Proactive scheduling advisory for Nepal national festivals, air cargo congestion windows, and highway linehauls.
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                Peak Shipping Advisory
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($occasions as $occ)
                @php
                    $isCritical = $occ['impact_level'] === 'CRITICAL_PEAK';
                    $isHigh = $occ['impact_level'] === 'HIGH_PEAK';
                    $isIntl = $occ['impact_level'] === 'INTERNATIONAL_PEAK';
                    
                    $borderClass = $isCritical ? 'border-rose-400 bg-rose-50/20' : ($isHigh ? 'border-amber-400 bg-amber-50/20' : ($isIntl ? 'border-blue-400 bg-blue-50/20' : 'border-slate-200 bg-white'));
                    $badgeClass = $isCritical ? 'bg-rose-100 text-rose-800 border-rose-200' : ($isHigh ? 'bg-amber-100 text-amber-800 border-amber-200' : ($isIntl ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200'));
                @endphp
                <div class="rounded-2xl border {{ $borderClass }} p-5 shadow-xs transition hover:shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $badgeClass }}">
                                {{ str_replace('_', ' ', $occ['impact_level']) }}
                            </span>
                            <span class="text-xs font-medium text-slate-500">
                                <i class="far fa-calendar-alt text-teal-600"></i> {{ $occ['approx_month'] }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900">{{ $occ['name'] }}</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $occ['description'] }}</p>

                        <div class="mt-3.5 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-700 space-y-1">
                            <div class="font-bold text-teal-800 flex items-center gap-1.5">
                                <i class="fas fa-bullhorn text-teal-600"></i>
                                <span>Logistics Advisory:</span>
                            </div>
                            <p class="leading-relaxed">{{ $occ['logistical_notice'] }}</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">
                            Cutoff: <strong class="text-slate-800">{{ $occ['cutoff_days_prior'] }} days before peak</strong>
                        </span>
                        <a href="{{ route('shipments.create') }}" class="font-bold text-teal-700 hover:text-teal-800 flex items-center gap-1">
                            <span>Book In Advance</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- DOOR-TO-DOOR DELIVERY & DUAL-OTP FLOW SIMULATOR -->
    <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="max-w-2xl">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200 uppercase tracking-wider">
                Door-to-Door Architecture
            </span>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-1.5">
                Dual-OTP Cryptographic Custody Transfer & COD Segregation
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                How COURIER with NETPACK protects every parcel handover between Seller, Delivery Rider, and Customer Doorstep.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Step 1 -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 relative group hover:border-teal-500 transition">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white font-bold flex items-center justify-center mb-3 shadow-sm">
                    1
                </div>
                <h4 class="text-sm font-bold text-slate-900">Seller Books Direct Delivery</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Seller enters parcel weight, dimensions, and COD cash amount. The system calculates an instant quote and generates a secret <strong>6-digit Pickup OTP</strong>.
                </p>
                <div class="mt-3 inline-block px-2 py-1 rounded bg-teal-100 text-teal-800 font-mono text-xs font-bold">
                    Pickup OTP: ******
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 relative group hover:border-teal-500 transition">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white font-bold flex items-center justify-center mb-3 shadow-sm">
                    2
                </div>
                <h4 class="text-sm font-bold text-slate-900">First-Mile Rider Pickup</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Verified rider arrives at Seller's door. The Seller discloses the Pickup OTP. Rider inputs the code into the mobile app to accept physical parcel custody.
                </p>
                <div class="mt-3 inline-block px-2 py-1 rounded bg-blue-100 text-blue-800 font-mono text-xs font-bold">
                    Status: Custody In Transit
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 relative group hover:border-teal-500 transition">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white font-bold flex items-center justify-center mb-3 shadow-sm">
                    3
                </div>
                <h4 class="text-sm font-bold text-slate-900">Customer Doorstep Handover</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Recipient customer receives a private <strong>6-digit Delivery OTP</strong>. When rider arrives, customer pays COD cash and shares the Delivery OTP.
                </p>
                <div class="mt-3 inline-block px-2 py-1 rounded bg-amber-100 text-amber-800 font-mono text-xs font-bold">
                    Customer Delivery OTP: ******
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 relative group hover:border-teal-500 transition">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center mb-3 shadow-sm">
                    4
                </div>
                <h4 class="text-sm font-bold text-slate-900">Instant POD & Segregated Ledger</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Rider snaps geotagged POD photo. System validates OTP, marks DELIVERED, and segregates COD cash in hand from rider delivery earnings.
                </p>
                <div class="mt-3 inline-block px-2 py-1 rounded bg-emerald-100 text-emerald-800 font-mono text-xs font-bold">
                    Status: DELIVERED & Verified
                </div>
            </div>
        </div>
    </div>

    <!-- DAILY OPERATIONAL SCHEDULES & CUTOFF MATRICES -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Daily Cutoffs -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-clock text-teal-600"></i>
                <span>Daily Logistics Cutoff Times (Nepal Standard Time)</span>
            </h3>

            <div class="divide-y divide-slate-100 text-xs">
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block text-xs">Same-Day Intra-City Booking Cutoff</strong>
                        <span class="text-slate-500 text-[11px]">Kathmandu, Lalitpur, Bhaktapur door-to-door</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 font-bold border border-teal-200">
                        {{ $schedules['same_day_cutoff'] ?? '12:00' }} PM Daily
                    </span>
                </div>

                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block text-xs">Express Flash Delivery Window</strong>
                        <span class="text-slate-500 text-[11px]">Direct intra-city rider point-to-point</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 font-bold border border-blue-200">
                        {{ $schedules['express_intacity_hours'] ?? '2-4 hours' }}
                    </span>
                </div>

                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block text-xs">TIA Cargo Terminal Intake Cutoff</strong>
                        <span class="text-slate-500 text-[11px]">Tribhuvan International Airport customs export</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 font-bold border border-amber-200">
                        {{ $schedules['tia_cargo_intake_cutoff'] ?? '15:00' }} PM Daily
                    </span>
                </div>

                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block text-xs">Night Highway Linehaul Departures</strong>
                        <span class="text-slate-500 text-[11px]">Inter-city road express to Pokhara, Biratnagar, Butwal</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-800 font-bold border border-purple-200">
                        {{ $schedules['night_linehaul_departure'] ?? '19:00' }} PM Nightly
                    </span>
                </div>
            </div>
        </div>

        <!-- Tiered COD Limits & Segregated Financials -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-hand-holding-dollar text-emerald-600"></i>
                <span>Tiered COD Cash Limits & Segregation</span>
            </h3>

            <div class="space-y-2.5 text-xs">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block">Level 1 &middot; New Verified Rider</strong>
                        <span class="text-slate-500 text-[11px]">Initial authorization upon KYC bluebook validation</span>
                    </div>
                    <span class="font-mono font-bold text-slate-900">Rs. 5,000 Limit</span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block">Level 2 &middot; Proven Courier Rider</strong>
                        <span class="text-slate-500 text-[11px]">Completed 50+ successful handovers with zero loss</span>
                    </div>
                    <span class="font-mono font-bold text-slate-900">Rs. 20,000 Limit</span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block">Level 3 &middot; High-Volume Veteran</strong>
                        <span class="text-slate-500 text-[11px]">Senior fleet rider with stellar customer rating</span>
                    </div>
                    <span class="font-mono font-bold text-slate-900">Rs. 50,000 Limit</span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <strong class="text-slate-800 block">Level 4 &middot; Enterprise Authorized</strong>
                        <span class="text-slate-500 text-[11px]">Approved bank guarantee or agency contract</span>
                    </div>
                    <span class="font-mono font-bold text-teal-700">Custom Authorized</span>
                </div>
            </div>
        </div>
    </div>

    <!-- GLOBAL PROFESSIONAL AI TOOLS INTEGRATION SHOWCASE -->
    <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-950 to-slate-900 border border-slate-800 p-6 sm:p-8 text-white shadow-xl space-y-6">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 uppercase tracking-wider">
                Global Sourced AI Architecture
            </span>
            <h2 class="text-2xl font-black text-white tracking-tight mt-2">
                Multi-Modal Enterprise AI Stack
            </h2>
            <p class="text-xs text-slate-400 mt-1 max-w-2xl">
                COURIER with NETPACK is engineered to seamlessly integrate with global best-in-class AI infrastructure while maintaining 100% offline autonomous readiness.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <!-- OpenAI -->
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-teal-300">OpenAI GPT-4o / Realtime</span>
                    <i class="fas fa-brain text-teal-400"></i>
                </div>
                <p class="text-slate-400 text-[11px]">
                    Natural reasoning, tool calling, and live voice audio synthesis with sub-second latency.
                </p>
                <span class="inline-block text-[10px] text-emerald-400 font-mono">
                    <i class="fas fa-circle text-[7px] mr-1"></i> Ready (.env configured)
                </span>
            </div>

            <!-- Web Speech API -->
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-teal-300">Web Speech API (STT & TTS)</span>
                    <i class="fas fa-microphone-lines text-emerald-400"></i>
                </div>
                <p class="text-slate-400 text-[11px]">
                    Zero-latency client-side speech recognition and synthesis natively running in all browsers.
                </p>
                <span class="inline-block text-[10px] text-emerald-400 font-mono">
                    <i class="fas fa-check-circle text-[8px] mr-1"></i> 100% Active & Free
                </span>
            </div>

            <!-- Google Gemini -->
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-teal-300">Google Gemini 2.0 / 2.5</span>
                    <i class="fas fa-bolt text-amber-400"></i>
                </div>
                <p class="text-slate-400 text-[11px]">
                    High-speed token throughput for large customs manifests, invoices, and HAWB generation.
                </p>
                <span class="inline-block text-[10px] text-teal-300 font-mono">
                    <i class="fas fa-plug text-[8px] mr-1"></i> Multi-Provider Adapter
                </span>
            </div>

            <!-- ElevenLabs Voice -->
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-teal-300">ElevenLabs Neural Voice</span>
                    <i class="fas fa-waveform text-purple-400"></i>
                </div>
                <p class="text-slate-400 text-[11px]">
                    Studio-grade human inflection and empathetic voice synthesis for automated dispatcher hotlines.
                </p>
                <span class="inline-block text-[10px] text-teal-300 font-mono">
                    <i class="fas fa-sliders text-[8px] mr-1"></i> Configurable
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
