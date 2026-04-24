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
                <p class="text-muted mb-0 mt-1">Input peserta tugas belajar berdasarkan master data</p>
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

                                    <div class="col-md-3">
                                        <label class="small">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_mulai[]" 
                                               class="form-control"
                                               value="{{ $row->tanggal_mulai }}" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="small">Tanggal Selesai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_selesai[]" 
                                               class="form-control"
                                               value="{{ $row->tanggal_selesai }}" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="small">No SK</label>
                                        <input type="text" name="no_sk[]" 
                                               class="form-control"
                                               value="{{ $row->no_sk_tubel }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="small">Upload SK</label>
                                        <input type="file" name="file_sk[]" class="form-control" accept="application/pdf">

                                        @if($row->file_sk_tubel)
                                            <div class="mt-2 file-existing d-flex align-items-center gap-3">
                                                <a href="{{ asset('storage/'.$row->file_sk_tubel) }}" 
                                                target="_blank" 
                                                class="text-decoration-none fw-semibold"
                                                style="color:#16a34a;">
                                                    Lihat file
                                                </a>

                                                {{-- ✅ checkbox hapus --}}
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                        type="checkbox" 
                                                        name="hapus_file[]" 
                                                        value="{{ $row->id }}" 
                                                        id="hapus{{ $row->id }}">
                                                    <label class="form-check-label text-danger small" for="hapus{{ $row->id }}">
                                                        Hapus file
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBaris(this)">
                                        Hapus
                                    </button>
                                </div>

                            </div>
                        </div>

                        @endforeach

                    </div>

                    {{-- ACTION --}}
                    <div class="text-end mt-4">
                        <a href="{{ route('tugas-belajar.index') }}" class="btn btn-light">Batal</a>
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

    // reset input
    baris.querySelectorAll('input').forEach(i => i.value = '');
    baris.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

    // ❗ hapus preview file lama
    baris.querySelectorAll('.file-existing').forEach(el => el.remove());

    container.appendChild(baris);
    updateHapusButton();
}

function hapusBaris(btn) {
    // ✅ PERUBAHAN: HAPUS LANGSUNG TANPA CEK JUMLAH BARIS
    // BISA HAPUS SEMUA PESERTA TERMASUK YANG TERAKHIR
    btn.closest('.baris-peserta').remove();
    updateHapusButton();
}

function updateHapusButton() {
    const rows = document.querySelectorAll('.baris-peserta');
    rows.forEach((row, index) => {
        const btn = row.querySelector('.btn-outline-danger');
        // ✅ Tombol hapus selalu muncul
        if(btn) {
            btn.style.display = 'inline-block';
        }
    });
}

document.getElementById('filterTahun').addEventListener('change', function () {
    let tahun = this.value;
    let options = document.querySelectorAll('#masterTubel option');

    options.forEach(option => {
        let optTahun = option.getAttribute('data-tahun');

        if (!tahun || optTahun === tahun) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });

    // reset pilihan
    document.getElementById('masterTubel').value = '';
});
</script>

<style>
.btn-orange-outline {
    border: 1px solid #f97316;
    color: #f97316;
}
.btn-orange-outline:hover {
    background: #f97316;
    color: white;
}
</style>
@endsection