@extends('layouts.admin')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 mb-3">
            <h3 class="fw-bold mb-0" style="color: #f97316;">Edit Data Pelatihan</h3>
        </div>
        
        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('rekap-pelatihan.update', $pelatihan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        {{-- SEKSI PESERTA --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PESERTA</label>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-3" onclick="tambahBaris()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Orang
                                </button>
                            </div>

                            <div id="container-peserta">
                                @foreach($pesertaTerpilih as $pt)
                                <div class="row g-3 mb-3 baris-peserta align-items-end">
                                    <div class="col-md-11">
                                        <label class="small text-muted mb-1 text-uppercase">Pilih Nama Pegawai</label>
                                        <select name="peserta[]" class="form-select rounded-3 shadow-sm py-2" required>
                                            @foreach($pegawais as $peg)
                                                <option value="{{ $peg->nip }}|{{ $peg->nama }}" 
                                                    {{ $pt->nip == $peg->nip ? 'selected' : '' }}>
                                                    {{ $peg->nama }} ({{ $peg->nip }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger w-100 rounded-3 shadow-sm" onclick="hapusBaris(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-3 opacity-25">

                        {{-- INFORMASI PELATIHAN --}}
                        <div class="col-12">
                            <label class="form-label fw-bold" style="color: #b87a4a;">JENIS PELATIHAN / NAMA KEGIATAN</label>
                            <input type="text" name="jenis_pelatihan" class="form-control rounded-3 py-2 shadow-sm" value="{{ $pelatihan->jenis_pelatihan }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TAHUN</label>
                            <input type="number" name="tahun_pelatihan" class="form-control rounded-3 py-2 shadow-sm" value="{{ $pelatihan->tahun }}" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">WAKTU PELAKSANAAN</label>
                            <input type="date" name="waktu_pelaksanaan" class="form-control rounded-3 py-2 shadow-sm" value="{{ $pelatihan->waktu_pelaksanaan }}" required>
                            <div class="form-text mt-2 text-muted">
                                <i class="bi bi-info-circle me-1"></i> Status akan otomatis disesuaikan (Mendatang/Berlangsung/Selesai) berdasarkan tanggal ini.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI PENYELENGGARA</label>
                            <input type="text" name="instansi" class="form-control rounded-3 py-2 shadow-sm" value="{{ $pelatihan->instansi_penyelenggara }}" required>
                        </div>

                        {{-- INFO STATUS SAAT INI (Opsional, hanya tampilan readonly) --}}
                        <div class="col-12 mt-2">
                             <div class="p-3 rounded-3 bg-light border border-dashed text-center">
                                 <span class="text-muted me-2 small uppercase fw-bold">Status Sistem Saat Ini:</span>
                                 @if($pelatihan->status == 'selesai')
                                     <span class="badge bg-success rounded-pill px-3 py-2">Selesai Berlangsung</span>
                                 @elseif($pelatihan->status == 'berlangsung')
                                     <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Sedang Berlangsung</span>
                                 @else
                                     <span class="badge bg-primary rounded-pill px-3 py-2">Akan Datang</span>
                                 @endif
                             </div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="col-12 mt-5 text-end border-top pt-4">
                            <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light rounded-4 px-4 me-2 border fw-semibold">Batal</a>
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
    function tambahBaris() {
        const container = document.getElementById('container-peserta');
        const rows = document.querySelectorAll('.baris-peserta');
        if (rows.length > 0) {
            const newRow = rows[0].cloneNode(true);
            const selects = newRow.querySelectorAll('select');
            selects.forEach(select => select.selectedIndex = 0);
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
    .form-control:focus, .form-select:focus {
        border-color: #f97316 !important;
        box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1) !important;
    }
    .bg-light {
        background-color: #fcfcfc !important;
    }
</style>
@endsection