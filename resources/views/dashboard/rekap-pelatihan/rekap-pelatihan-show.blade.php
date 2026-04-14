@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('rekap-pelatihan.index') }}" style="color: #f97316; text-decoration: none;">Rekap Pelatihan</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Detail Pelatihan
</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- BAGIAN ATAS: INFORMASI EVENT --}}
    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0 d-flex justify-content-between align-items-center" style="background: #fffbeb;">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">{{ $pelatihan->jenis_pelatihan }}</h4>
                    <p class="mb-0 text-muted">Data Rekap Pelatihan Terdaftar</p>
                </div>
                <a href="{{ route('rekap-pelatihan.edit', $pelatihan->id) }}" class="btn btn-warning rounded-4 px-4 shadow-sm fw-bold text-white">
                    <i class="bi bi-pencil-square me-2"></i>Edit & Validasi
                </a>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Tahun Pelatihan</label>
                            <span class="fs-5 fw-bold" style="color: #f97316;">{{ $pelatihan->tahun }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Waktu Pelaksanaan</label>
                            <span class="fs-5 fw-bold" style="color: #5c4a3a;">{{ \Carbon\Carbon::parse($pelatihan->waktu_pelaksanaan)->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        {{-- REVISI: Instansi pindah ke kolom JP atas --}}
                        <div class="p-3 rounded-4" style="background: #f8f9fa; border: 1px solid #eee;">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Instansi Penyelenggara</label>
                            <span class="fs-5 fw-bold" style="color: #5c4a3a;">{{ $pelatihan->instansi_penyelenggara }}</span>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN BAWAH: DAFTAR PESERTA --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 5px; height: 25px; background: #f97316; border-radius: 10px;"></div>
                        <h5 class="fw-bold mb-0" style="color: #5c4a3a;">Daftar Peserta, JP & Sertifikat</h5>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead style="background: #fff8f0;">
                            <tr>
                                <th width="60" class="text-center py-3">NO</th>
                                <th width="180" class="py-3">NIP</th>
                                <th class="py-3">NAMA LENGKAP PESERTA</th>
                                <th width="100" class="text-center py-3">JP</th>
                                <th width="200" class="text-center py-3">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peserta as $index => $p)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $p->nip }}</td>
                                <td class="text-uppercase" style="letter-spacing: 0.5px;">{{ $p->nama_peserta }}</td>
                                {{-- REVISI: JP tampil di setiap baris --}}
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $p->jp ?? '0' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($p->sertifikat_path)
                                            <a href="{{ asset('storage/' . $p->sertifikat_path) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-3">
                                                <i class="bi bi-file-earmark-check-fill"></i> Lihat
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-outline-orange rounded-3 fw-bold" style="color: #f97316; border-color: #f97316;" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $p->id }}">
                                            <i class="bi bi-gear-fill me-1"></i> Kelola
                                        </button>
                                    </div>

                                    {{-- MODAL KELOLA JP & SERTIFIKAT --}}
                                    <div class="modal fade" id="uploadModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <form action="{{ route('rekap-pelatihan.upload-sertifikat-peserta', $p->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header border-0 bg-light rounded-top-4">
                                                        <h6 class="modal-title fw-bold">Kelola Data Peserta</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="mb-4 text-center">
                                                            <h6 class="fw-bold mb-0">{{ $p->nama_peserta }}</h6>
                                                            <div class="small text-muted">{{ $p->nip }}</div>
                                                        </div>
                                                        
                                                        {{-- REVISI: Input JP pindah ke modal per peserta --}}
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-uppercase">Jam Pelajaran (JP)</label>
                                                            <input type="number" name="jp" class="form-control rounded-3" value="{{ $p->jp }}" placeholder="Masukkan jumlah JP">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-uppercase">File Sertifikat</label>
                                                            <input type="file" name="sertifikat" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                                                            <div class="form-text small text-danger">Format: PDF, JPG, PNG (Max 2MB). Biarkan kosong jika tidak ingin mengganti file.</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn text-white rounded-3 px-4 fw-bold" style="background: #f97316;">Simpan Data</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada peserta terdaftar</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 d-flex justify-content-between">
                    <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light rounded-4 px-4 border">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 1px;
        color: #b87a4a;
        border-bottom: 2px solid #fed7aa;
    }
    .table tbody td {
        padding: 1rem;
        color: #5c4a3a;
    }
    .btn-outline-orange:hover {
        background: #f97316;
        color: white !important;
    }
</style>
@endsection