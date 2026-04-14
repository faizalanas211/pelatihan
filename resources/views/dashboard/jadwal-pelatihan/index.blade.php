@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Jadwal Kegiatan
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
                        <i class="bi bi-calendar-event-fill" style="color: #f97316; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1.6rem;">
                            Jadwal Kegiatan
                        </h3>
                        <p class="text-muted mb-0 mt-1" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle"></i> Agenda pelatihan & sertifikasi mendatang atau sedang berjalan
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 mb-4" style="height: 3px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    {{-- SEARCH --}}
    <div class="col-12">
        <form action="{{ route('jadwal-pelatihan.index') }}" method="GET">
            <div class="card border-0 shadow-sm rounded-4" style="background: #fffcf8;">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-10">
                            <label class="form-label fw-bold mb-1" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase;">Cari Jadwal</label>
                            <div class="input-group rounded-3 overflow-hidden shadow-sm border-0">
                                <span class="input-group-text bg-white border-0">
                                    <i class="bi bi-search" style="color: #f97316;"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-0" placeholder="Cari nama kegiatan atau instansi..." value="{{ request('search') }}" style="font-size: 0.9rem;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn w-100 rounded-3 shadow-sm fw-bold text-white" style="background: #f97316; border: none; padding: 0.6rem;">
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- LIST JADWAL --}}
    <div class="col-12 mt-2">
        <div class="row g-4">
            @forelse($jadwal as $item)
                <div class="col-md-4">
                    {{-- Card dengan border warna dinamis berdasarkan status --}}
                    <div class="card rounded-4 border-0 shadow-sm h-100 border-start border-4 pelatihan-card-grid {{ $item->status == 'berlangsung' ? 'border-warning' : 'border-orange' }}">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    @if($item->status == 'berlangsung')
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.65rem;">
                                            <i class="bi bi-play-circle-fill me-1"></i> SEDANG JALAN
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.65rem;">
                                            <i class="bi bi-calendar-check me-1"></i> MENDATANG
                                        </span>
                                    @endif
                                </div>
                                {{-- BADGE TIPE (Sertifikasi / Pelatihan) --}}
                                <span class="badge text-uppercase" style="background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; font-size: 0.6rem;">
                                    {{ $item->tipe_kegiatan }}
                                </span>
                            </div>

                            <h5 class="fw-bold mb-3" style="color: #4a3728; font-size: 1.1rem; min-height: 2.8rem;">
                                {{ $item->nama_kegiatan }}
                            </h5>
                            
                            <div class="small text-muted mb-4">
                                <div class="mb-2"><i class="bi bi-building me-2 text-orange"></i>{{ $item->instansi }}</div>
                                <div><i class="bi bi-calendar3 me-2 text-orange"></i>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</div>
                            </div>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <div class="small fw-bold text-muted">
                                    <i class="bi bi-people-fill text-orange me-1"></i>
                                    @if($item->tipe_kegiatan == 'sertifikasi')
                                        {{ DB::table('sertifikasi_peserta')->where('sertifikasi_id', $item->id)->count() }} Orang
                                    @else
                                        {{ DB::table('pelatihan_peserta')->where('pelatihan_id', $item->id)->count() }} Peserta
                                    @endif
                                </div>

                                {{-- Link Detail Dinamis --}}
                                @php
                                    $route = $item->tipe_kegiatan == 'sertifikasi' ? 'sertifikasi.show' : 'rekap-pelatihan.show';
                                @endphp
                                <a href="{{ route($route, $item->id) }}" class="text-orange fw-bold small text-decoration-none btn-hover-link">
                                    LIHAT DETAIL <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="mb-3 rounded-circle d-inline-flex p-4" style="background: #f8f9fa;">
                        <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    </div>
                    <h5 class="text-muted fw-bold">Tidak ada agenda terdekat</h5>
                    <p class="small text-muted">Pastikan status kegiatan diset ke "Akan Datang" atau "Sedang Berlangsung".</p>
                </div>
            @endforelse
        </div>
        
        <div class="d-flex justify-content-center mt-5">
            {{ $jadwal->links() }}
        </div>
    </div>
</div>

<style>
    .pelatihan-card-grid { transition: all 0.3s ease; background: white; }
    .pelatihan-card-grid:hover { transform: translateY(-8px); box-shadow: 0 15px 30px -10px rgba(249, 115, 22, 0.15) !important; }
    .border-orange { border-color: #f97316 !important; }
    .text-orange { color: #f97316 !important; }
    .btn-hover-link:hover { letter-spacing: 0.5px; transition: all 0.2s ease; color: #d9480f !important; }
</style>
@endsection