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

    {{-- HEADER --}}
    <div class="col-12">
        <div class="card rounded-4 border-0 shadow-sm overflow-hidden">
            <div class="card-header p-4 border-0 d-flex justify-content-between align-items-center" style="background: #fffbeb;">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #5c4a3a;">
                        {{ $pelatihan->nama_pelatihan }}
                    </h4>
                    <p class="mb-0 text-muted">Data Peserta Pelatihan</p>
                </div>
                <div>
                    <a href="{{ route('rekap-pelatihan.create', [
                        'tahun' => $pelatihan->tahun,
                        'master_id' => $pelatihan->id
                    ]) }}" 
                    class="btn rounded-4 px-4 py-2 shadow-sm d-inline-flex align-items-center fw-semibold"
                    style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white;">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Peserta
                    </a>

                    @if(!empty($peserta) && count($peserta) > 0)
                    
                    @endif
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">

                {{-- INFO MASTER --}}
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold">Tahun</label>
                            <div class="fw-bold fs-5 text-orange">
                                {{ $pelatihan->tahun }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold">Instansi Penyelenggara</label>
                            <div class="fw-bold">
                                {{ $pelatihan->instansi ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PESERTA --}}
                <div class="d-flex align-items-center mb-4">
                    <div style="width: 5px; height: 25px; background: #f97316; border-radius: 10px;"></div>
                    <h5 class="fw-bold mb-0 ms-2">Daftar Peserta Pelatihan</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead style="background: #fff8f0;">
                            <tr>
                                <th class="text-center">NO</th>
                                <th>NIP</th>
                                <th>NAMA</th>
                                <th class="text-center">TANGGAL</th>
                                <th class="text-center">JP</th>
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
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.85rem;">
                                        <i class="bi bi-stopwatch"></i> {{ $p->jp }} JP
                                    </span>
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
                                    <a href="{{ route('rekap-pelatihan.edit', $pelatihan->id) }}" 
                                       class="btn btn-sm btn-outline-orange">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                </td>
                            </tr>

                            {{-- MODAL PREVIEW GAMBAR --}}
                            @if($p->sertifikat_path && in_array(strtolower($extension), ['jpg', 'jpeg', 'png']))
                            <div class="modal fade" id="previewModal{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content rounded-4">
                                        <div class="modal-header">
                                            <h6 class="fw-bold">
                                                Preview Sertifikat - {{ $p->nama_peserta }}
                                            </h6>
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
                    <a href="{{ route('rekap-pelatihan.index') }}" class="btn btn-light">
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
.bg-success-subtle {
    background-color: #e8f5e9 !important;
}
</style>
@endsection