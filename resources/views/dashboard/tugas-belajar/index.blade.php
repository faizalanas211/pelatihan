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
    <div class="col-12">
        <form action="{{ route('tugas-belajar.index') }}" method="GET">
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
            <span class="badge px-3 py-2" style="background: #fff3e0;">
                Total: {{ $tubel->total() }} Program
            </span>
        </div>

        <div class="row g-4">
            @forelse($tubel as $item)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex flex-column p-4">

                            <div class="d-flex justify-content-between mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:50px;height:50px;background:#fff3e0;">
                                    <i class="bi bi-mortarboard-fill text-orange fs-4"></i>
                                </div>
                                <span class="badge" style="background:#fffbeb;">
                                    {{ $item->tahun }}
                                </span>
                            </div>

                            <h5 class="fw-bold mb-2" style="color:#4a3728;">
                                {{ $item->nama_pelatihan }}
                            </h5>

                            <p class="text-muted small mb-3">
                                <i class="bi bi-bookmark"></i> Program Tugas Belajar
                            </p>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between">
                                <div class="small text-muted">
                                    @php
                                        $jumlahPeserta = DB::table('tubel_peserta')
                                            ->where('master_pelatihan_id', $item->id)
                                            ->count();
                                    @endphp
                                    <i class="bi bi-people-fill text-orange"></i>
                                    {{ $jumlahPeserta }} Peserta
                                </div>

                                <a href="{{ route('tugas-belajar.show', $item->id) }}" class="text-orange fw-bold small">
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
            {{ $tubel->appends(request()->input())->links() }}
        </div>
    </div>

</div>
@endsection