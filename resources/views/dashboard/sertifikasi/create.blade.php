@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('sertifikasi.index') }}" style="color: #f97316; text-decoration: none;">Sertifikasi</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Tambah Sertifikasi
</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-award-fill" style="color: #f97316; font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1.6rem;">
                    Tambah Sertifikasi Kolektif
                </h3>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">Pilih master sertifikasi dan input data rombongan pegawai</p>
            </div>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0" style="background: #fffbeb;">
                <h6 class="fw-bold mb-0" style="color: #5c4a3a; font-size: 1rem;">
                    <i class="bi bi-pencil-square me-2" style="color: #f97316;"></i>Formulir Data Sertifikasi
                </h6>
            </div>
            <div class="card-body p-4 p-md-5 bg-white">
                @if ($errors->any())
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('sertifikasi.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        {{-- FILTER TAHUN MASTER --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">FILTER TAHUN MASTER</label>
                            <select id="filter-tahun" class="form-select rounded-3 shadow-sm py-2 border-warning">
                                <option value="all">-- Semua Tahun --</option>
                                @foreach($daftarTahun as $th)
                                    <option value="{{ $th }}">{{ $th }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SEKSI PILIH MASTER SERTIFIKASI --}}
                        <div class="col-md-8">
                            <label class="form-label fw-bold" style="color: #b87a4a;">JENIS SERTIFIKASI (MASTER DATA)</label>
                            <select name="master_pelatihan_id" id="master_pelatihan_id" class="form-select rounded-3 shadow-sm py-2" required>
                                <option value="" disabled selected>-- Pilih Jenis Sertifikasi --</option>
                                @foreach($masterSertifikasi as $m)
                                    <option value="{{ $m->id }}" 
                                            data-tahun="{{ $m->tahun }}" 
                                            class="opt-sertifikasi"
                                            {{ old('master_pelatihan_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->nama_pelatihan }} — ({{ $m->tahun }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TANGGAL PELAKSANAAN --}}
                        <div class="col-md-3">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TANGGAL MULAI</label>
                            <input type="date" name="tgl_terbit" class="form-control rounded-3 shadow-sm py-2" value="{{ old('tgl_terbit') }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TANGGAL SELESAI</label>
                            <input type="date" name="tanggal_selesai" class="form-control rounded-3 shadow-sm py-2" value="{{ old('tanggal_selesai') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI PENERBIT</label>
                            <input type="text" name="instansi" class="form-control rounded-3 shadow-sm py-2" placeholder="Contoh: BNSP / Cisco / Oracle" value="{{ old('instansi') }}" required>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        {{-- SEKSI PESERTA --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PEGAWAI (PESERTA)</label>
                                <button type="button" class="btn btn-sm btn-orange-outline rounded-3" onclick="tambahBaris()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Orang
                                </button>
                            </div>
                            
                            <div id="container-peserta">
                                <div class="row g-3 mb-3 baris-peserta align-items-end">
                                    <div class="col-md-11">
                                        <label class="small text-muted mb-1 text-uppercase">Pilih Nama Pegawai</label>
                                        <select name="peserta[]" class="form-select rounded-3 shadow-sm py-2" required>
                                            <option value="" disabled selected>-- Pilih Peserta --</option>
                                            @foreach($pegawais as $p)
                                                <option value="{{ $p->nip }}|{{ $p->nama }}">{{ $p->nama }} ({{ $p->nip }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-light rounded-3 w-100 disabled"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- INFO STATUS --}}
                        <div class="col-12 mt-2">
                            <div class="alert alert-warning border-0 rounded-4 py-3 shadow-sm d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>
                                    <strong class="d-block text-uppercase" style="font-size: 0.75rem;">Info Sertifikasi</strong>
                                    <span class="small">Pastikan masa berlaku sertifikat sesuai dengan tanggal selesai yang diinputkan. File scan sertifikat diunggah di menu detail.</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('sertifikasi.index') }}" class="btn btn-light rounded-4 px-4 border fw-semibold" style="color: #5c4a3a;">
                                    Batal
                                </a>
                                <button type="submit" class="btn rounded-4 px-5 shadow-sm fw-bold text-white" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none;">
                                    <i class="bi bi-save me-2"></i>SIMPAN KOLEKTIF
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    // FUNGSI FILTER TAHUN MASTER
    document.getElementById('filter-tahun').addEventListener('change', function() {
        const selectedTahun = this.value;
        const selectSertifikasi = document.getElementById('master_pelatihan_id');
        const options = selectSertifikasi.querySelectorAll('.opt-sertifikasi');

        selectSertifikasi.value = "";

        options.forEach(option => {
            const tahunOption = option.getAttribute('data-tahun');
            if (selectedTahun === 'all' || tahunOption === selectedTahun) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // FUNGSI TAMBAH BARIS PESERTA
    function tambahBaris() {
        const container = document.getElementById('container-peserta');
        const barisAsli = document.querySelector('.baris-peserta');
        const selectOptions = barisAsli.querySelector('select').innerHTML; 
        
        const html = `
            <div class="row g-3 mb-3 baris-peserta align-items-end animate__animated animate__fadeIn">
                <div class="col-md-11">
                    <select name="peserta[]" class="form-select rounded-3 shadow-sm py-2" required>
                        ${selectOptions}
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger rounded-3 w-100" onclick="hapusBaris(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function hapusBaris(btn) {
        btn.closest('.baris-peserta').remove();
    }
</script>

<style>
    #filter-tahun { cursor: pointer; background-color: #fffbeb; }
    .btn-orange-outline { color: #f97316; border: 1px solid #f97316; background: transparent; transition: all 0.2s ease; }
    .btn-orange-outline:hover { background: #f97316; color: white; }
    .form-control:focus, .form-select:focus { border-color: #f97316 !important; box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1) !important; }
    .animate__fadeIn { animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection