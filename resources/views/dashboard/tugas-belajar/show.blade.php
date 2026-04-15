@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('tugas-belajar.index') }}" style="color: #f97316; text-decoration: none;">Rekap Tugas Belajar</a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Detail Tugas Belajar
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
                        {{ $tubel->nama_pelatihan }}
                    </h4>
                    <p class="mb-0 text-muted">Data Peserta Tugas Belajar</p>
                </div>
                <div>
                    <a href="{{ route('tugas-belajar.create', [
    'tahun' => $tubel->tahun,
    'master_id' => $tubel->id
]) }}" 
class="btn rounded-4 px-4 py-2 shadow-sm d-inline-flex align-items-center fw-semibold"
style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white;">
    <i class="bi bi-plus-circle me-2"></i>Tambah Peserta Tubel
</a>

                    <a href="{{ route('tugas-belajar.edit', $tubel->id) }}" 
                    class="btn btn-warning rounded-4 px-4 shadow-sm fw-bold text-white">
                        <i class="bi bi-pencil-square me-2"></i>Edit
                    </a>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">

                {{-- INFO MASTER --}}
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold">Tahun</label>
                            <div class="fw-bold fs-5 text-orange">
                                {{ $tubel->tahun }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="p-3 rounded-4 bg-light border">
                            <label class="small text-muted text-uppercase fw-bold">Program / Perguruan Tinggi</label>
                            <div class="fw-bold">
                                {{ $tubel->nama_pelatihan }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PESERTA --}}
                <div class="d-flex align-items-center mb-4">
                    <div style="width: 5px; height: 25px; background: #f97316; border-radius: 10px;"></div>
                    <h5 class="fw-bold mb-0 ms-2">Daftar Peserta Tubel</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead style="background: #fff8f0;">
                            <tr>
                                <th class="text-center">NO</th>
                                <th>NIP</th>
                                <th>NAMA</th>
                                <th class="text-center">TANGGAL</th>
                                <th>NO SK</th>
                                <th class="text-center">FILE SK</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peserta as $i => $p)
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td>{{ $p->pegawai->nip }}</td>
                                <td class="text-uppercase">{{ $p->pegawai->nama }}</td>

                                {{-- tanggal --}}
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">s/d</small>
                                    <br>
                                    {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d M Y') }}
                                </td>

                                <td>{{ $p->no_sk_tubel }}</td>

                                {{-- file --}}
                                <td class="text-center">
                                    @if($p->file_sk_tubel)
                                        <a href="{{ asset('storage/'.$p->file_sk_tubel) }}" 
                                           target="_blank" 
                                           class="btn btn-success btn-sm">
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-muted small">Belum ada</span>
                                    @endif
                                </td>

                                {{-- aksi --}}
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-orange"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal{{ $p->id }}">
                                        Kelola
                                    </button>
                                </td>
                            </tr>

                            {{-- MODAL EDIT --}}
                            <div class="modal fade" id="modal{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4">
                                        <form action="{{ route('tugas-belajar.update', $p->id) }}" 
                                              method="POST" 
                                              enctype="multipart/form-data">
                                            @csrf

                                            <div class="modal-header">
                                                <h6 class="fw-bold">
                                                    Kelola: {{ $p->pegawai->nama }}
                                                </h6>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">

                                                <div class="mb-3">
                                                    <label>No SK</label>
                                                    <input type="text" name="no_sk_tubel" 
                                                           class="form-control"
                                                           value="{{ $p->no_sk_tubel }}">
                                                </div>

                                                <div class="mb-3">
                                                    <label>Upload SK</label>
                                                    <input type="file" name="file_sk_tubel" class="form-control">
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
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
                    <a href="{{ route('tugas-belajar.index') }}" class="btn btn-light">
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
}
.btn-outline-orange:hover {
    background: #f97316;
    color: white;
}
</style>
@endsection