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
                    <p class="mb-0 text-muted">Data Rekap & Detail Pelaksanaan Pelatihan</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('rekap-pelatihan.edit', $pelatihan->id) }}" class="btn btn-warning rounded-4 px-4 shadow-sm fw-bold text-white">
                        <i class="bi bi-pencil-square me-2"></i>Edit
                    </a>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">
                {{-- INFO MASTER --}}
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Tahun</label>
                            <div class="fw-bold fs-5 text-orange">
                                {{ $pelatihan->tahun }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Instansi Penyelenggara</label>
                            <div class="fw-bold fs-5" style="color: #5c4a3a;">
                                {{ $pelatihan->instansi_penyelenggara }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PESERTA --}}
                <div class="d-flex align-items-center mb-4">
                    <div style="width: 5px; height: 25px; background: #f97316; border-radius: 10px;"></div>
                    <h5 class="fw-bold mb-0 ms-2">Daftar Peserta & Sertifikat</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead style="background: #fff8f0;">
                            <tr>
                                <th class="text-center py-3">NO</th>
                                <th class="py-3">NIP</th>
                                <th class="py-3">NAMA PESERTA</th>
                                <th class="text-center py-3">TANGGAL</th>
                                <th class="text-center py-3">JP</th>
                                <th class="text-center py-3">AKSI / SERTIFIKAT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peserta as $index => $p)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $p->nip }}</td>
                                <td class="text-uppercase" style="letter-spacing: 0.5px; font-size: 0.85rem;">{{ $p->nama_peserta }}</td>
                                
                                {{-- TANGGAL: DISESUAIKAN DENGAN DESAIN TUBEL --}}
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d M Y') }}
                                    <br>
                                    <small class="text-muted fw-bold" style="font-size: 0.7rem;">s/d</small>
                                    <br>
                                    {{ \Carbon\Carbon::parse($p->tanggal_selesai)->translatedFormat('d M Y') }}
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $p->jp ?? '0' }}</span>
                                </td>
                                
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($p->sertifikat_path)
                                            <a href="{{ asset('storage/' . $p->sertifikat_path) }}" target="_blank" class="btn btn-sm btn-success rounded-3 shadow-sm px-3">
                                                Lihat
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-outline-orange rounded-3 fw-bold px-3" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $p->id }}">
                                            Kelola
                                        </button>
                                    </div>

                                    {{-- MODAL KELOLA --}}
                                    <div class="modal fade" id="uploadModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4 text-start">
                                                <form action="{{ route('rekap-pelatihan.upload-sertifikat-peserta', $p->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header border-0 bg-light rounded-top-4">
                                                        <h6 class="modal-title fw-bold text-dark">Kelola Data: {{ $p->nama_peserta }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="small fw-semibold text-uppercase mb-2 d-block">Jam Pelajaran (JP)</label>
                                                            <input type="number" name="jp" class="form-control rounded-3" value="{{ $p->jp }}" placeholder="Masukkan jumlah JP">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="small fw-semibold text-uppercase mb-2 d-block">Upload Sertifikat</label>
                                                            <input type="file" name="sertifikat" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                                                            @if($p->sertifikat_path)
                                                                <small class="text-success mt-2 d-block">✔ File sertifikat sudah tersedia</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn text-white px-4" style="background: #f97316;">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada peserta terdaftar</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light rounded-3">
                        ← Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-orange { color: #f97316 !important; }
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 1px;
        color: #b87a4a;
        border-bottom: 2px solid #fed7aa;
        text-transform: uppercase;
        vertical-align: middle;
    }
    .btn-outline-orange {
        border: 1px solid #f97316;
        color: #f97316;
        transition: all 0.2s;
    }
    .btn-outline-orange:hover {
        background: #f97316;
        color: white !important;
    }
</style>
@endsection