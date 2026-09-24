<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>COURIER with NETPACK - Nepal's Premier International, Domestic & E-Commerce Courier</title>
    
    <!-- Vite Pipeline Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, .font-heading {
            font-family: 'Outfit', 'Inter', sans-serif;
        }
        .hero-mesh {
            background: linear-gradient(135deg, #0A192F 0%, #0F3952 45%, #0D9488 100%);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }
        .glow-effect {
            box-shadow: 0 10px 30px -10px rgba(13, 148, 136, 0.45);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-teal-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    🇳🇵 NEPAL EXPEDITION
                </span>
                <span>Air Cargo to USA, UK, Australia, EU & Express Door Delivery across all 7 Provinces</span>
            </div>
            <div class="flex items-center gap-4 text-[11px]">
                <span class="flex items-center gap-1.5"><i class="fas fa-headset text-teal-400"></i> +977-1-5970123</span>
                <span class="hidden md:inline">|</span>
                <span class="flex items-center gap-1.5"><i class="fas fa-shield-halved text-teal-400"></i> IATA & Customs Verified</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Brand Logo -->
                <x-logo size="lg" :href="route('home')" />

                <!-- Navigation Links -->
                <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-700">
                    <a href="#services" class="hover:text-teal-600 transition">Three Core Services</a>
                    <a href="#network" class="hover:text-teal-600 transition">Nepal & Global Network</a>
                    <a href="{{ route('tracking.page') }}" class="text-teal-700 font-bold hover:text-teal-800 transition flex items-center gap-1.5">
                        <i class="fas fa-barcode"></i> Track Shipment
                    </a>
                    <a href="{{ url('/grocery-box') }}" class="hover:text-teal-600 transition flex items-center gap-1.5">
                        <i class="fas fa-basket-shopping text-teal-600"></i> Grocery Box
                    </a>
                </nav>

                <!-- Auth & Portal Action Buttons -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-teal-600 text-white px-4 py-2.5 rounded-xl font-semibold text-sm hover:bg-teal-700 transition shadow-sm">
                            <i class="fas fa-tachometer-alt"></i> Portal Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-700 hover:text-teal-600 font-semibold text-sm px-3 py-2 transition">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-teal-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-teal-700 transition shadow-md shadow-teal-600/20">
                            <i class="fas fa-user-plus"></i> Open Account
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section with Instant Tracking Bar -->
    <section class="hero-mesh relative overflow-hidden text-white pt-16 pb-24 lg:pt-24 lg:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <!-- Left Hero Copy -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 rounded-full bg-teal-500/20 px-4 py-1.5 text-xs font-semibold text-teal-300 border border-teal-400/30">
                        <i class="fas fa-award"></i> The Most Comprehensive Courier Ecosystem in Nepal
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.1]">
                        Delivering Nepal to the <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 via-emerald-200 to-cyan-300">World</span> & Every Local Doorstep
                    </h1>
                    <p class="text-slate-300 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                        One unified powerhouse platform for <strong>International Air Cargo (HAWB)</strong>, <strong>Domestic Express Logistics across 77 districts</strong>, and <strong>Last-Mile E-Commerce with live rider GPS tracking & instant COD settlement</strong>.
                    </p>

                    <!-- Instant Track & Trace Hero Widget -->
                    <div class="glass-panel p-3 rounded-2xl glow-effect max-w-xl mx-auto lg:mx-0">
                        <form method="GET" action="{{ route('tracking.search') }}" class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-teal-300">
                                    <i class="fas fa-barcode text-lg"></i>
                                </span>
                                <input type="text" name="tracking"
                                       placeholder="Enter Tracking # (e.g. NPI-..., NPD-..., USNP-...)"
                                       class="w-full pl-10 pr-4 py-3 bg-white text-slate-900 font-mono font-semibold rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-400 uppercase tracking-wide"
                                       required>
                            </div>
                            <button type="submit"
                                    class="bg-gradient-to-r from-teal-400 to-emerald-400 text-slate-950 font-bold px-6 py-3 rounded-xl hover:from-teal-300 hover:to-emerald-300 transition flex items-center justify-center gap-2 shrink-0 shadow-md">
                                <i class="fas fa-search"></i>
                                <span>Track Package</span>
                            </button>
                        </form>
                        <div class="mt-2 text-[11px] text-teal-200/80 px-2 flex items-center justify-between">
                            <span>Supports International HAWBs, Domestic Waybills, and E-commerce Orders</span>
                            <a href="{{ route('tracking.page') }}" class="underline hover:text-white font-semibold">Advanced Tracker →</a>
                        </div>
                    </div>

                    <!-- Trust Stats Bar -->
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-white/15 max-w-lg mx-auto lg:mx-0 text-center lg:text-left">
                        <div>
                            <p class="text-2xl sm:text-3xl font-extrabold text-white font-heading">50+ Countries</p>
                            <p class="text-xs text-slate-300">Worldwide Air Freight</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-extrabold text-white font-heading">77 Districts</p>
                            <p class="text-xs text-slate-300">All 7 Nepal Provinces</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-extrabold text-white font-heading">70% Instant</p>
                            <p class="text-xs text-slate-300">Seller COD Settlement</p>
                        </div>
                    </div>
                </div>

                <!-- Right Hero Feature Card -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-white/15 pb-4">
                            <span class="text-sm font-bold text-teal-300 uppercase tracking-wider">Operational Portals</span>
                            <span class="inline-flex items-center gap-1.5 text-xs text-emerald-300 font-semibold">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span> 24/7 Live Network
                            </span>
                        </div>

                        <div class="space-y-3">
                            <!-- International Pill -->
                            <div class="flex items-center gap-4 p-3.5 rounded-2xl bg-white/10 hover:bg-white/15 transition border border-white/10">
                                <span class="h-10 w-10 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center text-lg shrink-0">
                                    <i class="fas fa-plane-departure"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold">International Cargo & HAWB</h4>
                                    <p class="text-xs text-slate-300 truncate">Air Waybills for USA, UK, EU, Australia</p>
                                </div>
                                <span class="text-xs text-teal-300 font-semibold"><i class="fas fa-chevron-right"></i></span>
                            </div>

                            <!-- Domestic Pill -->
                            <div class="flex items-center gap-4 p-3.5 rounded-2xl bg-white/10 hover:bg-white/15 transition border border-white/10">
                                <span class="h-10 w-10 rounded-xl bg-teal-500/20 text-teal-300 flex items-center justify-center text-lg shrink-0">
                                    <i class="fas fa-truck-fast"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold">Domestic Express Network</h4>
                                    <p class="text-xs text-slate-300 truncate">Flash 2-Hour, Same-Day & Himalayan Logistics</p>
                                </div>
                                <span class="text-xs text-teal-300 font-semibold"><i class="fas fa-chevron-right"></i></span>
                            </div>

                            <!-- E-Commerce Pill -->
                            <div class="flex items-center gap-4 p-3.5 rounded-2xl bg-white/10 hover:bg-white/15 transition border border-white/10">
                                <span class="h-10 w-10 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-lg shrink-0">
                                    <i class="fas fa-motorcycle"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold">E-Commerce & Rider Dispatch</h4>
                                    <p class="text-xs text-slate-300 truncate">Live GPS tracking & Cash on Delivery</p>
                                </div>
                                <span class="text-xs text-teal-300 font-semibold"><i class="fas fa-chevron-right"></i></span>
                            </div>
                        </div>

                        <div class="pt-2 flex gap-3">
                            <a href="{{ route('shipments.create') }}" class="flex-1 text-center bg-white text-slate-900 font-bold py-3 rounded-xl text-sm hover:bg-slate-100 transition shadow-sm">
                                Book Shipment
                            </a>
                            <a href="{{ route('register') }}" class="flex-1 text-center border border-white/30 text-white font-bold py-3 rounded-xl text-sm hover:bg-white/10 transition">
                                Merchant Signup
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ambient Background Decors -->
        <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-10 right-10 w-80 h-80 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>
    </section>

    <!-- The 3 Core Delivery Pillars Section -->
    <section id="services" class="py-24 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs uppercase font-bold tracking-widest text-teal-600 bg-teal-50 px-3 py-1 rounded-full">
                    Three Pillars of Excellence
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-heading">
                    Engineered for Every Scale of Delivery in Nepal
                </h2>
                <p class="text-slate-600 text-base leading-relaxed">
                    Whether you are an exporter shipping high-value handicrafts to New York, a Kathmandu retailer fulfilling express orders, or an e-commerce seller needing instant cashflow.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Pillar 1: International -->
                <div class="rounded-3xl border border-slate-200 p-8 bg-gradient-to-b from-white to-slate-50 shadow-sm hover:shadow-xl hover:border-sky-500/40 transition duration-300 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="h-14 w-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl">
                                <i class="fas fa-plane-departure"></i>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700 bg-sky-100 px-3 py-1 rounded-full">
                                Global Air Cargo
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 font-heading">
                            1. International Deliveries & HAWB
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Door-to-airport and door-to-door express courier from Nepal to USA, UK, Australia, Europe, Japan, and UAE. Featuring automated House Air Waybill (HAWB) generation, Customs export declarations, and overseas partner handoff.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-700 font-medium">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-sky-600"></i> Standardized IATA-compliant HAWBs with QR code</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-sky-600"></i> Tribhuvan International Airport (TIA) customs transit</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-sky-600"></i> Commercial invoicing, document parsing & tracking</li>
                        </ul>
                    </div>
                    <div class="pt-4 border-t border-slate-200">
                        <a href="{{ route('shipments.create') }}" class="w-full inline-flex items-center justify-center gap-2 bg-sky-600 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-sky-700 transition">
                            Book International <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Pillar 2: Domestic -->
                <div class="rounded-3xl border-2 border-teal-600 p-8 bg-gradient-to-b from-teal-50/40 to-white shadow-md hover:shadow-xl transition duration-300 flex flex-col justify-between space-y-6 relative">
                    <span class="absolute -top-3 right-6 bg-teal-600 text-white text-[10px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full shadow">
                        All 7 Provinces
                    </span>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="h-14 w-14 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center text-2xl">
                                <i class="fas fa-truck-fast"></i>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-800 bg-teal-100 px-3 py-1 rounded-full">
                                Domestic Express
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 font-heading">
                            2. Domestic Courier Network
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Connecting all 77 districts of Nepal. Choose between <strong>Flash (1–2 hr)</strong>, <strong>Same-Day</strong> within Kathmandu Valley, <strong>Standard</strong> inter-district delivery, and specialized <strong>Himalayan Cargo</strong> routes.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-700 font-medium">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-teal-600"></i> Automated Bagging, Manifesting & Hub Forwarding</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-teal-600"></i> Verified Proof of Delivery (POD) with signature upload</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-teal-600"></i> Cash on Delivery (COD) collection at destination</li>
                        </ul>
                    </div>
                    <div class="pt-4 border-t border-slate-200">
                        <a href="{{ route('domestic.pickup.create') }}" class="w-full inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-teal-700 transition">
                            Request Domestic Pickup <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Pillar 3: E-Commerce -->
                <div class="rounded-3xl border border-slate-200 p-8 bg-gradient-to-b from-white to-slate-50 shadow-sm hover:shadow-xl hover:border-indigo-500/40 transition duration-300 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                                <i class="fas fa-motorcycle"></i>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full">
                                E-Commerce Logistics
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 font-heading">
                            3. E-Commerce & Rider Fulfillment
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Built for modern online merchants. Uber-style live rider GPS tracking, instant customer notifications, automated return management, and <strong>70% instant seller settlement</strong> upon verified delivery.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-700 font-medium">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-600"></i> Live rider GPS map for recipients & merchants</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-600"></i> Automated COD wallet accounting & rapid payout</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-600"></i> Seller portal with batch order entry & thermal labels</li>
                        </ul>
                    </div>
                    <div class="pt-4 border-t border-slate-200">
                        <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-indigo-700 transition">
                            Start Selling Online <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How the Delivery Flow Works -->
    <section class="py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
                <span class="text-xs uppercase font-bold tracking-widest text-teal-600">Smooth Logistics Process</span>
                <h2 class="text-3xl font-extrabold text-slate-900 font-heading">How COURIER with NETPACK Operates</h2>
                <p class="text-slate-600 text-sm">Four seamless steps from booking to doorstep confirmation.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 relative">
                <!-- Step 1 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 text-center shadow-sm">
                    <span class="h-12 w-12 rounded-full bg-teal-100 text-teal-800 font-extrabold text-lg flex items-center justify-center mx-auto mb-4 font-heading">
                        01
                    </span>
                    <h3 class="font-bold text-slate-900 mb-2">Book or Import</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Create an international, domestic, or e-commerce order online. The system generates an atomic tracking number and HAWB document immediately.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 text-center shadow-sm">
                    <span class="h-12 w-12 rounded-full bg-teal-100 text-teal-800 font-extrabold text-lg flex items-center justify-center mx-auto mb-4 font-heading">
                        02
                    </span>
                    <h3 class="font-bold text-slate-900 mb-2">Fast Hub Intake</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Rider collects the parcel from the sender or warehouse. The barcode/QR is scanned at the central Kathmandu hub for bagging & transit routing.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 text-center shadow-sm">
                    <span class="h-12 w-12 rounded-full bg-teal-100 text-teal-800 font-extrabold text-lg flex items-center justify-center mx-auto mb-4 font-heading">
                        03
                    </span>
                    <h3 class="font-bold text-slate-900 mb-2">Flight / Road Transit</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        International dispatches fly through airline gateways; domestic parcels travel via secured vehicle manifests across provinces.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 text-center shadow-sm">
                    <span class="h-12 w-12 rounded-full bg-teal-100 text-teal-800 font-extrabold text-lg flex items-center justify-center mx-auto mb-4 font-heading">
                        04
                    </span>
                    <h3 class="font-bold text-slate-900 mb-2">POD & Settlement</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Receiver signs for parcel (digital or paper POD). If COD, funds are collected and instantly settled to the seller's verified wallet.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <section class="bg-slate-900 text-white py-16 relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 text-center relative z-10 space-y-6">
            <h2 class="text-3xl sm:text-4xl font-extrabold font-heading">
                Ready to Experience Nepal's Most Reliable Courier?
            </h2>
            <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto">
                Join thousands of individuals, exporters, retail brands, and e-commerce stores who trust COURIER with NETPACK daily.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                <a href="{{ route('tracking.page') }}" class="bg-white text-slate-900 font-bold px-8 py-3.5 rounded-xl hover:bg-slate-100 transition shadow-lg text-sm flex items-center gap-2">
                    <i class="fas fa-barcode"></i> Track Your Shipment
                </a>
                <a href="{{ route('register') }}" class="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-8 py-3.5 rounded-xl transition shadow-lg text-sm flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Open Free Account
                </a>
            </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-teal-900/30 to-blue-900/30 pointer-events-none"></div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 pt-16 pb-8 border-t border-slate-900 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-900">
                <div class="space-y-4">
                    <x-logo variant="white" size="lg" :href="route('home')" />
                    <p class="leading-relaxed text-slate-400">
                        Nepal's specialized digital courier infrastructure providing international air cargo forwarding, domestic inter-district transport, and e-commerce rider logistics.
                    </p>
                    <p class="text-[11px] text-slate-500">Kathmandu, Bagmati Province, Nepal</p>
                </div>

                <div class="space-y-3">
                    <h4 class="text-white font-bold text-sm">Services</h4>
                    <ul class="space-y-2">
                        <li><a href="#services" class="hover:text-teal-400">International Air Cargo</a></li>
                        <li><a href="#services" class="hover:text-teal-400">Domestic Express Courier</a></li>
                        <li><a href="#services" class="hover:text-teal-400">E-Commerce & COD Delivery</a></li>
                        <li><a href="{{ url('/grocery-box') }}" class="hover:text-teal-400">Smart Grocery Box</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h4 class="text-white font-bold text-sm">Tracking & Tools</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('tracking.page') }}" class="hover:text-teal-400">Public Tracking Search</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-teal-400">Customer Portal</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-teal-400">Seller Merchant Portal</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-teal-400">Rider & Partner Login</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h4 class="text-white font-bold text-sm">Contact Support</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li class="flex items-center gap-2"><i class="fas fa-phone text-teal-400"></i> +977-1-5970123</li>
                        <li class="flex items-center gap-2"><i class="fas fa-envelope text-teal-400"></i> support@couriernetpack.com</li>
                        <li class="flex items-center gap-2"><i class="fas fa-clock text-teal-400"></i> Sunday–Friday, 9:00 AM – 6:00 PM</li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>&copy; {{ date('Y') }} COURIER with NETPACK. All rights reserved. Registered under Laws of Nepal.</p>
                <div class="flex gap-4">
                    <span>Privacy Policy</span>
                    <span>·</span>
                    <span>Terms of Carriage</span>
                    <span>·</span>
                    <span>IATA Air Waybill Standards</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Omnipresent AI Logistics Copilot (Voice & Text) -->
    <x-ai-copilot-widget />

</body>
</html>
