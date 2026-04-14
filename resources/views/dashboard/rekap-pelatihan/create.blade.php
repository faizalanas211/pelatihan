@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('rekap-pelatihan.index') }}" style="color: #f97316; text-decoration: none;">Rekap Pelatihan</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Tambah Pelatihan
</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-people-fill" style="color: #f97316; font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1.6rem;">
                    Tambah Data Pelatihan
                </h3>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">Pilih peserta dan lengkapi detail pelatihan yang dilaksanakan</p>
            </div>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0" style="background: #fffbeb;">
                <h6 class="fw-bold mb-0" style="color: #5c4a3a; font-size: 1rem;">
                    <i class="bi bi-pencil-square me-2" style="color: #f97316;"></i>Formulir Data Pelatihan
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

                <form action="{{ route('rekap-pelatihan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-4">
                        {{-- SEKSI PESERTA --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PESERTA</label>
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

                        <hr class="my-3 text-muted opacity-25">

                        <div class="col-12">
                            <label class="form-label fw-bold" style="color: #b87a4a;">JENIS PELATIHAN</label>
                            <input type="text" name="jenis_pelatihan" class="form-control rounded-3 shadow-sm py-2" placeholder="Contoh: Teknis IT, Leadership, dll" value="{{ old('jenis_pelatihan') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TAHUN</label>
                            <select name="tahun_pelatihan" class="form-select rounded-3 shadow-sm py-2">
                                @for ($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ old('tahun_pelatihan') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">WAKTU PELAKSANAAN</label>
                            <input type="date" name="waktu_pelaksanaan" class="form-control rounded-3 shadow-sm py-2" value="{{ old('waktu_pelaksanaan') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">JP (JAM PELAJARAN)</label>
                            <div class="input-group">
                                {{-- REVISI 1: Hapus 'required' agar boleh kosong --}}
                                <input type="number" name="jp" class="form-control rounded-start-3 shadow-sm py-2" placeholder="Boleh kosong" value="{{ old('jp') }}">
                                <span class="input-group-text rounded-end-3" style="background: #fffbeb; color: #b87a4a; font-weight: 600;">Jam</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI PENYELENGGARA</label>
                            <input type="text" name="instansi" class="form-control rounded-3 shadow-sm py-2" placeholder="Nama instansi penyelenggara" value="{{ old('instansi') }}" required>
                        </div>

                        {{-- REVISI 2: Hapus input file sertifikat di sini, karena mentor minta per orang di halaman Show --}}
                        <div class="col-12 mt-2">
                            <div class="alert alert-warning border-0 rounded-4 py-3 shadow-sm d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>
                                    <strong class="d-block text-uppercase" style="font-size: 0.75rem;">Info Sertifikat</strong>
                                    <span class="small">File sertifikat diunggah <strong>secara individu</strong> melalui halaman detail (Show) setelah data disimpan.</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light rounded-4 px-4 border fw-semibold" style="color: #5c4a3a;">
                                    Batal
                                </a>
                                <button type="submit" class="btn rounded-4 px-5 shadow-sm fw-bold text-white" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none;">
                                    <i class="bi bi-save me-2"></i>SIMPAN DATA
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT DYNAMIC DROPDOWN --}}
<script>
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
                <div class="col-md-1">
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
    .btn-orange-outline { color: #f97316; border: 1px solid #f97316; background: transparent; transition: all 0.2s ease; }
    .btn-orange-outline:hover { background: #f97316; color: white; }
    .form-control:focus, .form-select:focus { border-color: #f97316 !important; box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1) !important; }
    .animate__fadeIn { animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection