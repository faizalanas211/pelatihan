@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('sertifikasi.index') }}" style="color: #f97316; text-decoration: none;">Sertifikasi</a></li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">Edit Data</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-3 p-2" style="background: #f9731620;">
                <i class="bi bi-pencil-square" style="color: #f97316; font-size: 1.5rem;"></i>
            </div>
            <h3 class="fw-bold mb-0" style="color: #f97316;">Edit Data Sertifikasi</h3>
        </div>
        
        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('sertifikasi.update', $sertifikasi->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        {{-- SEKSI PESERTA --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 4px; height: 20px; background: #f97316; border-radius: 10px;"></div>
                                    <label class="form-label fw-bold mb-0" style="color: #b87a4a;">DAFTAR PEGAWAI (PESERTA)</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-3 fw-bold" onclick="tambahBaris()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Orang
                                </button>
                            </div>

                            <div id="container-peserta">
                                {{-- Looping Peserta yang sudah ada --}}
                                @foreach($pesertaTerpilih as $pt)
                                <div class="row g-3 mb-3 baris-peserta align-items-end">
                                    <div class="col-md-11">
                                        <label class="small text-muted mb-1 text-uppercase" style="font-size: 0.7rem;">Pilih Nama Pegawai</label>
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
                                        <button type="button" class="btn btn-outline-danger w-100 rounded-3 shadow-sm" onclick="this.closest('.baris-peserta').remove()">
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
                            <label class="form-label fw-bold" style="color: #b87a4a;">JENIS SERTIFIKASI / NAMA KEAHLIAN</label>
                            <input type="text" name="jenis_sertifikasi" class="form-control rounded-3 py-2 shadow-sm" value="{{ $sertifikasi->jenis_sertifikasi }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #b87a4a;">TANGGAL TERBIT</label>
                            <input type="date" name="tgl_terbit" class="form-control rounded-3 py-2 shadow-sm" value="{{ $sertifikasi->tgl_terbit }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #b87a4a;">INSTANSI PENERBIT</label>
                            <input type="text" name="instansi" class="form-control rounded-3 py-2 shadow-sm" value="{{ $sertifikasi->instansi_penerbit }}" required>
                        </div>

                        {{-- REVISI: 3 PILIHAN STATUS (KEMBARAN REKAP) --}}
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold" style="color: #b87a4a;">STATUS KEGIATAN</label>
                            <div class="d-flex gap-3 mt-1 flex-wrap">
                                {{-- Akan Datang --}}
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusMendatang" value="mendatang" {{ $sertifikasi->status == 'mendatang' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusMendatang">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill" style="cursor: pointer;">Akan Datang</span>
                                    </label>
                                </div>
                                {{-- Sedang Berlangsung --}}
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusBerlangsung" value="berlangsung" {{ $sertifikasi->status == 'berlangsung' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusBerlangsung">
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill" style="cursor: pointer;">Sedang Berlangsung</span>
                                    </label>
                                </div>
                                {{-- Selesai --}}
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusSelesai" value="selesai" {{ $sertifikasi->status == 'selesai' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusSelesai">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill" style="cursor: pointer;">Selesai Berlangsung</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-text mt-2 small text-muted">*Status "Selesai" akan tampil di menu Sertifikasi. Status lain akan tampil di Jadwal Pelatihan.</div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="col-12 mt-5 text-end border-top pt-4">
                            <a href="{{ route('sertifikasi.show', $sertifikasi->id) }}" class="btn btn-light rounded-4 px-4 me-2 border fw-semibold">Batal</a>
                            <button type="submit" class="btn rounded-4 px-5 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #f97316, #f59e0b); border: none;">
                                <i class="bi bi-check-circle me-2"></i>SIMPAN PERUBAHAN
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Hidden Template untuk tambah baris --}}
<template id="baris-template">
    <div class="row g-3 mb-3 baris-peserta align-items-end">
        <div class="col-md-11">
            <select name="peserta[]" class="form-select rounded-3 shadow-sm py-2" required>
                <option value="" disabled selected>-- Pilih Pegawai --</option>
                @foreach($pegawais as $peg)
                    <option value="{{ $peg->nip }}|{{ $peg->nama }}">{{ $peg->nama }} ({{ $peg->nip }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger w-100 rounded-3 shadow-sm" onclick="this.closest('.baris-peserta').remove()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
    function tambahBaris() {
        const container = document.getElementById('container-peserta');
        const template = document.getElementById('baris-template').innerHTML;
        container.insertAdjacentHTML('beforeend', template);
    }
</script>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #f97316 !important;
        box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.1) !important;
    }
    .form-check-input:checked {
        background-color: #f97316;
        border-color: #f97316;
    }
</style>
@endsection