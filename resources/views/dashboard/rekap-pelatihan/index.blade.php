@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Rekap Pelatihan
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%);">
                        <i class="bi bi-archive-fill fs-1" style="color: #f97316;"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            Rekap Pelatihan
                        </h1>
                        <p class="text-muted mb-0 mt-1">
                            <i class="bi bi-check-circle"></i> Daftar pelatihan yang telah selesai dilaksanakan
                        </p>
                    </div>
                </div>
            </div>
            <button class="btn rounded-4 px-4 shadow-sm" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none;" onclick="tambahPelatihan()">
                <i class="bi bi-plus-circle me-2"></i>Tambah Pelatihan
            </button>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    {{-- FILTER TAHUN & BULAN --}}
    <div class="col-12">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold" style="color: #b87a4a;">Tahun</label>
                <select class="form-select rounded-4 shadow-sm" id="filterTahun" style="background: #fffbeb; border-color: #fed7aa;">
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold" style="color: #b87a4a;">Bulan</label>
                <select class="form-select rounded-4 shadow-sm" id="filterBulan" style="background: #fffbeb; border-color: #fed7aa;">
                    <option value="all">Semua Bulan</option>
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold" style="color: #b87a4a;">Cari Pelatihan</label>
                <div class="input-group rounded-4 overflow-hidden shadow-sm">
                    <span class="input-group-text bg-white border-end-0" style="background: #fffbeb;">
                        <i class="bi bi-search" style="color: #f97316;"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Nama pelatihan..." style="background: #fffbeb;">
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn w-100 rounded-4" style="background: #fffbeb; color: #f97316; border: 1px solid #fed7aa;" onclick="resetFilter()">
                    <i class="bi bi-arrow-repeat me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>

    {{-- LIST PELATIHAN (STATIS) --}}
    <div class="col-12 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color: #5c4a3a;">
                <i class="bi bi-list-check me-2" style="color: #f97316;"></i>
                Daftar Pelatihan Selesai
            </h5>
            <small class="text-muted" id="totalData">Menampilkan 5 pelatihan</small>
        </div>

        <div id="pelatihanList">
            {{-- CARD 1 --}}
            <div class="card rounded-4 border-0 shadow-sm mb-3 pelatihan-card" data-nama="Leadership Training" data-tahun="2024" data-bulan="1">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #f9731620, #ffedd5);">
                                    <i class="bi bi-trophy-fill fs-3" style="color: #f97316;"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">Leadership Training</h4>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">
                                        <i class="bi bi-check-circle-fill me-1 small"></i> Selesai
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tanggal</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">15 - 17 Januari 2024</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tempat</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">Ruang Serbaguna</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-4 mt-md-0">
                            <button class="btn rounded-4 px-4 py-2" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none;" onclick="lihatPeserta(1)">
                                <i class="bi bi-eye me-2"></i>Lihat Peserta
                            </button>
                            <div class="mt-2">
                                <small class="text-muted"><i class="bi bi-people-fill me-1"></i> 12 Peserta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2 --}}
            <div class="card rounded-4 border-0 shadow-sm mb-3 pelatihan-card" data-nama="Pelatihan Teknis IT" data-tahun="2024" data-bulan="2">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #f9731620, #ffedd5);">
                                    <i class="bi bi-laptop-fill fs-3" style="color: #f97316;"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">Pelatihan Teknis IT</h4>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">
                                        <i class="bi bi-check-circle-fill me-1 small"></i> Selesai
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tanggal</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">10 - 12 Februari 2024</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tempat</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">Lab Komputer</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-4 mt-md-0">
                            <button class="btn rounded-4 px-4 py-2" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none;" onclick="lihatPeserta(2)">
                                <i class="bi bi-eye me-2"></i>Lihat Peserta
                            </button>
                            <div class="mt-2">
                                <small class="text-muted"><i class="bi bi-people-fill me-1"></i> 8 Peserta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3 --}}
            <div class="card rounded-4 border-0 shadow-sm mb-3 pelatihan-card" data-nama="Manajerial Skill" data-tahun="2024" data-bulan="3">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #f9731620, #ffedd5);">
                                    <i class="bi bi-bar-chart-steps fs-3" style="color: #f97316;"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">Manajerial Skill</h4>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">
                                        <i class="bi bi-check-circle-fill me-1 small"></i> Selesai
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tanggal</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">5 - 7 Maret 2024</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tempat</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">Ruang Rapat Utama</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-4 mt-md-0">
                            <button class="btn rounded-4 px-4 py-2" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none;" onclick="lihatPeserta(3)">
                                <i class="bi bi-eye me-2"></i>Lihat Peserta
                            </button>
                            <div class="mt-2">
                                <small class="text-muted"><i class="bi bi-people-fill me-1"></i> 15 Peserta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 4 --}}
            <div class="card rounded-4 border-0 shadow-sm mb-3 pelatihan-card" data-nama="Public Speaking" data-tahun="2023" data-bulan="11">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #f9731620, #ffedd5);">
                                    <i class="bi bi-mic-fill fs-3" style="color: #f97316;"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">Public Speaking</h4>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">
                                        <i class="bi bi-check-circle-fill me-1 small"></i> Selesai
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tanggal</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">20 - 22 November 2023</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tempat</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">Aula Lantai 2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-4 mt-md-0">
                            <button class="btn rounded-4 px-4 py-2" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none;" onclick="lihatPeserta(4)">
                                <i class="bi bi-eye me-2"></i>Lihat Peserta
                            </button>
                            <div class="mt-2">
                                <small class="text-muted"><i class="bi bi-people-fill me-1"></i> 20 Peserta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 5 --}}
            <div class="card rounded-4 border-0 shadow-sm mb-3 pelatihan-card" data-nama="Digital Marketing" data-tahun="2024" data-bulan="4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #f9731620, #ffedd5);">
                                    <i class="bi bi-megaphone-fill fs-3" style="color: #f97316;"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">Digital Marketing</h4>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">
                                        <i class="bi bi-check-circle-fill me-1 small"></i> Selesai
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tanggal</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">1 - 3 April 2024</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill" style="color: #f97316; width: 24px;"></i>
                                        <div>
                                            <small class="text-muted d-block">Tempat</small>
                                            <span class="fw-semibold" style="color: #5c4a3a;">Lab Multimedia</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-4 mt-md-0">
                            <button class="btn rounded-4 px-4 py-2" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none;" onclick="lihatPeserta(5)">
                                <i class="bi bi-eye me-2"></i>Lihat Peserta
                            </button>
                            <div class="mt-2">
                                <small class="text-muted"><i class="bi bi-people-fill me-1"></i> 10 Peserta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function tambahPelatihan() {
        Swal.fire({
            title: 'Tambah Pelatihan',
            text: 'Fitur ini akan segera hadir',
            icon: 'info',
            confirmButtonColor: '#f97316'
        });
    }

    function lihatPeserta(id) {
        Swal.fire({
            title: 'Lihat Peserta',
            text: 'Menampilkan daftar peserta pelatihan',
            icon: 'info',
            confirmButtonColor: '#f97316'
        });
    }

    function resetFilter() {
        document.getElementById('filterTahun').value = '2024';
        document.getElementById('filterBulan').value = 'all';
        document.getElementById('searchInput').value = '';
        filterData();
    }

    function filterData() {
        const tahun = document.getElementById('filterTahun').value;
        const bulan = document.getElementById('filterBulan').value;
        const search = document.getElementById('searchInput').value.toLowerCase();
        
        const cards = document.querySelectorAll('.pelatihan-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            let show = true;
            
            const cardTahun = card.getAttribute('data-tahun');
            const cardBulan = card.getAttribute('data-bulan');
            const cardNama = card.getAttribute('data-nama').toLowerCase();
            
            if (tahun !== cardTahun) show = false;
            if (bulan !== 'all' && bulan !== cardBulan) show = false;
            if (search && !cardNama.includes(search)) show = false;
            
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        
        document.getElementById('totalData').innerText = `Menampilkan ${visibleCount} pelatihan`;
    }
    
    document.getElementById('filterTahun').addEventListener('change', filterData);
    document.getElementById('filterBulan').addEventListener('change', filterData);
    document.getElementById('searchInput').addEventListener('keyup', filterData);
    
    filterData();
</script>

<style>
    .pelatihan-card {
        transition: all 0.3s ease;
        background: white;
        border: 1px solid #fef3c7;
    }
    
    .pelatihan-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.12) !important;
        border-color: #fed7aa;
    }
</style>

@endsection