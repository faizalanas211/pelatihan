@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Rekap Pelatihan
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-archive-fill" style="color: #f97316; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Rekap Pelatihan
                        </h3>
                        <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">
                            <i class="bi bi-check-circle"></i> Daftar event pelatihan yang telah dilaksanakan
                        </p>
                    </div>
                </div>
            </div>
            <a href="{{ route('rekap-pelatihan.create') }}" class="btn rounded-4 px-4 py-2 shadow-sm d-inline-flex align-items-center fw-semibold" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white;">
                <i class="bi bi-plus-circle me-2"></i>Tambah Pelatihan
            </a>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b); border-radius: 2px;"></div>
    </div>

    {{-- FILTER --}}
    <div class="col-12 mb-4">
        <form action="{{ route('rekap-pelatihan.index') }}" method="GET">
            <div class="card border-0 shadow-sm rounded-4" style="background: #fffcf8;">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-uppercase">Tahun</label>
                            <select class="form-select border-0 shadow-sm" name="tahun">
                                <option value="">Semua Tahun</option>
                                @for ($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-uppercase">Cari Pelatihan</label>
                            <input type="text" name="search" class="form-control border-0 shadow-sm"
                                   placeholder="Nama pelatihan..."
                                   value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn w-100 text-white fw-bold" style="background: #f97316;">
                                Filter
                            </button>
                            <a href="{{ route('rekap-pelatihan.index') }}" class="btn border">
                                <i class="bi bi-arrow-repeat"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- LIST PELATIHAN --}}
    <div class="col-12 mt-2">
        <div class="d-flex justify-content-between mb-3">
            <h6 class="fw-bold text-uppercase">
                <i class="bi bi-list-check me-2 text-orange"></i>DAFTAR PELATIHAN
            </h6>
            <span class="badge px-3 py-2" style="background: #fff3e0; color: #f97316;">
                Total: {{ $pelatihan->total() }} Program
            </span>
        </div>

        <div class="row g-4">
            @forelse($pelatihan as $item)
                @php
                    // HITUNG JUMLAH PESERTA BERDASARKAN MASTER PELATIHAN
                    // Cari header di tabel pelatihan berdasarkan nama dan tahun
                    $headerPelatihan = DB::table('pelatihan')
                        ->where('jenis_pelatihan', $item->nama_pelatihan)
                        ->where('tahun', $item->tahun)
                        ->first();
                    
                    $jumlahPeserta = 0;
                    if ($headerPelatihan) {
                        $jumlahPeserta = DB::table('pelatihan_peserta')
                            ->where('pelatihan_id', $headerPelatihan->id)
                            ->count();
                    }
                @endphp
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex flex-column p-4">

                            <div class="d-flex justify-content-between mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:50px;height:50px;background:#fff3e0;">
                                    <i class="bi bi-patch-check-fill text-orange fs-4"></i>
                                </div>
                                <span class="badge" style="background:#fffbeb; color:#f97316;">
                                    {{ $item->tahun }}
                                </span>
                            </div>

                            <h5 class="fw-bold mb-2" style="color:#4a3728; font-size:1rem;">
                                {{ $item->nama_pelatihan }}
                            </h5>

                            <p class="text-muted small mb-3">
                                <i class="bi bi-bookmark me-1"></i> Program Pelatihan
                            </p>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between">
                                <div class="small text-muted">
                                    <i class="bi bi-people-fill text-orange me-1"></i>
                                    {{ $jumlahPeserta }} Peserta
                                </div>

                                <a href="{{ route('rekap-pelatihan.show', $item->id) }}" class="text-orange fw-bold small text-decoration-none">
                                    DETAIL →
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                    <h5 class="text-muted mt-3">Data tidak ditemukan</h5>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $pelatihan->appends(request()->input())->links() }}
        </div>
    </div>

</div>

<style>
    .text-orange { color: #f97316 !important; }
    
    .card {
        transition: all 0.2s ease;
        background: white;
    }
    
    .card:hover {
        background-color: #fefaf5;
    }

    .card-body h5 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }
    
    .pagination .page-item .page-link {
        border: none;
        background: #fff;
        color: #f97316;
        margin: 0 2px;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .pagination .page-item.active .page-link {
        background: #f97316;
        color: #fff;
    }
    
    .border-top {
        border-top: 1px solid #f0f0f0 !important;
    }
</style>
@endsection