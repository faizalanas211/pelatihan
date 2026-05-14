@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Rekap Tugas Belajar
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
                        <i class="bi bi-book-half" style="color: #f97316; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Rekap Tugas Belajar
                        </h3>
                        <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">
                            <i class="bi bi-mortarboard"></i> Daftar program tugas belajar pegawai
                        </p>
                    </div>
                </div>
            </div>
            <a href="{{ route('tugas-belajar.create') }}" class="btn rounded-4 px-4 py-2 shadow-sm d-inline-flex align-items-center fw-semibold" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white;">
                <i class="bi bi-plus-circle me-2"></i>Tambah Tubel
            </a>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b); border-radius: 2px;"></div>
    </div>

    {{-- FILTER --}}
    <div class="col-12 mb-4">
        <form action="{{ route('tugas-belajar.index') }}" method="GET">
            <div class="card border-0 shadow-sm rounded-4" style="background: #fffcf8;">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-uppercase">Tahun</label>
                            <select class="form-select border-0 shadow-sm" name="tahun">
                                <option value="">Semua Tahun</option>

                                @foreach($tahunList as $th)
                                    <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>
                                        {{ $th }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-uppercase">Cari</label>
                            <input type="text" name="search" class="form-control border-0 shadow-sm"
                                   placeholder="Nama universitas..."
                                   value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn w-100 text-white fw-bold" style="background: #f97316;">
                                Filter
                            </button>
                            <a href="{{ route('tugas-belajar.index') }}" class="btn border">
                                <i class="bi bi-arrow-repeat"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- LIST TUBEL --}}
    <div class="col-12 mt-2">
        <div class="d-flex justify-content-between mb-3">
            <h6 class="fw-bold text-uppercase">
                <i class="bi bi-list-check me-2 text-orange"></i>Daftar Tugas Belajar
            </h6>
        </div>

        <div class="row g-4">
            @forelse($tubel as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 h-100" style="border-radius: 20px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.02), 0 4px 12px rgba(0,0,0,0.03); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                        <div class="card-body d-flex flex-column p-4">
                            
                            {{-- TOP SECTION --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: linear-gradient(135deg, #fef5e8, #fff9f0);">
                                    <i class="bi bi-mortarboard-fill" style="color: #f97316; font-size: 1.4rem;"></i>
                                </div>
                                <span class="px-3 py-1 rounded-pill" style="background: #f8f9fa; color: #6c757d; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.3px;">
                                    {{ $item->tahun }}
                                </span>
                            </div>

                            {{-- TITLE --}}
                            <h5 class="fw-semibold mb-2" style="color: #1a1a1a; line-height: 1.4;">
                                {{ Str::limit($item->nama_pelatihan, 55) }}
                            </h5>

                            {{-- DIVIDER --}}
                            <div class="my-3" style="height: 1px; background: linear-gradient(90deg, #f0f0f0, #e0e0e0, #f0f0f0);"></div>

                            {{-- BOTTOM SECTION --}}
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #fef5e8; border-radius: 8px;">
                                        <i class="bi bi-people-fill" style="color: #f97316; font-size: 0.8rem;"></i>
                                    </div>
                                    <div>
                                        <span class="text-muted" style="font-size: 0.7rem;">Peserta</span>
                                        <br>
                                        <strong class="fw-semibold" style="color: #1a1a1a; font-size: 0.9rem;">
                                            @php
                                                $jumlahPeserta = DB::table('tubel_peserta')
                                                    ->where('master_pelatihan_id', $item->id)
                                                    ->count();
                                            @endphp
                                            {{ $jumlahPeserta }}
                                        </strong>
                                    </div>
                                </div>
                                
                                <a href="{{ route('tugas-belajar.show', $item->id) }}" class="d-flex align-items-center gap-1 fw-semibold" style="color: #f97316; font-size: 0.85rem; text-decoration: none; transition: all 0.2s;">
                                    <span>Detail</span>
                                    <i class="bi bi-arrow-right" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
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

        {{-- PAGINATION CUSTOM MANUAL (SAMA SEPERTI REKAP PELATIHAN) --}}
        @if($tubel->total() > 0)
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-5 pt-2">
            <div class="small text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Showing {{ $tubel->firstItem() }} to {{ $tubel->lastItem() }} of {{ $tubel->total() }} results
            </div>
            
            <div class="d-flex gap-1">
                {{-- Previous --}}
                @if($tubel->onFirstPage())
                    <span class="px-3 py-1 rounded" style="background: #e9ecef; color: #adb5bd; font-size: 0.8rem;">Previous</span>
                @else
                    <a href="{{ $tubel->previousPageUrl() }}" class="px-3 py-1 rounded text-decoration-none" style="background: #f1f3f5; color: #495057; font-size: 0.8rem;">Previous</a>
                @endif

                {{-- Nomor Halaman --}}
                @php
                    $currentPage = $tubel->currentPage();
                    $lastPage = $tubel->lastPage();
                    $start = max(1, $currentPage - 1);
                    $end = min($lastPage, $currentPage + 1);
                    
                    if ($start > 1) {
                        echo '<a href="'.$tubel->url(1).'" class="px-2 py-1 rounded text-center text-decoration-none" style="min-width: 32px; background: #f1f3f5; color: #495057; font-size: 0.8rem;">1</a>';
                        if ($start > 2) echo '<span class="px-1" style="color: #adb5bd;">...</span>';
                    }
                    
                    for ($i = $start; $i <= $end; $i++) {
                        if ($i == $currentPage) {
                            echo '<span class="px-2 py-1 rounded text-center" style="min-width: 32px; background: #f97316; color: white; font-size: 0.8rem;">'.$i.'</span>';
                        } else {
                            echo '<a href="'.$tubel->url($i).'" class="px-2 py-1 rounded text-center text-decoration-none" style="min-width: 32px; background: #f1f3f5; color: #495057; font-size: 0.8rem;">'.$i.'</a>';
                        }
                    }
                    
                    if ($end < $lastPage) {
                        if ($end < $lastPage - 1) echo '<span class="px-1" style="color: #adb5bd;">...</span>';
                        echo '<a href="'.$tubel->url($lastPage).'" class="px-2 py-1 rounded text-center text-decoration-none" style="min-width: 32px; background: #f1f3f5; color: #495057; font-size: 0.8rem;">'.$lastPage.'</a>';
                    }
                @endphp

                {{-- Next --}}
                @if($tubel->hasMorePages())
                    <a href="{{ $tubel->nextPageUrl() }}" class="px-3 py-1 rounded text-decoration-none" style="background: #f1f3f5; color: #495057; font-size: 0.8rem;">Next</a>
                @else
                    <span class="px-3 py-1 rounded" style="background: #e9ecef; color: #adb5bd; font-size: 0.8rem;">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>

<style>
    /* Card Hover Effect */
    .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.08), 0 4px 8px rgba(0,0,0,0.02) !important;
    }
    
    /* Arrow Hover Animation */
    .card:hover .bi-arrow-right {
        transform: translateX(3px);
    }
    
    .text-orange { color: #f97316 !important; }
    
    .border-top {
        border-top: 1px solid #f0f0f0 !important;
    }
</style>

@endsection