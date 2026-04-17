@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Riwayat Pengembangan SDM
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3"
                     style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%);
                            width: 65px; height: 65px;
                            display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-mortarboard" style="color: #f97316; font-size: 1.8rem;"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"
                        style="background: linear-gradient(135deg, #f97316, #f59e0b);
                               -webkit-background-clip: text;
                               -webkit-text-fill-color: transparent;">
                        Riwayat Pengembangan SDM
                    </h3>
                    <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">
                        Rekap pelatihan, sertifikasi, dan tugas belajar pegawai
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-3 mb-2"
             style="height: 3px;
                    background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24);
                    border-radius: 2px;">
        </div>
    </div>

    
    {{-- TABLE --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                @if($data->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <span>Belum ada data pegawai</span>
                    </div>
                @else

                <div class="row g-2 mb-3 align-items-center">

                    {{-- LEFT: SEARCH --}}
                    <div class="col-md-4">
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                class="form-control"
                                placeholder="Cari nama / NIP...">

                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                            <input type="hidden" name="direction" value="{{ request('direction') }}">

                            <button class="btn btn-outline-orange">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>

                    {{-- MIDDLE: SORT --}}
                    <div class="col-md-4">
                        <form method="GET" class="d-flex gap-1">
                            <select name="sort" class="form-select">
                                <option value="">Urutkan</option>
                                <option value="nama" {{ request('sort')=='nama'?'selected':'' }}>Nama</option>
                                <option value="jp" {{ request('sort')=='jp'?'selected':'' }}>JP</option>
                                <option value="pelatihan" {{ request('sort')=='pelatihan'?'selected':'' }}>Pelatihan</option>
                                <option value="sertifikasi" {{ request('sort')=='sertifikasi'?'selected':'' }}>Sertifikasi</option>
                                <option value="tubel" {{ request('sort')=='tubel'?'selected':'' }}>Tubel</option>
                            </select>

                            <select name="direction" class="form-select">
                                <option value="asc" {{ request('direction')=='asc'?'selected':'' }}>ASC</option>
                                <option value="desc" {{ request('direction')=='desc'?'selected':'' }}>DESC</option>
                            </select>

                            <input type="hidden" name="search" value="{{ request('search') }}">

                            <button class="btn btn-outline-orange">Terapkan</button>
                        </form>
                    </div>

                    {{-- RIGHT: ACTION --}}
                    <div class="col-md-4 text-md-end d-flex justify-content-md-end gap-2">

                        <a href="{{ route('riwayat-sdm.index') }}" 
                        class="btn btn-outline-secondary">
                            Reset
                        </a>

                        <a href="{{ route('riwayat-sdm.export', request()->query()) }}" 
   class="btn btn-orange btn-export">
    <i class="bi bi-file-earmark-excel"></i> Export
</a>

                    </div>

                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>No</th>
                                <th class="text-start">Nama Pegawai</th>
                                <th>Pelatihan</th>
                                <th>Sertifikasi</th>
                                <th>Tubel</th>
                                <th>JP</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $i => $row)
                                <tr>
                                    <td class="text-center">{{ $data->firstItem() + $i }}</td>

                                    <td>
                                        <div class="fw-semibold">{{ $row->nama }}</div>
                                        <small class="text-muted">{{ $row->nip }}</small>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                            {{ $row->total_pelatihan ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                            {{ $row->total_sertifikasi ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                                            {{ $row->total_tubel ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="fw-bold">
                                            {{ $row->total_jp ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('riwayat-sdm.show', [
                                                $row->id,
                                                'page' => request('page')
                                            ]) }}"
                                           class="btn btn-sm btn-outline-orange rounded-pill px-3">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @endif

            </div>
        </div>
    </div>

    {{-- Pagination --}}
        @if($data->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
            <div class="mb-3 mb-md-0 text-muted">
                Menampilkan {{ $data->firstItem() ?? 0 }}
                sampai {{ $data->lastItem() ?? 0 }}
                dari {{ $data->total() }} data pegawai
            </div>

            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item {{ $data->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $data->url(1) }}">
                            <i class="bx bx-chevrons-left"></i>
                        </a>
                    </li>

                    <li class="page-item {{ $data->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $data->previousPageUrl() }}">
                            <i class="bx bx-chevron-left"></i>
                        </a>
                    </li>

                    @foreach($data->getUrlRange(
                        max(1, $data->currentPage() - 2),
                        min($data->lastPage(), $data->currentPage() + 2)
                    ) as $page => $url)
                        <li class="page-item {{ $page == $data->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    <li class="page-item {{ !$data->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $data->nextPageUrl() }}">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </li>

                    <li class="page-item {{ !$data->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $data->url($data->lastPage()) }}">
                            <i class="bx bx-chevrons-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
</div>

<style>
    .btn-outline-orange {
    border: 1px solid #f97316;
    color: #f97316;
}

.btn-outline-orange:hover {
    background-color: #f97316;
    color: #fff;
}

.pagination .page-link {
    color: #f97316;
    border-radius: 8px;
}

.pagination .page-item.active .page-link {
    background-color: #f97316;
    border-color: #f97316;
    color: #fff;
}

.pagination .page-link:hover {
    background-color: #fed7aa;
    color: #f97316;
}

.btn-orange {
    background: linear-gradient(135deg, #f97316, #f59e0b);
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 10px;
    padding: 8px 16px;
    box-shadow: 0 4px 14px rgba(249,115,22,.35);
    transition: all 0.25s ease;
}

/* 🔥 Hover effect */
.btn-orange:hover {
    background: linear-gradient(135deg, #ea580c, #d97706);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249,115,22,.45);
    color: #fff;
}

/* klik (active) */
.btn-orange:active {
    transform: scale(0.97);
    box-shadow: 0 3px 10px rgba(249,115,22,.3);
}

/* icon spacing + animasi */
.btn-export i {
    margin-right: 6px;
    transition: transform 0.2s ease;
}

/* icon ikut gerak pas hover */
.btn-export:hover i {
    transform: scale(1.1) rotate(-5deg);
}
</style>
@endsection