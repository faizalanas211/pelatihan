@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('tugas-belajar.index') }}" style="color: #f97316; text-decoration: none;">Rekap Tugas Belajar</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('tugas-belajar.show', $tubel->id) }}" style="color: #f97316; text-decoration: none;">Detail Tugas Belajar</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Edit Data Tugas Belajar
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-book-half" style="color: #f97316; font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Edit Data Tugas Belajar
                </h3>
                <p class="text-muted mb-0 mt-1">Edit peserta tugas belajar berdasarkan master data</p>
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

                <form action="{{ route('tugas-belajar.update', $tubel->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- FILTER TAHUN (BUKAN INPUT DATA) --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">FILTER TAHUN</label>

                        <select id="filterTahun" class="form-select" disabled>
                            <option selected>{{ $tubel->tahun }}</option>
                        </select>
                    </div>

                    {{-- MASTER TUBEL --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">MASTER TUGAS BELAJAR</label>
                        
                        <select name="master_pelatihan_id" class="form-select" disabled>
                            <option selected>
                                {{ $tubel->nama_pelatihan }} ({{ $tubel->tahun }})
                            </option>
                        </select>

                        {{-- kirim hidden biar tetap masuk ke request --}}
                        <input type="hidden" name="master_pelatihan_id" value="{{ $tubel->id }}">
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

                        @foreach($peserta as $row)

                        <div class="card border-0 shadow-sm mb-3 baris-peserta">
                            <div class="card-body p-3">
                                <input type="hidden" name="id[]" value="{{ $row->id }}">

                                {{-- Pegawai --}}
                                <div class="mb-3">
                                    <label class="small fw-semibold text-muted">Pegawai <span class="text-danger">*</span></label>
                                    <select name="pegawai_id[]" class="form-select" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach($pegawais as $p)
                                            <option value="{{ $p->id }}" {{ $p->id == $row->pegawai_id ? 'selected' : '' }}>
                                                {{ $p->nama }} ({{ $p->nip }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Detail --}}
                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_mulai[]" 
                                               class="form-control"
                                               value="{{ $row->tanggal_mulai }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small fw-semibold">Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai[]" 
                                               class="form-control"
                                               value="{{ $row->tanggal_selesai }}">
                                        <small class="text-muted">Kosongkan jika belum selesai</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status[]" class="form-select" required>
                                            <option value="belum_selesai" {{ $row->status == 'belum_selesai' ? 'selected' : '' }}>Belum Selesai</option>
                                            <option value="selesai" {{ $row->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                        </select>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBaris(this)">
                                        <i class="bi bi-trash me-1"></i> Hapus
                                    </button>
                                </div>

                            </div>
                        </div>

                        @endforeach

                    </div>

                    {{-- ACTION --}}
                    <div class="text-end mt-4">
                        <a href="{{ route('tugas-belajar.show', $tubel->id) }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn text-white" style="background: #f97316;">
                            <i class="bi bi-save me-1"></i> Simpan
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

    // reset input
    baris.querySelectorAll('input').forEach(i => i.value = '');
    baris.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

    container.appendChild(baris);
    updateHapusButton();
}

function hapusBaris(btn) {
    // HAPUS LANGSUNG TANPA CEK JUMLAH BARIS
    btn.closest('.baris-peserta').remove();
    updateHapusButton();
}

function updateHapusButton() {
    const rows = document.querySelectorAll('.baris-peserta');
    rows.forEach((row, index) => {
        const btn = row.querySelector('.btn-outline-danger');
        // Tombol hapus selalu muncul
        if(btn) {
            btn.style.display = 'inline-block';
        }
    });
}
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