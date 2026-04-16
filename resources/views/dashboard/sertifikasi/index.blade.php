@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Sertifikasi Pegawai
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
                        <i class="bi bi-award-fill" style="color: #f97316; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1.6rem;">
                            Daftar Sertifikasi
                        </h3>
                        <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">
                            <i class="bi bi-check-circle"></i> Daftar sertifikat keahlian yang telah diterbitkan
                        </p>
                    </div>
                </div>
            </div>
            <a href="{{ route('sertifikasi.create') }}" class="btn rounded-4 px-4 py-2 shadow-sm d-inline-flex align-items-center fw-semibold" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none; font-size: 0.9rem;">
                <i class="bi bi-plus-circle me-2"></i>Tambah Sertifikasi
            </a>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    {{-- FILTER TAHUN & PENCARIAN (REVISI: Sekarang Serasi dengan Rekap Pelatihan) --}}
    <div class="col-12">
        <form action="{{ route('sertifikasi.index') }}" method="GET">
            <div class="card border-0 shadow-sm rounded-4" style="background: #fffcf8;">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-1" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Tahun Terbit</label>
                            <select class="form-select rounded-3 shadow-sm border-0" name="tahun" style="background: #ffffff; color: #5c4a3a; font-size: 0.9rem;">
                                <option value="">Semua Tahun</option>
                                @for ($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold mb-1" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Cari Sertifikasi</label>
                            <div class="input-group rounded-3 overflow-hidden shadow-sm border-0">
                                <span class="input-group-text bg-white border-0">
                                    <i class="bi bi-search" style="color: #f97316;"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-0" placeholder="Nama sertifikasi atau instansi penerbit..." value="{{ request('search') }}" style="background: #ffffff; font-size: 0.9rem;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-sm fw-bold" style="background: #f97316; border: none; font-size: 0.85rem; padding: 0.6rem;">
                                    Filter
                                </button>
                                <a href="{{ route('sertifikasi.index') }}" class="btn rounded-3 shadow-sm" style="background: #ffffff; color: #f97316; border: 1px solid #fed7aa; padding: 0.6rem;">
                                    <i class="bi bi-arrow-repeat"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- LIST EVENT SERTIFIKASI --}}
    <div class="col-12 mt-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-uppercase" style="color: #5c4a3a; letter-spacing: 1px;">
                <i class="bi bi-award me-2" style="color: #f97316;"></i>
                Daftar Sertifikasi
            </h6>
            <span class="badge rounded-pill px-3 py-2" style="background: #fff3e0; color: #e65100; font-size: 0.75rem;">
                Total: {{ $sertifikasi->total() }} Sertifikat
            </span>
        </div>

        <div id="sertifikasiList">
            <div class="row g-4">
                @forelse($sertifikasi as $item)
                    <div class="col-md-4">
                        <div class="card rounded-4 border-0 shadow-sm pelatihan-card-grid h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f9731615, #ffedd5);">
                                        <i class="bi bi-patch-check-fill fs-3" style="color: #f97316;"></i>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge" style="background: #fffbeb; color: #f97316; border: 1px solid #fed7aa;">
                                            {{ \Carbon\Carbon::parse($item->tgl_terbit)->format('Y') }}
                                        </span>
                                        <br>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.65rem;">
                                            <i class="bi bi-check-all"></i> TERBIT
                                        </span>
                                    </div>
                                </div>

                                <h5 class="fw-bold mb-2" style="color: #4a3728; font-size: 1.1rem;">{{ $item->jenis_sertifikasi }}</h5>
                                
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-building me-1"></i> {{ $item->instansi_penerbit }}<br>
                                    <i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($item->tgl_terbit)->translatedFormat('d F Y') }}
                                </p>

                                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        @php
                                            $jumlahPeserta = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $item->id)->count();
                                        @endphp
                                        <i class="bi bi-people-fill text-orange me-1"></i> {{ $jumlahPeserta }} Pegawai
                                    </div>
                                    <a href="{{ route('sertifikasi.show', $item->id) }}" class="text-orange fw-bold small text-decoration-none btn-hover-link">
                                        DETAIL <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="mb-3 rounded-circle d-inline-flex p-4" style="background: #f8f9fa;">
                            <i class="bi bi-award fs-1 text-muted"></i>
                        </div>
                        <h5 class="text-muted fw-bold">Data tidak ditemukan</h5>
                        <p class="small text-muted">Belum ada data sertifikasi yang diinputkan.</p>
                    </div>
                @endforelse
            </div> 
            
            <div class="d-flex justify-content-center mt-5">
                {{ $sertifikasi->appends(request()->input())->links() }}
            </div>
        </div>
    </div>

</div>

<style>
    .pelatihan-card-grid {
        transition: all 0.3s ease;
        background: white;
        border: 1px solid #fdf2e9;
    }
    
    .pelatihan-card-grid:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px -10px rgba(249, 115, 22, 0.15) !important;
        border-color: #fed7aa;
    }

    .card-body h5 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.8rem;
    }

    .text-orange { color: #f97316 !important; }

    .btn-hover-link:hover {
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        color: #d9480f !important;
    }
    
    .pagination .page-item .page-link {
        border: none;
        background: #fff;
        color: #f97316;
        margin: 0 2px;
        border-radius: 8px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .pagination .page-item.active .page-link {
        background: #f97316;
        color: #fff;
    }
</style>
@endsection