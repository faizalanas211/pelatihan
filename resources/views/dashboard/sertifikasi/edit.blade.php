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
        {{-- HEADER --}}
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%);">
                <i class="bi bi-pencil-square" style="color: #f97316; font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #5c4a3a;">Edit Data Sertifikasi</h3>
                <p class="text-muted mb-0 small">Perbarui informasi instansi atau jadwal pelaksanaan tiap pegawai</p>
            </div>
        </div>
        
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('sertifikasi.update', $sertifikasi->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        {{-- INFORMASI SERTIFIKASI --}}
                        <div class="col-md-7">
                            <label class="form-label fw-bold" style="color: #b87a4a;">NAMA KEGIATAN / JENIS SERTIFIKASI</label>
                            <input type="text" class="form-control rounded-3 py-2 shadow-sm bg-light" value="{{ $sertifikasi->jenis_sertifikasi }}" readonly>
                            <div class="form-text mt-1 italic"><i class="bi bi-info-circle"></i> Mengikuti Master Data (Read-only).</div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI PENERBIT <span class="text-danger">*</span></label>
                            <input type="text" name="instansi" class="form-control rounded-3 py-2 shadow-sm" value="{{ $sertifikasi->instansi_penerbit }}" required>
                        </div>

                        <hr class="my-2 opacity-25">

                        {{-- SEKSI PESERTA --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PEGAWAI & JADWAL INDIVIDUAL</label>
                                <button type="button" class="btn btn-sm btn-orange-outline rounded-3" onclick="tambahBaris()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Peserta
                                </button>
                            </div>

                            <div id="container-peserta">
                                @foreach($pesertaTerpilih as $index => $pt)
                                <div class="card border-0 shadow-sm mb-4 baris-peserta overflow-hidden" style="background: #f8f9fa; border: 1px solid #e9ecef !important; border-radius: 15px;">
                                    <div class="card-body p-4">
                                        {{-- INPUT PEGAWAI --}}
                                        <div class="mb-4">
                                            <label class="small fw-bold text-uppercase text-muted mb-2">Nama Pegawai <span class="text-danger">*</span></label>
                                            <select name="pegawai_id[]" class="form-select shadow-sm py-2" required>
                                                @foreach($pegawais as $peg)
                                                    <option value="{{ $peg->nip }}|{{ $peg->nama }}" {{ $pt->nip == $peg->nip ? 'selected' : '' }}>
                                                        {{ $peg->nama }} ({{ $peg->nip }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- DETAIL TANGGAL --}}
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="small fw-bold text-uppercase text-muted mb-2">Tanggal Mulai <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_mulai[]" class="form-control shadow-sm py-2" value="{{ $pt->tanggal_mulai }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small fw-bold text-uppercase text-muted mb-2">Tanggal Selesai <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_selesai[]" class="form-control shadow-sm py-2" value="{{ $pt->tanggal_selesai }}" required>
                                            </div>
                                        </div>

                                        {{-- ACTION HAPUS --}}
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus rounded-3 px-3" onclick="hapusBaris(this)">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="col-12 mt-4 d-flex justify-content-end gap-3 pt-4 border-top">
                            <a href="{{ route('sertifikasi.show', $sertifikasi->id) }}" class="btn btn-light rounded-4 px-4 border fw-semibold">Batal</a>
                            <button type="submit" class="btn rounded-4 px-5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none;">
                                <i class="bi bi-save me-2"></i>SIMPAN PERUBAHAN
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', updateHapusButton);

    function tambahBaris() {
        const container = document.getElementById('container-peserta');
        const rows = document.querySelectorAll('.baris-peserta');
        if (rows.length > 0) {
            const newRow = rows[0].cloneNode(true);
            
            // Reset input values
            newRow.querySelectorAll('input').forEach(i => i.value = '');
            newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            
            newRow.classList.add('animate__fadeIn');
            container.appendChild(newRow);
            updateHapusButton();
        }
    }

    function hapusBaris(btn) {
        const rows = document.querySelectorAll('.baris-peserta');
        if (rows.length > 1) {
            btn.closest('.baris-peserta').remove();
            updateHapusButton();
        } else {
            alert('Minimal harus ada satu peserta!');
        }
    }

    function updateHapusButton() {
        const rows = document.querySelectorAll('.baris-peserta');
        rows.forEach((row) => {
            const btn = row.querySelector('.btn-hapus');
            btn.style.display = rows.length === 1 ? 'none' : 'inline-block';
        });
    }
</script>

<style>
    .btn-orange-outline { color: #f97316; border: 1px solid #f97316; background: transparent; transition: all 0.2s; }
    .btn-orange-outline:hover { background: #f97316; color: white; }
    .form-control:focus, .form-select:focus { border-color: #f97316 !important; box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1) !important; }
    .bg-light { background-color: #f8f9fa !important; }
    .italic { font-style: italic; font-size: 0.8rem; }
    .animate__fadeIn { animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection