@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('sertifikasi.index') }}" style="color: #f97316; text-decoration: none;">Sertifikasi</a></li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">Detail Sertifikasi</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0 d-flex justify-content-between align-items-center" style="background: #fffbeb;">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">{{ $sertifikasi->jenis_sertifikasi }}</h4>
                    <p class="mb-0 text-muted">Detail Data Sertifikasi</p>
                </div>
                <div class="d-flex gap-2">
                    {{-- TOMBOL EDIT EVENT --}}
                    <a href="{{ route('sertifikasi.edit', $sertifikasi->id) }}" class="btn btn-warning rounded-4 px-3 shadow-sm fw-bold text-white">
                        <i class="bi bi-pencil-square me-1"></i> Edit Event
                    </a>
                    <a href="{{ route('sertifikasi.index') }}" class="btn btn-light rounded-4 px-3 border shadow-sm"> Kembali</a>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">
                {{-- HEADER INFORMASI: TAHUN, TEMPAT, TANGGAL --}}
                <div class="row g-4 mb-5 text-center">
                    <div class="col-md-4">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Tahun Terbit</label>
                            <span class="fs-5 fw-bold" style="color: #f97316;">{{ \Carbon\Carbon::parse($sertifikasi->tgl_terbit)->format('Y') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Tempat (Instansi)</label>
                            <span class="fs-5 fw-bold" style="color: #5c4a3a;">{{ $sertifikasi->instansi_penerbit }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Tanggal Terbit</label>
                            <span class="fs-5 fw-bold" style="color: #5c4a3a;">{{ \Carbon\Carbon::parse($sertifikasi->tgl_terbit)->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-hover align-middle border">
                        <thead style="background: #fff8f0;">
                            <tr>
                                <th width="60" class="text-center py-3">NO</th>
                                <th>NAMA PEGAWAI (NIP)</th>
                                <th class="text-center">MASA BERLAKU</th>
                                <th width="250" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peserta as $index => $p)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ $p->nama_peserta }}</div>
                                    <div class="small text-muted">{{ $p->nip }}</div>
                                </td>
                                <td class="text-center">
                                    {{-- FIX: KOSONG JIKA TIDAK ADA DATA MASA BERLAKU --}}
                                    @if($p->masa_berlaku)
                                        <span class="badge bg-light text-dark border px-3 py-2">
                                            {{ \Carbon\Carbon::parse($p->masa_berlaku)->translatedFormat('d F Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small italic">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($p->sertifikat_path)
                                            <a href="{{ asset('storage/' . $p->sertifikat_path) }}" target="_blank" class="btn btn-sm btn-success rounded-3">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-outline-orange rounded-3 fw-bold" style="color: #f97316; border-color: #f97316;" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $p->id }}">
                                            <i class="bi bi-gear-fill me-1"></i> Kelola
                                        </button>
                                    </div>

                                    {{-- MODAL KELOLA PER ORANG --}}
                                    <div class="modal fade" id="uploadModal{{ $p->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('sertifikasi.upload-file', $p->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header border-0 bg-light">
                                                        <h6 class="modal-title fw-bold">Kelola Data: {{ $p->nama_peserta }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-uppercase">Masa Berlaku Sertifikat</label>
                                                            <input type="date" name="masa_berlaku" class="form-control rounded-3" value="{{ $p->masa_berlaku }}">
                                                            <div class="form-text small text-muted">Kosongkan jika tidak memiliki masa berlaku.</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-uppercase">File Sertifikat (PDF/JPG)</label>
                                                            <input type="file" name="sertifikat" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="submit" class="btn text-white rounded-3 w-100 fw-bold shadow-sm" style="background: #f97316;">SIMPAN PERUBAHAN</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-outline-orange:hover {
        background: #f97316;
        color: white !important;
    }
</style>
@endsection