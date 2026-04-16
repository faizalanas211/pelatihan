@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('sertifikasi.index') }}" style="color: #f97316; text-decoration: none;">Sertifikasi</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Detail Sertifikasi
</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- BAGIAN ATAS: INFORMASI EVENT --}}
    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0 d-flex justify-content-between align-items-center" style="background: #fffbeb;">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">{{ $sertifikasi->jenis_sertifikasi }}</h4>
                    <p class="mb-0 text-muted">Data Detail Pelaksanaan Sertifikasi Pegawai</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sertifikasi.edit', $sertifikasi->id) }}" class="btn btn-warning rounded-4 px-4 shadow-sm fw-bold text-white border-0" style="background: #f59e0b;">
                        <i class="bi bi-pencil-square me-2"></i>Edit
                    </a>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">
                {{-- INFO UTAMA: TAHUN, TANGGAL, PENERBIT --}}
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Tahun</label>
                            <span class="fs-5 fw-bold" style="color: #f97316;">{{ \Carbon\Carbon::parse($sertifikasi->tgl_terbit)->format('Y') }}</span>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Waktu Pelaksanaan / Terbit</label>
                            <span class="fs-6 fw-bold" style="color: #5c4a3a;">
                                <i class="bi bi-calendar3 me-2 text-muted"></i>
                                {{ \Carbon\Carbon::parse($sertifikasi->tgl_terbit)->translatedFormat('d M Y') }} 
                                @if(isset($sertifikasi->tanggal_selesai))
                                    <span class="text-muted mx-2">s/d</span>
                                    {{ \Carbon\Carbon::parse($sertifikasi->tanggal_selesai)->translatedFormat('d M Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Instansi Penerbit</label>
                            <span class="fs-6 fw-bold text-truncate d-block" style="color: #5c4a3a;" title="{{ $sertifikasi->instansi_penerbit }}">
                                {{ $sertifikasi->instansi_penerbit }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN BAWAH: DAFTAR PESERTA --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 5px; height: 25px; background: #f97316; border-radius: 10px;"></div>
                        <h5 class="fw-bold mb-0" style="color: #5c4a3a;">Daftar Pegawai & Sertifikat</h5>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead style="background: #fff8f0;">
                            <tr>
                                <th width="60" class="text-center py-3">NO</th>
                                <th width="180" class="py-3">NIP</th>
                                <th class="py-3">NAMA PEGAWAI</th>
                                <th width="180" class="text-center py-3">MASA BERLAKU</th>
                                <th width="200" class="text-center py-3">AKSI / SERTIFIKAT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peserta as $index => $p)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $p->nip }}</td>
                                <td class="text-uppercase" style="letter-spacing: 0.5px; font-size: 0.85rem;">{{ $p->nama_peserta }}</td>
                                <td class="text-center">
                                    @if($p->masa_berlaku)
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            {{ \Carbon\Carbon::parse($p->masa_berlaku)->translatedFormat('d F Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small italic">Permanen</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($p->sertifikat_path)
                                            <a href="{{ asset('storage/' . $p->sertifikat_path) }}" target="_blank" class="btn btn-sm btn-success rounded-3 shadow-sm">
                                                <i class="bi bi-file-earmark-pdf-fill"></i> Lihat
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-outline-orange rounded-3 fw-bold" style="color: #f97316; border-color: #f97316;" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $p->id }}">
                                            <i class="bi bi-gear-fill me-1"></i> {{ $p->sertifikat_path ? 'Ganti' : 'Kelola' }}
                                        </button>
                                    </div>

                                    {{-- MODAL KELOLA PER ORANG --}}
                                    <div class="modal fade" id="uploadModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4 text-start">
                                                <form action="{{ route('sertifikasi.upload-file', $p->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header border-0 bg-light rounded-top-4">
                                                        <h6 class="modal-title fw-bold text-dark">Kelola Data: {{ $p->nama_peserta }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3 text-center">
                                                            <div class="fw-bold fs-5 text-dark">{{ $p->nama_peserta }}</div>
                                                            <div class="text-muted small">NIP. {{ $p->nip }}</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-uppercase">Masa Berlaku Sertifikat</label>
                                                            <input type="date" name="masa_berlaku" class="form-control rounded-3" value="{{ $p->masa_berlaku }}">
                                                            <div class="form-text small text-muted">Kosongkan jika sertifikat berlaku selamanya.</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-uppercase">Unggah File Sertifikat</label>
                                                            <input type="file" name="sertifikat" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                                                            @if($p->sertifikat_path)
                                                                <div class="mt-2 small text-success">
                                                                    <i class="bi bi-check-circle-fill"></i> File sudah tersedia.
                                                                </div>
                                                            @endif
                                                            <div class="form-text small text-danger">Format: PDF, JPG, PNG (Max 2MB).</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-4 pt-0">
                                                        <button type="button" class="btn btn-light rounded-4 px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn text-white rounded-4 px-4 fw-bold shadow-sm" style="background: #f97316;">
                                                            Simpan Perubahan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada pegawai terdaftar</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 d-flex justify-content-between">
                    <a href="{{ route('sertifikasi.index') }}" class="btn btn-light rounded-4 px-4 border fw-semibold">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th {
        font-size: 0.7rem;
        letter-spacing: 1px;
        color: #b87a4a;
        border-bottom: 2px solid #fed7aa;
        text-transform: uppercase;
    }
    .table tbody td {
        padding: 1rem;
        color: #5c4a3a;
        border-bottom: 1px solid #f1f1f1;
    }
    .btn-outline-orange {
        border: 1px solid #f97316;
        background: transparent;
        transition: all 0.2s;
    }
    .btn-outline-orange:hover {
        background: #f97316;
        color: white !important;
    }
    .badge {
        font-weight: 600;
        font-size: 0.75rem;
    }
</style>
@endsection