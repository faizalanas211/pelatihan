@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('sertifikasi.index') }}" style="color: #f97316; text-decoration: none;">Sertifikasi</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Edit Sertifikasi
</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%);">
                <i class="bi bi-pencil-square" style="color: #f97316; font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #5c4a3a;">Edit Data Sertifikasi</h3>
                <p class="text-muted mb-0 small">Perbarui informasi pelaksanaan atau daftar peserta</p>
            </div>
        </div>
        
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('sertifikasi.update', $sertifikasi->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        {{-- SEKSI PESERTA --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PESERTA SERTIFIKASI</label>
                                <button type="button" class="btn btn-sm btn-orange-outline rounded-3" onclick="tambahBaris()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Orang
                                </button>
                            </div>

                            <div id="container-peserta">
                                @foreach($pesertaTerpilih as $index => $pt)
                                <div class="row g-3 mb-3 baris-peserta align-items-end animate__animated animate__fadeIn">
                                    <div class="col-md-11">
                                        @if($index == 0) <label class="small text-muted mb-1 text-uppercase">Pilih Nama Pegawai</label> @endif
                                        <select name="peserta[]" class="form-select rounded-3 shadow-sm py-2" required>
                                            @foreach($pegawais as $peg)
                                                <option value="{{ $peg->nip }}|{{ $peg->nama }}" 
                                                    {{ $pt->nip == $peg->nip ? 'selected' : '' }}>
                                                    {{ $peg->nama }} ({{ $peg->nip }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-outline-danger w-100 rounded-3 shadow-sm" onclick="hapusBaris(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-3 opacity-25">

                        {{-- INFORMASI SERTIFIKASI --}}
                        <div class="col-12">
                            <label class="form-label fw-bold" style="color: #b87a4a;">NAMA KEGIATAN / JENIS SERTIFIKASI</label>
                            <input type="text" name="jenis_sertifikasi" class="form-control rounded-3 py-2 shadow-sm bg-light" value="{{ $sertifikasi->jenis_sertifikasi }}" readonly>
                            <div class="form-text mt-1 italic"><i class="bi bi-info-circle"></i> Nama sertifikasi mengikuti Master Data (Read-only).</div>
                        </div>

                        {{-- BARIS SEJAJAR: 4 KOLOM --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TAHUN</label>
                            <input type="number" class="form-control rounded-3 py-2 shadow-sm bg-light" value="{{ \Carbon\Carbon::parse($sertifikasi->tgl_terbit)->format('Y') }}" readonly>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TANGGAL MULAI</label>
                            <input type="date" name="tgl_terbit" class="form-control rounded-3 py-2 shadow-sm" value="{{ $sertifikasi->tgl_terbit }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TANGGAL SELESAI</label>
                            <input type="date" name="tanggal_selesai" class="form-control rounded-3 py-2 shadow-sm" value="{{ $sertifikasi->tanggal_selesai }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI</label>
                            <input type="text" name="instansi" class="form-control rounded-3 py-2 shadow-sm" value="{{ $sertifikasi->instansi_penerbit }}" required>
                        </div>

                        {{-- INFO STATUS --}}
                        <div class="col-12">
                             <div class="p-3 rounded-4 bg-light border border-dashed d-flex align-items-center justify-content-center gap-3">
                                 <span class="text-muted small text-uppercase fw-bold">Status Saat Ini:</span>
                                 @if($sertifikasi->status == 'selesai')
                                     <span class="badge bg-success rounded-pill px-4 py-2 shadow-sm text-uppercase">Selesai</span>
                                 @elseif($sertifikasi->status == 'berlangsung')
                                     <span class="badge bg-warning text-dark rounded-pill px-4 py-2 shadow-sm text-uppercase">Berlangsung</span>
                                 @else
                                     <span class="badge bg-info text-white rounded-pill px-4 py-2 shadow-sm text-uppercase">Mendatang</span>
                                 @endif
                                 <div class="vr mx-2"></div>
                                 <small class="text-muted italic">Status akan terupdate otomatis berdasarkan rentang tanggal yang baru.</small>
                             </div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="col-12 mt-5 d-flex justify-content-end gap-3 pt-4 border-top">
                            <a href="{{ route('sertifikasi.show', $sertifikasi->id) }}" class="btn btn-light rounded-4 px-4 border fw-semibold">Batal</a>
                            <button type="submit" class="btn rounded-4 px-5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none;">
                                <i class="bi bi-save me-2"></i>UPDATE DATA
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
        const rows = document.querySelectorAll('.baris-peserta');
        
        if (rows.length > 0) {
            const newRow = rows[0].cloneNode(true);
            const label = newRow.querySelector('label');
            if (label) label.remove();
            const select = newRow.querySelector('select');
            select.selectedIndex = 0;
            const btnHapus = newRow.querySelector('button');
            btnHapus.disabled = false;
            btnHapus.classList.remove('disabled');
            newRow.classList.add('animate__fadeIn');
            container.appendChild(newRow);
        }
    }

    function hapusBaris(btn) {
        const rows = document.querySelectorAll('.baris-peserta');
        if (rows.length > 1) {
            btn.closest('.baris-peserta').remove();
        } else {
            alert('Minimal harus ada satu peserta!');
        }
    }
</script>

<style>
    /* CSS SAMAIN PERSIS DENGAN REKAP PELATIHAN */
    .btn-orange-outline { color: #f97316; border: 1px solid #f97316; background: transparent; transition: all 0.2s; }
    .btn-orange-outline:hover { background: #f97316; color: white; }
    .form-control:focus, .form-select:focus { border-color: #f97316 !important; box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1) !important; }
    .bg-light { background-color: #f8f9fa !important; }
    .italic { font-style: italic; font-size: 0.8rem; }
    
    /* Tambahan agar identik */
    .form-label {
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
</style>
@endsection