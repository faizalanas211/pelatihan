<aside id="layout-menu" class="layout-menu menu-vertical premium-sidebar">

<style>
.premium-sidebar {
    background: linear-gradient(165deg, #fff5eb 0%, #ffe4cc 30%, #ffd9b5 70%, #ffecd9 100%);
    border-radius: 0 32px 32px 0;
    box-shadow: 8px 0 32px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow-x: hidden;
    height: 100vh;
}

/* Top header gradient line */
.premium-sidebar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7);
    z-index: 10;
}

/* Right side subtle accent */
.premium-sidebar::after {
    content: '';
    position: absolute;
    top: 15%;
    right: 0;
    width: 3px;
    height: 70%;
    background: linear-gradient(180deg, transparent, #f97316, #f59e0b, #fbbf24, transparent);
    border-radius: 3px;
    opacity: 0.4;
}

/* MENU STYLING */
.premium-sidebar .menu-inner {
    padding: 0 16px;
    flex: 1;
}

.premium-sidebar .menu-link {
    border-radius: 16px;
    padding: 12px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #5c4a3a;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    text-decoration: none;
    margin: 2px 0;
}

.premium-sidebar .menu-link i {
    font-size: 20px;
    transition: all 0.3s ease;
    color: #b87a4a;
}

.premium-sidebar .menu-item.active > .menu-link {
    background: linear-gradient(135deg, #f97316, #f59e0b);
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.25);
}

.premium-sidebar .menu-item.active > .menu-link i {
    color: #ffffff !important;
}

.premium-sidebar .menu-link:hover {
    background: #ffffffcc;
    color: #ea580c !important;
    transform: translateX(4px);
}

.premium-sidebar .menu-link:hover i {
    color: #f97316 !important;
}

/* MENU HEADER */
.premium-sidebar .menu-header {
    padding: 24px 16px 8px 16px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #f97316;
    text-transform: uppercase;
    position: relative;
}

.premium-sidebar .menu-header::after {
    content: '';
    position: absolute;
    bottom: 4px;
    left: 16px;
    width: 30px;
    height: 2px;
    background: linear-gradient(90deg, #f97316, #fbbf24);
    border-radius: 2px;
}

/* APP BRAND */
.premium-sidebar .app-brand {
    padding: 24px 16px 20px 16px;
    text-align: center;
    border-bottom: 1px solid #fde68a;
    margin-bottom: 12px;
}

.app-brand-logo {
    background: linear-gradient(135deg, #f97316, #f59e0b);
    width: 50px;
    height: 50px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px auto;
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.2);
}

.app-brand-logo i {
    font-size: 26px;
    color: white;
}

.app-brand-text {
    font-size: 18px;
    font-weight: 800;
    background: linear-gradient(135deg, #ea580c, #f97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 0.5px;
    display: block;
    white-space: nowrap;
}

.app-brand-sub {
    font-size: 9px;
    color: #f59e0b;
    margin-top: 6px;
    font-weight: 500;
    display: block;
    text-align: center;
    word-break: keep-all;
    white-space: normal;
    line-height: 1.3;
    padding: 0 8px;
}

/* SIDEBAR PROFILE */
.sidebar-profile {
    margin: 16px;
    padding: 12px 14px;
    border-radius: 20px;
    background: #ffffffcc;
    backdrop-filter: blur(4px);
    border: 1px solid #fed7aa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
}

.sidebar-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.12);
    border-color: #fbbf24;
    background: #ffffff;
}

.profile-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.profile-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    cursor: pointer;
    border: 2px solid #fbbf24;
    transition: all 0.3s ease;
}

.profile-img:hover {
    transform: scale(1.03);
    border-color: #f97316;
}

.profile-info .profile-name {
    font-weight: 700;
    font-size: 14px;
    color: #4b3a2a;
    line-height: 1.3;
}

.profile-info .profile-role {
    font-size: 11px;
    color: #f97316;
    font-weight: 600;
    background: #fff7ed;
    padding: 2px 10px;
    border-radius: 20px;
    display: inline-block;
    margin-top: 4px;
}

.logout-btn {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #f97316;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.logout-btn:hover {
    background: linear-gradient(135deg, #f97316, #f59e0b);
    color: white;
    border-color: transparent;
    transform: scale(1.05);
}

/* ANIMATIONS */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-16px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.menu-item {
    animation: slideIn 0.25s ease forwards;
    opacity: 0;
}

.menu-item:nth-child(1) { animation-delay: 0.02s; }
.menu-item:nth-child(2) { animation-delay: 0.04s; }
.menu-item:nth-child(3) { animation-delay: 0.06s; }
.menu-item:nth-child(4) { animation-delay: 0.08s; }
.menu-item:nth-child(5) { animation-delay: 0.10s; }
.menu-item:nth-child(6) { animation-delay: 0.12s; }
.menu-item:nth-child(7) { animation-delay: 0.14s; }
.menu-item:nth-child(8) { animation-delay: 0.16s; }
.menu-item:nth-child(9) { animation-delay: 0.18s; }
.menu-item:nth-child(10) { animation-delay: 0.20s; }
.menu-item:nth-child(11) { animation-delay: 0.22s; }
.menu-item:nth-child(12) { animation-delay: 0.24s; }

/* SCROLLBAR */
.premium-sidebar::-webkit-scrollbar {
    width: 4px;
}

.premium-sidebar::-webkit-scrollbar-track {
    background: #fef3c7;
    border-radius: 10px;
}

.premium-sidebar::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #f97316, #fbbf24);
    border-radius: 10px;
}

