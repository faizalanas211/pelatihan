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

    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0 d-flex justify-content-between align-items-center" style="background: #fffbeb;">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">
                        {{ $sertifikasi->nama_pelatihan }}
                    </h4>
                    <p class="mb-0 text-muted">Data Peserta Sertifikasi</p>
                </div>
                <div>
                    <a href="{{ route('sertifikasi.create', [
                        'tahun' => $sertifikasi->tahun,
                        'master_id' => $sertifikasi->id
                    ]) }}" 
                    class="btn rounded-4 px-4 py-2 shadow-sm d-inline-flex align-items-center fw-semibold"
                    style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white;">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Peserta Sertifikasi
                    </a>

                    @if(!empty($peserta) && count($peserta) > 0)
                    <a href="{{ route('sertifikasi.edit', $sertifikasi->id) }}" 
                    class="btn btn-warning rounded-4 px-4 shadow-sm fw-bold text-white ms-2">
                        <i class="bi bi-pencil-square me-2"></i>Edit
                    </a>
                    @endif
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">

                {{-- INFO MASTER --}}
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold">Tahun</label>
                            <div class="fw-bold fs-5 text-orange">
                                {{ $sertifikasi->tahun }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold">Instansi Penerbit</label>
                            <div class="fw-bold">
                                {{ $header->instansi_penerbit ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PESERTA --}}
                <div class="d-flex align-items-center mb-4">
                    <div style="width: 5px; height: 25px; background: #f97316; border-radius: 10px;"></div>
                    <h5 class="fw-bold mb-0 ms-2">Daftar Peserta Sertifikasi</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead style="background: #fff8f0;">
                            <tr>
                                <th class="text-center">NO</th>
                                <th>NIP</th>
                                <th>NAMA</th>
                                <th class="text-center">TANGGAL PELAKSANAAN</th>
                                <th class="text-center">MASA BERLAKU</th>
                                <th class="text-center">FILE SERTIFIKAT</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peserta as $i => $p)
                            @php
                                $extension = pathinfo($p->sertifikat_path, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td>{{ $p->nip }}</td>
                                <td class="text-uppercase">{{ $p->nama_peserta }}</td>

                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">s/d</small>
                                    <br>
                                    {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d M Y') }}
                                </td>

                                <td class="text-center">
                                    {{ $p->masa_berlaku ? \Carbon\Carbon::parse($p->masa_berlaku)->format('d M Y') : '-' }}
                                </td>

                                <td class="text-center">
                                    @if($p->sertifikat_path)
                                        @if($isImage)
                                            <button class="btn btn-info btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#previewModal{{ $p->id }}">
                                                Preview
                                            </button>
                                        @else
                                            <a href="{{ asset('storage/'.$p->sertifikat_path) }}" 
                                               target="_blank" 
                                               class="btn btn-success btn-sm">
                                                Lihat PDF
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted small">Belum ada</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-orange"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal{{ $p->id }}">
                                        Kelola
                                    </button>
                                </td>
                            </tr>

                            {{-- MODAL PREVIEW GAMBAR --}}
                            @if($p->sertifikat_path && $isImage)
                            <div class="modal fade" id="previewModal{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content rounded-4">
                                        <div class="modal-header">
                                            <h6 class="fw-bold">Preview Sertifikat - {{ $p->nama_peserta }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="{{ asset('storage/'.$p->sertifikat_path) }}" 
                                                 class="img-fluid" 
                                                 alt="Sertifikat {{ $p->nama_peserta }}"
                                                 style="max-height: 500px;">
                                        </div>
                                        <div class="modal-footer">
                                            <a href="{{ asset('storage/'.$p->sertifikat_path) }}" 
                                               target="_blank" 
                                               class="btn btn-success">
                                                Buka di tab baru
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- MODAL EDIT --}}
                            <div class="modal fade" id="modal{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4">
                                        <form action="{{ route('sertifikasi.updatePeserta', $p->id) }}" 
                                              method="POST" 
                                              enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h6 class="fw-bold">Kelola: {{ $p->nama_peserta }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="small fw-semibold">Tanggal Mulai</label>
                                                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ $p->tanggal_mulai }}">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="small fw-semibold">Tanggal Selesai</label>
                                                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $p->tanggal_selesai }}">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="small fw-semibold">Masa Berlaku</label>
                                                    <input type="date" name="masa_berlaku" class="form-control" value="{{ $p->masa_berlaku }}">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="small fw-semibold">Upload Sertifikat</label>
                                                    <input type="file" name="sertifikat" class="form-control" accept="application/pdf,image/jpeg,image/jpg,image/png">
                                                    <small class="text-muted">PDF, JPG, JPEG, PNG (Max 2MB)</small>

                                                    @if($p->sertifikat_path)
                                                        <div class="mt-2">
                                                            <small class="text-success">
                                                                ✔ File sudah ada
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button class="btn text-white" style="background:#f97316;">
                                                    Simpan
                                                </button>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>

                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada peserta
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('sertifikasi.index') }}" class="btn btn-light">
                        ← Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.text-orange { color: #f97316; }
.btn-outline-orange {
    border: 1px solid #f97316;
    color: #f97316;
    background: transparent;
}
.btn-outline-orange:hover {
    background: #f97316;
    color: white;
}
.btn-info {
    background: #0dcaf0;
    color: white;
}
.btn-info:hover {
    background: #0bb5d8;
    color: white;
}
</style>
@endsection