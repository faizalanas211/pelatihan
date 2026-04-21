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

                    {{-- INSTANSI PENYELENGGARA --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">INSTANSI PENYELENGGARA <span class="text-danger">*</span></label>
                        <input type="text" name="instansi" class="form-control" placeholder="Masukkan instansi penyelenggara" value="{{ old('instansi') }}" required>
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
</script>

<style>
.btn-orange-outline {
    border: 1px solid #f97316;
    color: #f97316;
    background: transparent;
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
</style>
@endsection