/* BADGE */
.menu-badge {
    background: #f97316;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 20px;
    margin-left: auto;
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
}
</style>

{{-- APP BRAND --}}
<div class="app-brand">
    <div class="app-brand-logo">
        <i class="bx bxs-graduation"></i>
    </div>
    <div class="app-brand-text">Sistem Manajemen<br>Pelatihan</div>
</div>

<ul class="menu-inner">

    {{-- DASHBOARD --}}
    <li class="menu-item active">
        <a href="#" class="menu-link" onclick="return false;">
            <i class="menu-icon bx bxs-dashboard"></i>
            <div>Dashboard</div>
        </a>
    </li>


    {{-- PELATIHAN --}}
    <li class="menu-header">
        <span class="menu-header-text">📚 PELATIHAN</span>
    </li>

    <li class="menu-item {{ Request::is('dashboard/rekap*') ? 'active' : '' }}">
        <a href="{{ route('rekap-pelatihan.index') }}" class="menu-link">
        <i class="menu-icon bx bx-list-check"></i>
        <div>Rekap Pelatihan</div>
        </a>
    </li>

    <li class="menu-item {{ Request::routeIs('jadwal-pelatihan.index') ? 'active' : '' }}">
    <a href="{{ route('jadwal-pelatihan.index') }}" class="menu-link">
        <i class="menu-icon bx bx-calendar-event"></i>
        <div data-i18n="Jadwal Pelatihan">Jadwal Pelatihan</div>
    </a>
</li>

    <li class="menu-item {{ Request::routeIs('sertifikasi.*') ? 'active' : '' }}">
        <a href="{{ route('sertifikasi.index') }}" class="menu-link">
            <i class="menu-icon bx bx-award"></i>
            <div>Sertifikat</div>
        </a>
    </li>

    <li class="menu-item">
        <a href="#" class="menu-link" onclick="return false;">
            <i class="menu-icon bx bx-file"></i>
            <div>Laporan Pelatihan</div>
        </a>
    </li>


    {{-- MASTER DATA --}}
    <li class="menu-header">
        <span class="menu-header-text">📁 MASTER DATA</span>
    </li>

    <li class="menu-item {{ Request::is('dashboard/pegawai*') ? 'active' : '' }}">
    <a href="{{ route('pegawai.index') }}" class="menu-link">
        <i class="menu-icon bx bx-user"></i>
        <div>Data Pegawai</div>
    </a>
</li>

    <li class="menu-item">
        <a href="#" class="menu-link" onclick="return false;">
            <i class="menu-icon bx bx-building"></i>
            <div>Data Instansi</div>
        </a>
    </li>

    <li class="menu-item">
        <a href="#" class="menu-link" onclick="return false;">
            <i class="menu-icon bx bx-chalkboard"></i>
            <div>Data Pelatih</div>
        </a>
    </li>

    <li class="menu-item">
        <a href="#" class="menu-link" onclick="return false;">
            <i class="menu-icon bx bx-book-open"></i>
            <div>Materi Pelatihan</div>
        </a>
    </li>

    {{-- PENGATURAN --}}
    <li class="menu-header">
        <span class="menu-header-text">⚙️ PENGATURAN</span>
    </li>

    <li class="menu-item">
        <a href="#" class="menu-link" onclick="return false;">
            <i class="menu-icon bx bx-cog"></i>
            <div>Pengaturan Sistem</div>
        </a>
    </li>

    <li class="menu-item">
        <a href="#" class="menu-link" onclick="return false;">
            <i class="menu-icon bx bx-shield"></i>
            <div>Manajemen Role</div>
        </a>
    </li>

</ul>

{{-- SIDEBAR PROFILE --}}
@php
$pegawai = auth()->user()->pegawai ?? null;
@endphp

<div class="sidebar-profile">
    <div class="profile-left">
        <img src="{{ $pegawai && $pegawai->foto ? asset('storage/'.$pegawai->foto) : asset('admin/img/avatars/1.png') }}"
             class="profile-img"
             data-bs-toggle="modal"
             data-bs-target="#profileModal"
             style="cursor: pointer;">
        <div class="profile-info">
            <div class="profile-name">faizalanas</div>
            <div class="profile-role">Admin</div>
        </div>
    </div>
    <button class="logout-btn" data-bs-toggle="modal" data-bs-target="#profileModal" title="Profil">
        <i class="bx bx-user-circle"></i>
    </button>
</div>

</aside>

{{-- MODAL PROFILE --}}
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none; padding: 20px 24px;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bx bx-user-circle me-2"></i>Profil Akun
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 32px;">
                <img src="{{ $pegawai && $pegawai->foto ? asset('storage/'.$pegawai->foto) : asset('admin/img/avatars/1.png') }}"
                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #fed7aa; margin-bottom: 16px;">
                
                <h5 class="fw-bold mb-1" style="color: #4b3a2a;">faizalanas</h5>
                <p class="text-muted mb-4">Administrator</p>

                <hr style="margin: 24px 0;">

                <div class="alert" style="background: #fffbeb; border: none; color: #f97316; border-radius: 16px;">
                    <i class="bx bx-info-circle me-2"></i>
                    Mode Statis - Fungsi akan diaktifkan nanti
                </div>

                <button class="btn w-100" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none; padding: 12px; border-radius: 14px; font-weight: 600;" onclick="alert('Logout akan diaktifkan nanti')">
                    <i class="bx bx-log-out me-1"></i>Logout
                </button>
            </div>
        </div>
    </div>
</div>