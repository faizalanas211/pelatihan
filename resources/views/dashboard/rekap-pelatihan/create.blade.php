@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('rekap-pelatihan.index') }}" style="color: #f97316; text-decoration: none;">Rekap Pelatihan</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Tambah Data Pelatihan
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-archive-fill" style="color: #f97316; font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Tambah Data Pelatihan
                </h3>
                <p class="text-muted mb-0 mt-1">Input peserta pelatihan berdasarkan master data</p>
            </div>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24); border-radius: 2px;"></div>
    </div>

    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">

                @if ($errors->any())
                    <div class="alert alert-danger rounded-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success rounded-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger rounded-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('rekap-pelatihan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- FILTER TAHUN (BUKAN INPUT DATA) --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">FILTER TAHUN</label>

                        <select id="filterTahun" class="form-select">
                            <option value="">-- Semua Tahun --</option>
                            @for($i = date('Y'); $i >= 2020; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- MASTER PELATIHAN --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">MASTER PELATIHAN <span class="text-danger">*</span></label>
                        
                        <select name="master_pelatihan_id" id="masterPelatihan" class="form-select" required>
                            <option value="">-- Pilih Pelatihan --</option>

                            @foreach($masterPelatihan as $m)
                                <option value="{{ $m->id }}" 
                                    data-tahun="{{ $m->tahun }}"
                                    {{ $selectedMaster == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_pelatihan }} ({{ $m->tahun }}) — {{ $m->jp }} JP
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- TOMBOL IMPORT EXCEL --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="fw-bold">IMPORT EXCEL</label>
                            <button type="button" class="btn btn-sm" style="background: #f97316; color: white;" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                                <i class="bi bi-file-excel me-1"></i> Import Excel
                            </button>
                        </div>
                        <small class="text-muted">Gunakan tombol di bawah untuk menambah peserta secara manual</small>
                    </div>

                    <hr>

                    {{-- HEADER PESERTA --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="fw-bold mb-0">DATA PESERTA</label>
                        <button type="button" class="btn btn-sm btn-orange-outline" onclick="tambahBaris()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Peserta
                        </button>
                    </div>

                    <div id="container-peserta">

                        <div class="card border-0 shadow-sm mb-3 baris-peserta">
                            <div class="card-body p-3">

                                {{-- BARIS 1: PEGAWAI --}}
                                <div class="mb-3">
                                    <label class="small fw-semibold text-muted">Pegawai <span class="text-danger">*</span></label>
                                    <select name="pegawai_id[]" class="form-select" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach($pegawais as $p)
                                            <option value="{{ $p->nip }}|{{ $p->nama }}">
                                                {{ $p->nama }} ({{ $p->nip }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- BARIS 2: DETAIL --}}
                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="small fw-semibold text-muted">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_mulai[]" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small fw-semibold text-muted">Tanggal Selesai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_selesai[]" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small fw-semibold text-muted">Upload Sertifikat</label>
                                        <input type="file" name="file_sertifikat[]" class="form-control" accept="application/pdf">
                                    </div>

                                </div>

                                {{-- ACTION --}}
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBaris(this)">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- ACTION --}}
                    <div class="text-end mt-4">
                        <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn text-white" style="background: #f97316;">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form action="{{ route('rekap-pelatihan.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="master_pelatihan_id" id="import_master_id">

                <div class="modal-header border-0" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%);">
                    <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
                        <i class="bi bi-file-excel me-2" style="color: #f97316;"></i>
                        Import Peserta dari Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="opacity: 1; margin: 0;"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info rounded-3 small">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Pastikan Anda sudah memilih MASTER PELATIHAN terlebih dahulu.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Download Template Excel</label>
                        <div>
                            <a href="{{ route('rekap-pelatihan.download-template') }}" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="bi bi-download me-1"></i> Download Template
                            </a>
                        </div>
                        <small class="text-muted">Format: NIP | Nama | Tanggal Mulai | Tanggal Selesai</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File Excel</label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">Maksimal 5MB, format .xlsx atau .xls</small>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" style="background: #f97316; color: white;">
                        <i class="bi bi-upload me-1"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    updateHapusButton();
});

function tambahBaris() {
    const container = document.getElementById('container-peserta');
    const baris = document.querySelector('.baris-peserta').cloneNode(true);

    baris.querySelectorAll('input').forEach(i => i.value = '');
    baris.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

    container.appendChild(baris);
    updateHapusButton();
}

function hapusBaris(btn) {
    const rows = document.querySelectorAll('.baris-peserta');
    if(rows.length > 1){
        btn.closest('.baris-peserta').remove();
    }
    updateHapusButton();
}

function updateHapusButton() {
    const rows = document.querySelectorAll('.baris-peserta');
    rows.forEach((row, index) => {
        const btn = row.querySelector('.btn-outline-danger');
        btn.style.display = rows.length === 1 ? 'none' : 'inline-block';
    });
}

document.getElementById('filterTahun').addEventListener('change', function () {
    let tahun = this.value;
    let options = document.querySelectorAll('#masterPelatihan option');

    options.forEach(option => {
        let optTahun = option.getAttribute('data-tahun');

        if (!tahun || optTahun === tahun || option.value === "") {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });

    // reset pilihan
    document.getElementById('masterPelatihan').value = '';
});

// Saat modal akan dibuka, isi hidden field dengan data dari form utama
document.getElementById('modalImportExcel').addEventListener('show.bs.modal', function () {
    const masterId = document.querySelector('[name="master_pelatihan_id"]').value;
    
    document.getElementById('import_master_id').value = masterId || '';
    
    // Validasi jika belum dipilih
    if (!masterId) {
        alert('Silakan pilih MASTER PELATIHAN terlebih dahulu!');
        var modal = bootstrap.Modal.getInstance(document.getElementById('modalImportExcel'));
        if (modal) modal.hide();
        return;
    }
});
</script>

<style>
.btn-orange-outline {
    border: 1px solid #f97316;
    color: #f97316;
    background: transparent;
    transition: all 0.2s;
}
.btn-orange-outline:hover {
    background: #f97316;
    color: white;
}
.btn-outline-danger {
    border: 1px solid #dc3545;
    color: #dc3545;
    background: transparent;
}
.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
}
.form-control:focus, .form-select:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 0.2rem rgba(249, 115, 22, 0.25);
}

/* Hilangkan efek hover pada tombol close */
.btn-close {
    opacity: 1;
    transition: none;
    margin: 0 !important;
}
.btn-close:hover {
    opacity: 1;
    background-color: transparent;
    transform: none;
}
</style>
@endsection