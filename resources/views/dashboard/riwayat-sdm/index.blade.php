@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Riwayat Pengembangan SDM
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3"
                     style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%);
                            width: 65px; height: 65px;
                            display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-mortarboard" style="color: #f97316; font-size: 1.8rem;"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"
                        style="background: linear-gradient(135deg, #f97316, #f59e0b);
                               -webkit-background-clip: text;
                               -webkit-text-fill-color: transparent;">
                        Riwayat Pengembangan SDM
                    </h3>
                    <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">
                        Rekap pelatihan, sertifikasi, dan tugas belajar pegawai
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-3 mb-2"
             style="height: 3px;
                    background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24);
                    border-radius: 2px;">
        </div>
    </div>

    
    {{-- TABLE --}}
    <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <livewire:riwayat-sdm-table />

        </div>
    </div>
</div>

    
</div>

<style>
    .btn-outline-orange {
    border: 1px solid #f97316;
    color: #f97316;
}

.btn-outline-orange:hover {
    background-color: #f97316;
    color: #fff;
}

.pagination .page-link {
    color: #f97316;
    border-radius: 8px;
}

.pagination .page-item.active .page-link {
    background-color: #f97316;
    border-color: #f97316;
    color: #fff;
}

.pagination .page-link:hover {
    background-color: #fed7aa;
    color: #f97316;
}

.btn-orange {
    background: linear-gradient(135deg, #f97316, #f59e0b);
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 10px;
    padding: 8px 16px;
    box-shadow: 0 4px 14px rgba(249,115,22,.35);
    transition: all 0.25s ease;
}

/* 🔥 Hover effect */
.btn-orange:hover {
    background: linear-gradient(135deg, #ea580c, #d97706);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249,115,22,.45);
    color: #fff;
}

/* klik (active) */
.btn-orange:active {
    transform: scale(0.97);
    box-shadow: 0 3px 10px rgba(249,115,22,.3);
}

/* icon spacing + animasi */
.btn-export i {
    margin-right: 6px;
    transition: transform 0.2s ease;
}

/* icon ikut gerak pas hover */
.btn-export:hover i {
    transform: scale(1.1) rotate(-5deg);
}
</style>
@endsection