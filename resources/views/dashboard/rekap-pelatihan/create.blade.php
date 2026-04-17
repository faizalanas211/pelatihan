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
                <i class="bi bi-journal-plus" style="color: #f97316; font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Tambah Data Pelatihan
                </h3>
                <p class="text-muted mb-0 mt-1">Input data peserta pelatihan berdasarkan master data yang tersedia</p>
            </div>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24); border-radius: 2px;"></div>
    </div>

    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">

                @if ($errors->any())
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('rekap-pelatihan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        {{-- FILTER TAHUN --}}
                        <div class="col-md-4 mb-4">
                            <label class="form-label fw-bold">FILTER TAHUN MASTER <span class="text-danger">*</span></label>
                            <select id="filterTahun" class="form-select shadow-sm">
                                <option value="">-- Semua Tahun --</option>
                                @foreach($daftarTahun as $th)
                                    <option value="{{ $th }}">{{ $th }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- MASTER PELATIHAN --}}
                        <div class="col-md-8 mb-4">
                            <label class="form-label fw-bold">JENIS PELATIHAN (MASTER) <span class="text-danger">*</span></label>
                            <select name="master_pelatihan_id" id="masterPelatihan" class="form-select shadow-sm" required>
                                <option value="">-- Pilih Jenis Pelatihan --</option>
                                @foreach($masterPelatihan as $m)
                                    <option value="{{ $m->id }}" 
                                        data-tahun="{{ $m->tahun }}"
                                        {{ old('master_pelatihan_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->nama_pelatihan }} ({{ $m->tahun }}) — {{ $m->jp }} JP
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mb-4">
                            <label class="form-label fw-bold">INSTANSI PENYELENGGARA <span class="text-danger">*</span></label>
                            <input type="text" name="instansi" class="form-control shadow-sm" placeholder="Masukkan nama instansi penyelenggara" value="{{ old('instansi') }}" required>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    {{-- HEADER PESERTA --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <label class="fw-bold mb-0" style="color: #b87a4a;">DATA PESERTA & WAKTU PELAKSANAAN</label>
                        <button type="button" class="btn btn-sm btn-orange-outline rounded-3" onclick="tambahBaris()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Peserta
                        </button>
                    </div>

                    <div id="container-peserta">
                        {{-- BARIS INPUT DINAMIS --}}
                        <div class="card border-0 shadow-sm mb-4 baris-peserta overflow-hidden" style="background: #f8f9fa; border: 1px solid #e9ecef !important; border-radius: 15px;">
                            <div class="card-body p-4">
                                
                                {{-- INPUT PEGAWAI --}}
                                <div class="mb-4">
                                    <label class="small fw-bold text-uppercase text-muted mb-2">Pilih Pegawai <span class="text-danger">*</span></label>
                                    <select name="pegawai_id[]" class="form-select shadow-sm py-2" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach($pegawais as $p)
                                            <option value="{{ $p->nip }}|{{ $p->nama }}">
                                                {{ $p->nama }} ({{ $p->nip }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- DETAIL TANGGAL & FILE PER PESERTA --}}
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="small fw-bold text-uppercase text-muted mb-2">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_mulai[]" class="form-control shadow-sm py-2" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small fw-bold text-uppercase text-muted mb-2">Tanggal Selesai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_selesai[]" class="form-control shadow-sm py-2" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small fw-bold text-uppercase text-muted mb-2">Upload Sertifikat</label>
                                        <input type="file" name="file_sertifikat[]" class="form-control shadow-sm py-2" accept="application/pdf">
                                    </div>
                                </div>

                                {{-- ACTION HAPUS --}}
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-hapus rounded-3 px-3" onclick="hapusBaris(this)">
                                        <i class="bi bi-trash me-1"></i> Hapus Peserta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="text-end mt-5">
                        <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light px-4 rounded-3 border fw-semibold me-2">Batal</a>
                        <button type="submit" class="btn text-white px-5 shadow-sm rounded-3 fw-bold" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none;">
                            <i class="bi bi-save me-2"></i> SIMPAN REKAP
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    updateHapusButton();
});

function tambahBaris() {
    const container = document.getElementById('container-peserta');
    const rows = document.querySelectorAll('.baris-peserta');
    const baris = rows[0].cloneNode(true);

    // Reset semua input di baris baru
    baris.querySelectorAll('input').forEach(i => i.value = '');
    baris.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

    // Tambahkan efek animasi sederhana
    baris.style.opacity = '0';
    container.appendChild(baris);
    
    setTimeout(() => {
        baris.style.transition = 'opacity 0.3s ease';
        baris.style.opacity = '1';
    }, 10);

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
    rows.forEach((row) => {
        const btn = row.querySelector('.btn-hapus');
        btn.style.display = rows.length === 1 ? 'none' : 'inline-block';
    });
}

// Logika Filter Tahun
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

    document.getElementById('masterPelatihan').value = '';
});
</script>

<style>
    .btn-orange-outline { border: 1px solid #f97316; color: #f97316; font-weight: 500; transition: all 0.2s; }
    .btn-orange-outline:hover { background: #f97316; color: white; }
    .form-control:focus, .form-select:focus { border-color: #f97316; box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1); }
    .card { transition: transform 0.2s ease; }
</style>
@endsection