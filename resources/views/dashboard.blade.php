<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Agri Data</title>
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
</head>
<body>

<div class="dashboard-shell">

    <!-- =============================================
         SIDEBAR
         ============================================= -->
    <aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <a href="/" class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C12 2 6 7 6 12C6 15.3137 8.68629 18 12 18C15.3137 18 18 15.3137 18 12C18 7 12 2 12 2Z" fill="#1a6df8"/>
                    <path d="M12 2C12 2 15 6 15 10C15 12.2091 13.2091 14 12 14C10.7909 14 9 12.2091 9 10C9 6 12 2 12 2Z" fill="#a5f3fc"/>
                    <path d="M12 6V12" stroke="#e2e8f0" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </a>
            <span class="brand-name">Agri Data</span>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">

            <!-- UTAMA -->
            <p class="nav-section-label">Utama</p>

            <ul>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link-item active">
                        <!-- Home Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Map Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                            <line x1="9" y1="3" x2="9" y2="18"/>
                            <line x1="15" y1="6" x2="15" y2="21"/>
                        </svg>
                        <span class="nav-label">Peta Wilayah</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Cloud-Sun Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                            <path d="M20 17.58A5 5 0 0 0 18 8h-1.26A8 8 0 1 0 4 16.25"/>
                        </svg>
                        <span class="nav-label">Cuaca & Iklim</span>
                    </a>
                </li>
            </ul>

            <!-- PERTANIAN -->
            <p class="nav-section-label">Pertanian</p>
            <ul>
                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Leaf Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 1 9.8a7 7 0 0 1-9 8.2z"/>
                            <path d="M9 22v-4h4"/>
                        </svg>
                        <span class="nav-label">Penyakit Tanaman</span>
                        <span class="nav-badge">Baru</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Package Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        <span class="nav-label">Produk Panen</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Users Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span class="nav-label">Data Petani</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Shopping Cart Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span class="nav-label">Transaksi</span>
                    </a>
                </li>
            </ul>

            <!-- KONTEN -->
            <p class="nav-section-label">Konten</p>
            <ul>
                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- File Text Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span class="nav-label">Artikel</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Star Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        <span class="nav-label">Ulasan & Rating</span>
                    </a>
                </li>
            </ul>

            <!-- PENGATURAN -->
            <p class="nav-section-label">Sistem</p>
            <ul>
                <li class="nav-item">
                    <a href="#" class="nav-link-item">
                        <!-- Settings Icon -->
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        <span class="nav-label">Pengaturan</span>
                    </a>
                </li>
            </ul>

        </nav>

        <!-- User Profile -->
        <div class="sidebar-footer">
            <a href="#" class="user-profile-card">
                <div class="user-avatar">AD</div>
                <div class="user-info">
                    <p class="user-name">Admin Dinas</p>
                    <p class="user-role">Administrator</p>
                </div>
            </a>
        </div>

    </aside>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- =============================================
         MAIN CONTENT
         ============================================= -->
    <div class="main-content" id="main-content">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Toggle Button -->
                <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle Sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <!-- Breadcrumb -->
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <span class="breadcrumb-item">Agri Data</span>
                    <span class="breadcrumb-sep">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                    <span class="breadcrumb-item active">Dashboard</span>
                </nav>
            </div>

            <!-- Search Bar -->
            <div class="topbar-search">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" placeholder="Cari data, petani, produk…" aria-label="Cari">
            </div>

            <!-- Right Actions -->
            <div class="topbar-right">
                <!-- Notification -->
                <button class="topbar-icon-btn" aria-label="Notifikasi">
                    <div class="notif-dot"></div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </button>

                <!-- Settings -->
                <button class="topbar-icon-btn" aria-label="Pengaturan">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06-.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </button>

                <div class="topbar-divider"></div>

                <!-- Avatar -->
                <div class="topbar-avatar" title="Admin Dinas">AD</div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="page-content">

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-group">
                    <h1 class="page-title">Selamat Datang, Admin 👋</h1>
                    <p class="page-subtitle">Berikut ringkasan data pertanian hari ini — {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="page-header-actions">
                    <button class="btn btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Ekspor
                    </button>
                    <button class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Tambah Data
                    </button>
                </div>
            </div>

            <!-- ==================
                 KPI STAT CARDS
                 ================== -->
            <div class="stat-cards-grid">

                <!-- Card: Total Petani -->
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-card-icon blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="stat-card-trend up">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                            +12%
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-card-value">2.847</span>
                        <span class="stat-card-label">Total Petani Terdaftar</span>
                    </div>
                    <div class="stat-card-footer">
                        <span>+38</span> petani baru bulan ini
                    </div>
                </div>

                <!-- Card: Produk Panen -->
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-card-icon green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                            </svg>
                        </div>
                        <div class="stat-card-trend up">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                            +8%
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-card-value">1.234</span>
                        <span class="stat-card-label">Produk Panen Aktif</span>
                    </div>
                    <div class="stat-card-footer">
                        Stok total <span>48.920 kg</span> tersedia
                    </div>
                </div>

                <!-- Card: Total Transaksi -->
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-card-icon yellow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                        <div class="stat-card-trend up">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                            +23%
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-card-value">Rp 182 Jt</span>
                        <span class="stat-card-label">Total Nilai Transaksi</span>
                    </div>
                    <div class="stat-card-footer">
                        <span>342</span> transaksi bulan ini
                    </div>
                </div>

                <!-- Card: Laporan Penyakit -->
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-card-icon red">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                        <div class="stat-card-trend down">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                            +5%
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-card-value">27</span>
                        <span class="stat-card-label">Laporan Penyakit Tanaman</span>
                    </div>
                    <div class="stat-card-footer">
                        <span>8</span> kasus perlu penanganan segera
                    </div>
                </div>

            </div>

            <!-- ==================
                 CHARTS
                 ================== -->
            <div class="charts-grid">

                <!-- Line Chart: Produksi Bulanan -->
                <div class="chart-card">
                    <div class="card-header">
                        <div>
                            <p class="card-title">Tren Produksi Panen</p>
                            <p class="card-subtitle">Volume produksi (ton) per bulan</p>
                        </div>
                        <div class="card-actions">
                            <div class="tab-pills">
                                <span class="tab-pill active" data-period="6">6 Bln</span>
                                <span class="tab-pill" data-period="12">1 Thn</span>
                            </div>
                        </div>
                    </div>

                    <!-- SVG Line Chart -->
                    <div class="chart-area">
                        <div class="chart-svg-wrapper">
                            <svg id="line-chart" viewBox="0 0 700 280" xmlns="http://www.w3.org/2000/svg" width="100%" height="280">
                                <defs>
                                    <linearGradient id="lineGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#1a6df8" stop-opacity="0.15"/>
                                        <stop offset="100%" stop-color="#1a6df8" stop-opacity="0"/>
                                    </linearGradient>
                                    <linearGradient id="lineGradient2" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.12"/>
                                        <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>

                                <!-- Grid Lines -->
                                <line x1="60" y1="20" x2="680" y2="20" stroke="#f1f5f9" stroke-width="1"/>
                                <line x1="60" y1="75" x2="680" y2="75" stroke="#f1f5f9" stroke-width="1"/>
                                <line x1="60" y1="130" x2="680" y2="130" stroke="#f1f5f9" stroke-width="1"/>
                                <line x1="60" y1="185" x2="680" y2="185" stroke="#f1f5f9" stroke-width="1"/>
                                <line x1="60" y1="240" x2="680" y2="240" stroke="#e2e8f0" stroke-width="1"/>

                                <!-- Y-axis labels -->
                                <text x="50" y="24" font-size="11" fill="#94a3b8" text-anchor="end" font-family="Inter">500</text>
                                <text x="50" y="79" font-size="11" fill="#94a3b8" text-anchor="end" font-family="Inter">400</text>
                                <text x="50" y="134" font-size="11" fill="#94a3b8" text-anchor="end" font-family="Inter">300</text>
                                <text x="50" y="189" font-size="11" fill="#94a3b8" text-anchor="end" font-family="Inter">200</text>
                                <text x="50" y="244" font-size="11" fill="#94a3b8" text-anchor="end" font-family="Inter">100</text>

                                <!-- X-axis labels -->
                                <text x="100" y="265" font-size="11" fill="#94a3b8" text-anchor="middle" font-family="Inter">Jan</text>
                                <text x="200" y="265" font-size="11" fill="#94a3b8" text-anchor="middle" font-family="Inter">Feb</text>
                                <text x="300" y="265" font-size="11" fill="#94a3b8" text-anchor="middle" font-family="Inter">Mar</text>
                                <text x="400" y="265" font-size="11" fill="#94a3b8" text-anchor="middle" font-family="Inter">Apr</text>
                                <text x="500" y="265" font-size="11" fill="#94a3b8" text-anchor="middle" font-family="Inter">Mei</text>
                                <text x="600" y="265" font-size="11" fill="#94a3b8" text-anchor="middle" font-family="Inter">Jun</text>

                                <!-- Area fill: Padi -->
                                <path d="M100,130 L200,95 L300,75 L400,110 L500,55 L600,80 L600,240 L500,240 L400,240 L300,240 L200,240 L100,240 Z"
                                      fill="url(#lineGradient)"/>

                                <!-- Area fill: Jagung -->
                                <path d="M100,175 L200,165 L300,145 L400,155 L500,120 L600,135 L600,240 L500,240 L400,240 L300,240 L200,240 L100,240 Z"
                                      fill="url(#lineGradient2)"/>

                                <!-- Line: Jagung -->
                                <polyline points="100,175 200,165 300,145 400,155 500,120 600,135"
                                          fill="none" stroke="#10b981" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" stroke-dasharray="4 2" opacity="0.8"/>

                                <!-- Line: Padi -->
                                <polyline points="100,130 200,95 300,75 400,110 500,55 600,80"
                                          fill="none" stroke="#1a6df8" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                                <!-- Dots: Padi -->
                                <circle cx="100" cy="130" r="4" fill="#fff" stroke="#1a6df8" stroke-width="2"/>
                                <circle cx="200" cy="95"  r="4" fill="#fff" stroke="#1a6df8" stroke-width="2"/>
                                <circle cx="300" cy="75"  r="4" fill="#fff" stroke="#1a6df8" stroke-width="2"/>
                                <circle cx="400" cy="110" r="4" fill="#fff" stroke="#1a6df8" stroke-width="2"/>
                                <circle cx="500" cy="55"  r="5" fill="#1a6df8" stroke="#fff" stroke-width="2"/>
                                <circle cx="600" cy="80"  r="4" fill="#fff" stroke="#1a6df8" stroke-width="2"/>

                                <!-- Dots: Jagung -->
                                <circle cx="100" cy="175" r="3.5" fill="#fff" stroke="#10b981" stroke-width="2"/>
                                <circle cx="200" cy="165" r="3.5" fill="#fff" stroke="#10b981" stroke-width="2"/>
                                <circle cx="300" cy="145" r="3.5" fill="#fff" stroke="#10b981" stroke-width="2"/>
                                <circle cx="400" cy="155" r="3.5" fill="#fff" stroke="#10b981" stroke-width="2"/>
                                <circle cx="500" cy="120" r="3.5" fill="#fff" stroke="#10b981" stroke-width="2"/>
                                <circle cx="600" cy="135" r="3.5" fill="#fff" stroke="#10b981" stroke-width="2"/>

                                <!-- Tooltip on peak -->
                                <rect x="476" y="26" width="74" height="32" rx="8" fill="#1a6df8"/>
                                <text x="513" y="40" font-size="10" fill="#fff" text-anchor="middle" font-family="Inter" font-weight="700">480 ton</text>
                                <text x="513" y="53" font-size="9" fill="rgba(255,255,255,0.8)" text-anchor="middle" font-family="Inter">Mei · Padi</text>
                                <polygon points="510,58 516,58 513,64" fill="#1a6df8"/>
                            </svg>
                        </div>

                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background:#1a6df8"></span>
                                Padi
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background:#10b981"></span>
                                Jagung
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Donut Chart: Distribusi Komoditas -->
                <div class="chart-card">
                    <div class="card-header">
                        <div>
                            <p class="card-title">Distribusi Komoditas</p>
                            <p class="card-subtitle">Berdasarkan total stok aktif</p>
                        </div>
                    </div>

                    <div class="donut-chart-wrapper">
                        <!-- Donut SVG -->
                        <div class="donut-svg-wrapper">
                            <svg viewBox="0 0 180 180" xmlns="http://www.w3.org/2000/svg" width="180" height="180">
                                <!-- Background circle -->
                                <circle cx="90" cy="90" r="70" fill="none" stroke="#f1f5f9" stroke-width="22"/>
                                <!-- Padi 42% → stroke-dasharray: 42% of circumference (439.82) = 184.7 -->
                                <circle cx="90" cy="90" r="70" fill="none" stroke="#1a6df8" stroke-width="22"
                                        stroke-dasharray="184.7 255.12" stroke-dashoffset="109.95"
                                        stroke-linecap="round" transform="rotate(-90 90 90)"/>
                                <!-- Jagung 28% → 123.15 -->
                                <circle cx="90" cy="90" r="70" fill="none" stroke="#10b981" stroke-width="22"
                                        stroke-dasharray="123.15 316.67" stroke-dashoffset="-74.75"
                                        stroke-linecap="round" transform="rotate(-90 90 90)"/>
                                <!-- Cabai 18% → 79.17 -->
                                <circle cx="90" cy="90" r="70" fill="none" stroke="#f59e0b" stroke-width="22"
                                        stroke-dasharray="79.17 360.65" stroke-dashoffset="-197.9"
                                        stroke-linecap="round" transform="rotate(-90 90 90)"/>
                                <!-- Lainnya 12% → 52.78 -->
                                <circle cx="90" cy="90" r="70" fill="none" stroke="#94a3b8" stroke-width="22"
                                        stroke-dasharray="52.78 387.04" stroke-dashoffset="-277.07"
                                        stroke-linecap="round" transform="rotate(-90 90 90)"/>
                            </svg>
                            <div class="donut-center-label">
                                <div class="donut-center-value">48.9K</div>
                                <div class="donut-center-text">Total (kg)</div>
                            </div>
                        </div>

                        <!-- Donut Legend -->
                        <div class="donut-legend">
                            <div class="donut-legend-item">
                                <div class="donut-legend-label">
                                    <span class="legend-dot" style="background:#1a6df8; display:inline-block; width:10px; height:10px; border-radius:50%; flex-shrink:0;"></span>
                                    Padi
                                </div>
                                <div class="donut-legend-bar-wrap">
                                    <div class="donut-legend-bar" style="width:42%; background:#1a6df8;"></div>
                                </div>
                                <span class="donut-legend-pct">42%</span>
                            </div>
                            <div class="donut-legend-item">
                                <div class="donut-legend-label">
                                    <span class="legend-dot" style="background:#10b981; display:inline-block; width:10px; height:10px; border-radius:50%; flex-shrink:0;"></span>
                                    Jagung
                                </div>
                                <div class="donut-legend-bar-wrap">
                                    <div class="donut-legend-bar" style="width:28%; background:#10b981;"></div>
                                </div>
                                <span class="donut-legend-pct">28%</span>
                            </div>
                            <div class="donut-legend-item">
                                <div class="donut-legend-label">
                                    <span class="legend-dot" style="background:#f59e0b; display:inline-block; width:10px; height:10px; border-radius:50%; flex-shrink:0;"></span>
                                    Cabai
                                </div>
                                <div class="donut-legend-bar-wrap">
                                    <div class="donut-legend-bar" style="width:18%; background:#f59e0b;"></div>
                                </div>
                                <span class="donut-legend-pct">18%</span>
                            </div>
                            <div class="donut-legend-item">
                                <div class="donut-legend-label">
                                    <span class="legend-dot" style="background:#94a3b8; display:inline-block; width:10px; height:10px; border-radius:50%; flex-shrink:0;"></span>
                                    Lainnya
                                </div>
                                <div class="donut-legend-bar-wrap">
                                    <div class="donut-legend-bar" style="width:12%; background:#94a3b8;"></div>
                                </div>
                                <span class="donut-legend-pct">12%</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==================
                 BOTTOM ROW
                 ================== -->
            <div class="bottom-grid">

                <!-- Transaksi Terbaru -->
                <div class="table-card">
                    <div class="card-header">
                        <div>
                            <p class="card-title">Transaksi Terbaru</p>
                            <p class="card-subtitle">10 transaksi terakhir yang masuk</p>
                        </div>
                        <a href="#" class="btn btn-ghost" style="font-size:0.8rem; padding:6px 12px;">
                            Lihat Semua
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>

                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Petani</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">🌾</div>
                                            <div>
                                                <div class="product-name">Beras Premium</div>
                                                <div class="product-id">#TRX-0041</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Pak Suparman</td>
                                    <td>50 kg</td>
                                    <td>Rp 750.000</td>
                                    <td><span class="badge badge-success">Selesai</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">🌽</div>
                                            <div>
                                                <div class="product-name">Jagung Pipil</div>
                                                <div class="product-id">#TRX-0040</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Bu Rahayu</td>
                                    <td>120 kg</td>
                                    <td>Rp 600.000</td>
                                    <td><span class="badge badge-info">Diproses</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">🌶️</div>
                                            <div>
                                                <div class="product-name">Cabai Rawit</div>
                                                <div class="product-id">#TRX-0039</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Pak Hendra</td>
                                    <td>25 kg</td>
                                    <td>Rp 1.250.000</td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">🥬</div>
                                            <div>
                                                <div class="product-name">Bayam Organik</div>
                                                <div class="product-id">#TRX-0038</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Bu Siti</td>
                                    <td>30 kg</td>
                                    <td>Rp 180.000</td>
                                    <td><span class="badge badge-success">Selesai</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">🍅</div>
                                            <div>
                                                <div class="product-name">Tomat Segar</div>
                                                <div class="product-id">#TRX-0037</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Pak Wahyu</td>
                                    <td>60 kg</td>
                                    <td>Rp 420.000</td>
                                    <td><span class="badge badge-danger">Dibatalkan</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <span class="table-footer-text">Menampilkan 5 dari 342 transaksi</span>
                        <button class="btn btn-secondary" style="font-size:0.78rem; padding:6px 14px;">
                            Muat Lebih
                        </button>
                    </div>
                </div>

                <!-- Cuaca Madiun -->
                <div class="weather-card">
                    <!-- Weather Header -->
                    <div class="weather-header">
                        <div class="weather-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Madiun, Jawa Timur
                        </div>
                        <div class="weather-main">
                            <div>
                                <div class="weather-temp">28<sup>°C</sup></div>
                                <div class="weather-desc">Berawan Sebagian</div>
                            </div>
                            <svg class="weather-icon-large" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                                <!-- Sun -->
                                <circle cx="28" cy="26" r="10" fill="#fbbf24" opacity="0.95"/>
                                <line x1="28" y1="10" x2="28" y2="6" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
                                <line x1="28" y1="46" x2="28" y2="42" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
                                <line x1="12" y1="26" x2="8"  y2="26" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
                                <line x1="48" y1="26" x2="44" y2="26" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
                                <line x1="17" y1="15" x2="14" y2="12" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="39" y1="37" x2="42" y2="40" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="39" y1="15" x2="42" y2="12" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="17" y1="37" x2="14" y2="40" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
                                <!-- Cloud -->
                                <ellipse cx="42" cy="44" rx="14" ry="9" fill="rgba(255,255,255,0.9)"/>
                                <ellipse cx="34" cy="46" rx="10" ry="8" fill="rgba(255,255,255,0.9)"/>
                                <ellipse cx="38" cy="40" rx="9" ry="7" fill="rgba(255,255,255,0.9)"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Weather Detail Grid -->
                    <div class="weather-details">
                        <div class="weather-detail-item">
                            <svg class="weather-detail-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2a7 7 0 0 0-7 7c0 4.97 7 13 7 13s7-8.03 7-13a7 7 0 0 0-7-7z"/>
                                <circle cx="12" cy="9" r="2.5"/>
                            </svg>
                            <div class="weather-detail-value">82%</div>
                            <div class="weather-detail-label">Kelembapan</div>
                        </div>
                        <div class="weather-detail-item">
                            <svg class="weather-detail-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>
                            </svg>
                            <div class="weather-detail-value">12 km/j</div>
                            <div class="weather-detail-label">Angin</div>
                        </div>
                        <div class="weather-detail-item">
                            <svg class="weather-detail-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="2" x2="12" y2="6"/>
                                <line x1="12" y1="18" x2="12" y2="22"/>
                                <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/>
                                <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/>
                                <line x1="2" y1="12" x2="6" y2="12"/>
                                <line x1="18" y1="12" x2="22" y2="12"/>
                                <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/>
                                <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/>
                            </svg>
                            <div class="weather-detail-value">4 mm</div>
                            <div class="weather-detail-label">Curah Hujan</div>
                        </div>
                        <div class="weather-detail-item">
                            <svg class="weather-detail-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="5"/>
                                <line x1="12" y1="1" x2="12" y2="3"/>
                                <line x1="12" y1="21" x2="12" y2="23"/>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                <line x1="1" y1="12" x2="3" y2="12"/>
                                <line x1="21" y1="12" x2="23" y2="12"/>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                            </svg>
                            <div class="weather-detail-value">7 UV</div>
                            <div class="weather-detail-label">Indeks UV</div>
                        </div>
                    </div>

                    <!-- 7-Day Forecast -->
                    <div class="forecast-section">
                        <p class="forecast-title">Prakiraan 7 Hari</p>
                        <div class="forecast-list">
                            <div class="forecast-item">
                                <span class="forecast-day">Sen</span>
                                <svg class="forecast-icon" viewBox="0 0 24 24" fill="#fbbf24"><circle cx="12" cy="12" r="5"/></svg>
                                <div class="forecast-bar-wrap"><div class="forecast-bar" style="width:85%"></div></div>
                                <span class="forecast-temp">30°C</span>
                            </div>
                            <div class="forecast-item">
                                <span class="forecast-day">Sel</span>
                                <svg class="forecast-icon" viewBox="0 0 24 24" fill="#94a3b8"><path d="M20 17.58A5 5 0 0 0 18 8h-1.26A8 8 0 1 0 4 16.25" stroke="#94a3b8" stroke-width="1.5" fill="none"/><path d="M8 19v1M8 22v1M12 19v1M12 22v1M16 19v1M16 22v1" stroke="#64b5f6" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <div class="forecast-bar-wrap"><div class="forecast-bar" style="width:55%; background:linear-gradient(90deg,#94a3b8,#64b5f6)"></div></div>
                                <span class="forecast-temp">26°C</span>
                            </div>
                            <div class="forecast-item">
                                <span class="forecast-day">Rab</span>
                                <svg class="forecast-icon" viewBox="0 0 24 24" fill="#94a3b8"><path d="M20 17.58A5 5 0 0 0 18 8h-1.26A8 8 0 1 0 4 16.25" stroke="#94a3b8" stroke-width="1.5" fill="none"/><path d="M8 19v1M8 22v1M12 19v1M12 22v1M16 19v1M16 22v1" stroke="#64b5f6" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <div class="forecast-bar-wrap"><div class="forecast-bar" style="width:50%; background:linear-gradient(90deg,#94a3b8,#64b5f6)"></div></div>
                                <span class="forecast-temp">25°C</span>
                            </div>
                            <div class="forecast-item">
                                <span class="forecast-day">Kam</span>
                                <svg class="forecast-icon" viewBox="0 0 24 24" fill="#fbbf24"><circle cx="12" cy="12" r="5"/></svg>
                                <div class="forecast-bar-wrap"><div class="forecast-bar" style="width:78%"></div></div>
                                <span class="forecast-temp">29°C</span>
                            </div>
                            <div class="forecast-item">
                                <span class="forecast-day">Jum</span>
                                <svg class="forecast-icon" viewBox="0 0 24 24" fill="#fbbf24"><circle cx="12" cy="12" r="5"/></svg>
                                <div class="forecast-bar-wrap"><div class="forecast-bar" style="width:90%"></div></div>
                                <span class="forecast-temp">31°C</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <!-- /bottom-grid -->

        </main>
        <!-- /page-content -->

    </div>
    <!-- /main-content -->

</div>
<!-- /dashboard-shell -->

<!-- =============================================
     JAVASCRIPT
     ============================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const sidebar      = document.getElementById('sidebar');
    const mainContent  = document.getElementById('main-content');
    const toggleBtn    = document.getElementById('sidebar-toggle');
    const overlay      = document.getElementById('sidebar-overlay');
    const isMobile     = () => window.innerWidth <= 768;

    // Toggle sidebar (desktop = collapse / mobile = slide)
    toggleBtn.addEventListener('click', function () {
        if (isMobile()) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('visible');
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    });

    // Close on overlay click (mobile)
    overlay.addEventListener('click', function () {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('visible');
    });

    // Handle window resize
    window.addEventListener('resize', function () {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('visible');
        }
    });

    // Tab pills for chart period
    document.querySelectorAll('.tab-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            this.closest('.tab-pills').querySelectorAll('.tab-pill').forEach(function (p) {
                p.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // Animate stat card values (count-up)
    function animateCount(el, target, prefix, suffix, duration) {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(function () {
            start += increment;
            if (start >= target) {
                start = target;
                clearInterval(timer);
            }
            el.textContent = prefix + Math.floor(start).toLocaleString('id-ID') + suffix;
        }, 16);
    }

    // Trigger animation on page load for stat values
    const statValues = document.querySelectorAll('.stat-card-value');
    const configs = [
        { prefix: '', suffix: '', target: 2847 },
        { prefix: '', suffix: '', target: 1234 },
        { prefix: 'Rp ', suffix: ' Jt', target: 182 },
        { prefix: '', suffix: '', target: 27 },
    ];

    statValues.forEach(function (el, i) {
        if (configs[i]) {
            const cfg = configs[i];
            setTimeout(function () {
                animateCount(el, cfg.target, cfg.prefix, cfg.suffix, 1200);
            }, i * 150);
        }
    });

});
</script>

</body>
</html>
