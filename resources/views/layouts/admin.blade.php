{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'COURIER with NETPACK Admin')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }
        
        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 280px;
            background: #0b2a3b;
            color: #e5f0f7;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #1f4057;
            border-radius: 10px;
        }
        
        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid #1f4057;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-brand .logo-icon {
            width: 44px;
            height: 44px;
            background: #DC2626;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }
        
        .sidebar-brand .brand-text h1 {
            font-size: 20px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
        }
        
        .sidebar-brand .brand-text span {
            font-size: 12px;
            color: #9bb7c9;
            font-weight: 400;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 20px 14px;
            overflow-y: auto;
        }
        
        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #6f8fa3;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 8px 12px 12px;
            opacity: 0.7;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 11px 16px;
            border-radius: 10px;
            color: #cbdde9;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            margin-bottom: 2px;
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }
        
        .nav-item i {
            width: 20px;
            font-size: 16px;
            color: #88b0c9;
            transition: color 0.2s;
        }
        
        .nav-item:hover {
            background: #1a384b;
            color: white;
        }
        
        .nav-item:hover i {
            color: #f5b041;
        }
        
        .nav-item.active {
            background: #1f4057;
            color: white;
        }
        
        .nav-item.active i {
            color: #f5b041;
        }
        
        .nav-item .badge {
            margin-left: auto;
            background: #DC2626;
            color: white;
            font-size: 11px;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .nav-item .arrow {
            margin-left: auto;
            font-size: 12px;
            transition: transform 0.3s;
        }
        
        .nav-item .arrow.open {
            transform: rotate(90deg);
        }
        
        .nav-sub-items {
            padding-left: 30px;
            margin-top: 2px;
            margin-bottom: 4px;
        }
        
        .nav-sub-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: 8px;
            color: #9bb7c9;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 1px;
        }
        
        .nav-sub-item i {
            width: 16px;
            font-size: 13px;
            color: #6f8fa3;
        }
        
        .nav-sub-item:hover {
            background: #1a384b;
            color: white;
        }
        
        .nav-sub-item.active {
            background: #1f4057;
            color: white;
        }
        
        .nav-sub-item.active i {
            color: #f5b041;
        }
        
        /* Sidebar Footer */
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid #1f4057;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-footer .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #1f4d66;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            border: 2px solid #f5b041;
            flex-shrink: 0;
        }
        
        .sidebar-footer .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .sidebar-footer .user-info .name {
            font-weight: 600;
            color: white;
            font-size: 14px;
        }
        
        .sidebar-footer .user-info .email {
            font-size: 12px;
            color: #9bb7c9;
        }
        
        .sidebar-footer .logout-btn {
            color: #88b0c9;
            transition: color 0.2s;
            padding: 6px;
            border-radius: 8px;
        }
        
        .sidebar-footer .logout-btn:hover {
            color: #DC2626;
            background: rgba(220, 38, 38, 0.1);
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Top Header */
        .top-header {
            background: white;
            border-bottom: 1px solid #e2edf5;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-header .page-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0b2a3b;
        }
        
        .top-header .page-title p {
            font-size: 13px;
            color: #4f6f82;
            margin-top: 2px;
        }
        
        .top-header .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .top-header .header-actions .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #e2edf5;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f6f82;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .top-header .header-actions .notification-btn:hover {
            background: #f0f4f8;
            border-color: #c8dce9;
        }
        
        .top-header .header-actions .notification-btn .dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #DC2626;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .top-header .header-actions .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px 6px 6px;
            border-radius: 50px;
            border: 1px solid #e2edf5;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .top-header .header-actions .user-profile:hover {
            background: #f0f4f8;
        }
        
        .top-header .header-actions .user-profile img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .top-header .header-actions .user-profile .name {
            font-weight: 600;
            font-size: 14px;
            color: #0b2a3b;
        }
        
        /* Page Content */
        .page-content {
            flex: 1;
            overflow-y: auto;
            padding: 24px 32px 32px;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .mobile-menu-btn {
                display: flex !important;
            }
        }
        
        @media (min-width: 1025px) {
            .mobile-menu-btn {
                display: none !important;
            }
            .sidebar-overlay {
                display: none !important;
            }
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #0b2a3b;
            cursor: pointer;
            padding: 4px;
        }
        
        /* Scrollbar */
        .page-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .page-content::-webkit-scrollbar-thumb {
            background: #c8dce9;
            border-radius: 10px;
        }
        
        .page-content::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="logo-icon">N</div>
            <div class="brand-text">
                <h1>COURIER with NETPACK</h1>
                <span>Nepal's Trusted Delivery</span>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div class="nav-section-title">MAIN MENU</div>
            
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i>
                <span>Users Management</span>
                <span class="badge">{{ App\Models\User::count() }}</span>
            </a>
            
            <!-- Inquiries with Sub-items -->
            <div class="nav-item" onclick="toggleSubMenu('inquiriesSub')" style="cursor:pointer;">
                <i class="fas fa-file-alt"></i>
                <span>Inquiries</span>
                <i class="fas fa-chevron-right arrow" id="inquiriesArrow"></i>
            </div>
            <div class="nav-sub-items" id="inquiriesSub" style="display:none;">
                <a href="#" class="nav-sub-item">
                    <i class="fas fa-list"></i> All Inquiries
                </a>
                <a href="#" class="nav-sub-item">
                    <i class="fas fa-share"></i> Forwarded
                </a>
                <a href="#" class="nav-sub-item">
                    <i class="fas fa-star"></i> Shortlisted
                </a>
                <a href="#" class="nav-sub-item">
                    <i class="fas fa-check-circle"></i> Approved
                </a>
            </div>
            
            <!-- Quotes -->
            <div class="nav-item" onclick="toggleSubMenu('quotesSub')" style="cursor:pointer;">
                <i class="fas fa-dollar-sign"></i>
                <span>Quotes</span>
                <i class="fas fa-chevron-right arrow" id="quotesArrow"></i>
            </div>
            <div class="nav-sub-items" id="quotesSub" style="display:none;">
                <a href="#" class="nav-sub-item">
                    <i class="fas fa-list"></i> Manage Quotes
                </a>
                <a href="#" class="nav-sub-item">
                    <i class="fas fa-chart-bar"></i> Quote Analytics
                </a>
            </div>
            
            <div class="nav-section-title" style="margin-top:16px;">MANAGEMENT</div>
            
            <a href="#" class="nav-item">
                <i class="fas fa-plane"></i>
                <span>Air Cargo</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-ship"></i>
                <span>Sea Cargo</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-truck"></i>
                <span>ODC</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-chart-pie"></i>
                <span>Accounting</span>
            </a>
            
            <div class="nav-section-title" style="margin-top:16px;">SETTINGS</div>
            
            <a href="#" class="nav-item">
                <i class="fas fa-university"></i>
                <span>Financial Settings</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-file-invoice"></i>
                <span>Reports</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>System Settings</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-headset"></i>
                <span>Support</span>
            </a>
        </nav>
        
        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="avatar">{{ auth()->user()->name[0] ?? 'A' }}</div>
            <div class="user-info">
                <div class="name">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                <div class="email">{{ auth()->user()->email ?? 'admin@netpack.com' }}</div>
            </div>
            <a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </aside>
    
    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="page-title">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>@yield('page-title', 'Dashboard')</h2>
                <p>@yield('page-subtitle', 'Manage your logistics operations')</p>
            </div>
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="dot"></span>
                </button>
                <div class="user-profile">
                    <img src="{{ auth()->user()->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name ?? 'Admin').'&background=003366&color=fff' }}" alt="Profile">
                    <span class="name">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <i class="fas fa-chevron-down" style="font-size:12px;color:#9bb7c9;"></i>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>
    </div>
    
    <script>
        // Toggle Sub-menu
        function toggleSubMenu(id) {
            var sub = document.getElementById(id);
            var arrow = document.getElementById(id.replace('Sub', 'Arrow'));
            if (sub.style.display === 'none' || sub.style.display === '') {
                sub.style.display = 'block';
                if (arrow) arrow.classList.add('open');
            } else {
                sub.style.display = 'none';
                if (arrow) arrow.classList.remove('open');
            }
        }
        
        // Mobile Menu Toggle
        var mobileBtn = document.getElementById('mobileMenuBtn');
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        
        if (mobileBtn) {
            mobileBtn.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            });
        }
    </script>

    <!-- Omnipresent AI Logistics Copilot (Voice & Text) -->
    <x-ai-copilot-widget />
</body>
</html>

