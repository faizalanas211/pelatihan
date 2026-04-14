@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('sertifikasi.index') }}" style="color: #f97316; text-decoration: none;">Sertifikasi</a></li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">Tambah Data</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-award-fill" style="color: #f97316; font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1.6rem;">Tambah Sertifikasi Kolektif</h3>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">Input data sertifikat rombongan pegawai</p>
            </div>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0" style="background: #fffbeb;">
                <h6 class="fw-bold mb-0" style="color: #5c4a3a;"><i class="bi bi-pencil-square me-2" style="color: #f97316;"></i>Formulir Data</h6>
            </div>
            <div class="card-body p-4 p-md-5 bg-white">
                <form action="{{ route('sertifikasi.store') }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        {{-- DAFTAR PEGAWAI --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PEGAWAI (PESERTA)</label>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-3" onclick="tambahBaris()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Orang
                                </button>
                            </div>
                            <div id="container-peserta">
                                <div class="row g-3 mb-3 baris-peserta align-items-end">
                                    <div class="col-md-11">
                                        <select name="peserta[]" class="form-select rounded-3 shadow-sm py-2" required>
                                            <option value="" disabled selected>-- Pilih Pegawai --</option>
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

                        <hr class="my-3 opacity-25">

                        <div class="col-12">
                            <label class="form-label fw-bold" style="color: #b87a4a;">JENIS SERTIFIKASI / KEAHLIAN</label>
                            <input type="text" name="jenis_sertifikasi" class="form-control rounded-3 shadow-sm py-2" placeholder="Contoh: Sertifikasi Mikrotik MTCNA" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TANGGAL TERBIT</label>
                            <input type="date" name="tgl_terbit" class="form-control rounded-3 shadow-sm py-2" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI PENERBIT</label>
                            <input type="text" name="instansi" class="form-control rounded-3 shadow-sm py-2" placeholder="Contoh: BNSP / Cisco / Oracle" required>
                        </div>

                        <div class="col-12 text-end mt-4 pt-4 border-top">
                            <button type="submit" class="btn rounded-4 px-5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none;">
                                SIMPAN KOLEKTIF
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function tambahBaris() {
        const container = document.getElementById('container-peserta');
        const options = document.querySelector('.baris-peserta select').innerHTML;
        const html = `
            <div class="row g-3 mb-3 baris-peserta align-items-end">
                <div class="col-md-11">
                    <select name="peserta[]" class="form-select rounded-3 shadow-sm py-2" required>
                        ${options}
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger rounded-3 w-100" onclick="this.closest('.baris-peserta').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
</script>
@endsection