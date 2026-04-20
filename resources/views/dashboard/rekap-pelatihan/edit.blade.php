@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('rekap-pelatihan.index') }}" style="color: #f97316; text-decoration: none;">Rekap Pelatihan</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Edit Pelatihan
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
                <h3 class="fw-bold mb-0" style="color: #5c4a3a;">Edit Data Pelatihan</h3>
                <p class="text-muted mb-0 small">Perbarui informasi pelaksanaan atau daftar peserta</p>
            </div>
        </div>
        
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 p-md-5">
                {{-- Error Reporting --}}
                @if ($errors->any())
                    <div class="alert alert-danger rounded-4 border-0 mb-4 shadow-sm">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('rekap-pelatihan.update', $pelatihan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- Hidden Master ID --}}
                    @php
                        $masterId = \App\Models\MasterPelatihan::where('nama_pelatihan', $pelatihan->jenis_pelatihan)
                                    ->where('kategori', 'pelatihan')
                                    ->value('id');
                    @endphp
                    <input type="hidden" name="master_pelatihan_id" value="{{ $masterId }}">

                    <div class="row g-4">
                        {{-- INFO HEADER --}}
                        <div class="col-md-8">
                            <label class="form-label fw-bold" style="color: #b87a4a;">NAMA KEGIATAN / JENIS PELATIHAN</label>
                            <input type="text" class="form-control rounded-3 py-2 shadow-sm bg-light" value="{{ $pelatihan->jenis_pelatihan }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI PENYELENGGARA</label>
                            <input type="text" name="instansi" class="form-control rounded-3 py-2 shadow-sm" value="{{ $pelatihan->instansi_penyelenggara }}" required>
                        </div>

                        <hr class="my-2 opacity-25">

                        {{-- SEKSI PESERTA --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PESERTA & WAKTU INDIVIDUAL</label>
                                <button type="button" class="btn btn-sm btn-orange-outline rounded-3" onclick="tambahBaris()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Peserta
                                </button>
                            </div>

                            <div id="container-peserta">
                                @foreach($pesertaTerpilih as $pt)
                                <div class="card border-0 shadow-sm mb-3 baris-peserta" style="background: #f8f9fa; border: 1px solid #e9ecef !important;">
                                    <div class="card-body p-3">
                                        {{-- PEGAWAI --}}
                                        <div class="mb-3">
                                            <label class="small fw-semibold text-muted text-uppercase">Pegawai</label>
                                            <select name="pegawai_id[]" class="form-select shadow-sm" required>
                                                <option value="">-- Pilih Pegawai --</option>
                                                @foreach($pegawais as $peg)
                                                    <option value="{{ $peg->nip }}|{{ $peg->nama }}" 
                                                        {{ $pt->nip == $peg->nip ? 'selected' : '' }}>
                                                        {{ $peg->nama }} ({{ $peg->nip }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- TANGGAL PER PESERTA --}}
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="small fw-semibold text-muted text-uppercase">Tanggal Mulai</label>
                                                <input type="date" name="tanggal_mulai[]" class="form-control shadow-sm" value="{{ $pt->tanggal_mulai }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small fw-semibold text-muted text-uppercase">Tanggal Selesai</label>
                                                <input type="date" name="tanggal_selesai[]" class="form-control shadow-sm" value="{{ $pt->tanggal_selesai }}" required>
                                            </div>
                                        </div>

                                        {{-- ACTION --}}
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus rounded-3" onclick="hapusBaris(this)">
                                                <i class="bi bi-trash me-1"></i> Hapus Peserta
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="col-12 mt-4 d-flex justify-content-end gap-3 pt-4 border-top">
                            <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light rounded-4 px-4 border fw-semibold">Batal</a>
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
    document.addEventListener('DOMContentLoaded', function () {
        updateHapusButton();
    });

    function tambahBaris() {
        const container = document.getElementById('container-peserta');
        const rows = document.querySelectorAll('.baris-peserta');
        const newRow = rows[0].cloneNode(true);
        
        // Reset values
        newRow.querySelectorAll('input').forEach(i => i.value = '');
        newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        
        container.appendChild(newRow);
        updateHapusButton();
    }

    function hapusBaris(btn) {
        const rows = document.querySelectorAll('.baris-peserta');
        if (rows.length > 1) {
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
</script>

<style>
    .btn-orange-outline { color: #f97316; border: 1px solid #f97316; background: transparent; transition: all 0.2s; }
    .btn-orange-outline:hover { background: #f97316; color: white; }
    .form-control:focus, .form-select:focus { border-color: #f97316 !important; box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1) !important; }
    .bg-light { background-color: #f8f9fa !important; }
    .baris-peserta { transition: all 0.3s ease; }
</style>
@endsection