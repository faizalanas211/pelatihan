@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Dashboard
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- ADMIN --}}
    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'Admin')

    {{-- HEADER SECTION --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%);">
                        <i class="bi bi-bar-chart-steps fs-1" style="color: #f97316;"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-0" style="background: linear-gradient(135deg, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            Dashboard
                        </h1>
                        <p class="text-muted mb-0 mt-1">
                            <i class="bi bi-graph-up"></i> Ringkasan data pengembangan SDM pegawai
                        </p>
                    </div>
                </div>
            </div>
            <div class="text-end" style="background: #fffbeb; padding: 10px 18px; border-radius: 20px;">
                <p class="mb-0 small" style="color: #f59e0b;">
                    <i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </p>
                <p class="small mb-0" style="color: #b87a4a;">
                    <i class="bi bi-person-circle"></i> Admin: {{ auth()->user()->name ?? 'Administrator' }}
                </p>
            </div>
        </div>
        <div class="mt-2 mb-4" style="height: 4px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    {{-- TABEL REKAPITULASI JP PEGAWAI --}}
    <div class="col-12">
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #5c4a3a;">
                            <i class="bi bi-table me-2" style="color: #f97316;"></i>
                            Rekapitulasi JP Pegawai
                        </h5>
                        <p class="text-muted small mb-0">Data Jam Pelatihan (JP) seluruh pegawai - Maksimal 30 JP</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2">
                            <input type="hidden" name="tahun" value="{{ $tahun ?? '' }}">
                            <input type="hidden" name="jenis" value="{{ $jenis ?? 'semua' }}">
                            <select name="sort" class="form-select rounded-4 shadow-sm fw-bold" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; border: none; width: auto; cursor: pointer;">
                                <option value="desc" {{ ($sort ?? 'desc') == 'desc' ? 'selected' : '' }} style="background: white; color: #f97316;">🏆 JP Terbanyak</option>
                                <option value="asc" {{ ($sort ?? 'desc') == 'asc' ? 'selected' : '' }} style="background: white; color: #f97316;">📉 JP Tersedikit</option>
                            </select>
                            <button type="submit" class="btn btn-orange">
                                <i class="bi bi-arrow-repeat me-1"></i> Urutkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead style="background: #fffbeb;">
                            <tr>
                                <th class="ps-4 py-3" style="width: 50px;">NO</th>
                                <th>NIP</th>
                                <th>Nama Pegawai</th>
                                <th>JP (Jam Pelatihan)</th>
                                <th class="pe-4">Status JP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapitulasiJPPaginated ?? [] as $index => $item)
                            <tr class="border-bottom" style="border-color: #fef3c7;">
                                <td class="ps-4 fw-bold" style="color: #b87a4a;">{{ (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $index + 1 }}</td>
                                <td class="font-monospace small" style="color: #5c4a3a;">{{ $item->nip }}</td>
                                <td class="fw-semibold" style="color: #5c4a3a;">{{ $item->nama }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold" style="color: #f97316;">{{ $item->jp }}</span>
                                        <span class="text-muted small">/ {{ $item->max_jp }} JP</span>
                                        <div class="progress" style="height: 5px; width: 80px; background: #fed7aa;">
                                            <div class="progress-bar" style="width: {{ $item->persen }}%; background: #f97316;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pe-4">
                                    @if(str_contains($item->status, 'Maksimal'))
                                        <span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">
                                            <i class="bi bi-check-circle-fill me-1 small"></i> {{ $item->status }}
                                        </span>
                                    @elseif(str_contains($item->status, 'Kurang'))
                                        <span class="badge rounded-pill" style="background: #fef3c7; color: #d97706;">
                                            <i class="bi bi-exclamation-triangle-fill me-1 small"></i> {{ $item->status }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill" style="background: #fee2e2; color: #dc2626;">
                                            <i class="bi bi-x-circle-fill me-1 small"></i> {{ $item->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data pegawai</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot style="background: #fffbeb;">
                            <tr>
                                <td colspan="4" class="ps-4 py-3 fw-semibold" style="color: #5c4a3a;">Total Pegawai</td>
                                <td class="pe-4 fw-semibold" style="color: #f97316;">{{ count($rekapitulasiJP ?? []) }} Orang</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                {{-- PAGINATION --}}
                @if(($totalPages ?? 1) > 1)
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top px-4 pb-4">
                    <div class="mb-3 mb-md-0 text-muted small">
                        Menampilkan {{ (($currentPage ?? 1) - 1) * ($perPage ?? 10) + 1 }} 
                        sampai {{ min(($currentPage ?? 1) * ($perPage ?? 10), $totalData ?? 0) }} 
                        dari {{ $totalData ?? 0 }} pegawai
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item {{ ($currentPage ?? 1) == 1 ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ route('dashboard', array_merge(request()->all(), ['page' => 1])) }}">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item {{ ($currentPage ?? 1) == 1 ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ route('dashboard', array_merge(request()->all(), ['page' => ($currentPage ?? 1) - 1])) }}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            @php
                                $start = max(1, ($currentPage ?? 1) - 2);
                                $end = min($totalPages ?? 1, ($currentPage ?? 1) + 2);
                            @endphp
                            @for($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == ($currentPage ?? 1) ? 'active' : '' }}">
                                    <a class="page-link" href="{{ route('dashboard', array_merge(request()->all(), ['page' => $i])) }}">{{ $i }}</a>
                                </li>
                            @endfor
                            
                            <li class="page-item {{ ($currentPage ?? 1) == ($totalPages ?? 1) ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ route('dashboard', array_merge(request()->all(), ['page' => ($currentPage ?? 1) + 1])) }}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item {{ ($currentPage ?? 1) == ($totalPages ?? 1) ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ route('dashboard', array_merge(request()->all(), ['page' => $totalPages ?? 1])) }}">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- GRAFIK STATISTIK KEGIATAN --}}
    <div class="col-12 mt-4">
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #5c4a3a;">
                            <i class="bi bi-graph-up me-2" style="color: #f97316;"></i>
                            Statistik Kegiatan
                        </h5>
                        <p class="text-muted small mb-0">Jumlah kegiatan (Pelatihan / Sertifikasi / Tugas Belajar)</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2">
                            <input type="hidden" name="sort" value="{{ $sort ?? 'desc' }}">
                            <input type="hidden" name="page" value="{{ $currentPage ?? 1 }}">
                            <select name="tahun" class="form-select rounded-4 shadow-sm" style="background: #fffbeb; border-color: #fed7aa; width: auto;">
                                @for($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ ($tahun ?? date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <select name="jenis" class="form-select rounded-4 shadow-sm" style="background: #fffbeb; border-color: #fed7aa; width: auto;">
                                <option value="semua" {{ ($jenis ?? 'semua') == 'semua' ? 'selected' : '' }}>Semua</option>
                                <option value="pelatihan" {{ ($jenis ?? '') == 'pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                <option value="sertifikasi" {{ ($jenis ?? '') == 'sertifikasi' ? 'selected' : '' }}>Sertifikasi</option>
                                <option value="tubel" {{ ($jenis ?? '') == 'tubel' ? 'selected' : '' }}>Tugas Belajar</option>
                            </select>
                            <button type="submit" class="btn btn-orange">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="grafikKegiatan" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    {{-- 5 KEGIATAN TERBARU --}}
    <div class="col-12 mt-2">
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0" style="color: #5c4a3a;">
                    <i class="bi bi-clock-history me-2" style="color: #f97316;"></i>
                    5 Kegiatan Terbaru
                </h5>
                <p class="text-muted small mb-0">Pelatihan, sertifikasi, dan tugas belajar terakhir (tanggal unik)</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead style="background: #fffbeb;">
                            <tr>
                                <th class="ps-4 py-3">Jenis</th>
                                <th>Nama Kegiatan</th>
                                <th class="pe-4">Tanggal Mulai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kegiatanTerbaru ?? [] as $item)
                            <tr>
                                <td class="ps-4">
                                    @if($item->jenis == 'Pelatihan')
                                        <span class="badge" style="background: #f9731620; color: #f97316;">{{ $item->jenis }}</span>
                                    @elseif($item->jenis == 'Sertifikasi')
                                        <span class="badge" style="background: #10b98120; color: #10b981;">{{ $item->jenis }}</span>
                                    @else
                                        <span class="badge" style="background: #f59e0b20; color: #f59e0b;">{{ $item->jenis }}</span>
                                    @endif
                                </td>
                                <td class="fw-semibold" style="color: #5c4a3a;">{{ $item->nama }}</td>
                                <td class="pe-4">{{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Belum ada kegiatan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ROLE PEGAWAI --}}
    @else

    <div class="col-12">
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="card-body p-5 text-center" style="background: linear-gradient(135deg, #fff, #fffbeb);">
                <div class="mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-4" style="background: linear-gradient(135deg, #f9731620, #ffedd5);">
                        <i class="bi bi-person-circle fs-1" style="color: #f97316;"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-2" style="color: #5c4a3a;">Selamat Datang, {{ auth()->user()->name ?? 'Pegawai' }}!</h3>
                <p class="text-muted mb-4">Lihat ringkasan pelatihan Anda di sini</p>
                
                <div class="row g-4 mt-2">
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 border" style="background: white; border-color: #fed7aa;">
                            <i class="bi bi-journal-bookmark-fill fs-2 mb-2 d-block" style="color: #f97316;"></i>
                            <h6 class="text-muted mb-1">Total Pelatihan</h6>
                            <h2 class="fw-bold" style="color: #f97316;">{{ $totalPelatihan ?? 0 }}</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 border" style="background: white; border-color: #fed7aa;">
                            <i class="bi bi-award-fill fs-2 mb-2 d-block" style="color: #10b981;"></i>
                            <h6 class="text-muted mb-1">Sertifikat</h6>
                            <h2 class="fw-bold" style="color: #10b981;">{{ $totalSertifikasi ?? 0 }}</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 border" style="background: white; border-color: #fed7aa;">
                            <i class="bi bi-clock-history fs-2 mb-2 d-block" style="color: #f59e0b;"></i>
                            <h6 class="text-muted mb-1">JP Pelatihan</h6>
                            <h2 class="fw-bold" style="color: #f59e0b;">{{ $jpPegawai ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('grafikKegiatan')?.getContext('2d');
    if(ctx) {
        const dataValues = {!! json_encode($statistik['values'] ?? []) !!};
        const maxValue = Math.max(...dataValues, 1);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($statistik['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']) !!},
                datasets: [{
                    label: 'Jumlah Kegiatan',
                    data: dataValues,
                    backgroundColor: 'rgba(249, 115, 22, 0.7)',
                    borderRadius: 8,
                    barPercentage: 0.65
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: function(context) { return 'Jumlah: ' + context.raw; } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(maxValue, 15),
                        min: 0,
                        ticks: { stepSize: 1, callback: function(value) { return Number.isInteger(value) ? value : null; } },
                        grid: { color: '#fef3c7' },
                        title: { display: true, text: 'Jumlah Kegiatan', color: '#b87a4a' }
                    },
                    x: {
                        grid: { display: false },
                        title: { display: true, text: 'Bulan', color: '#b87a4a' }
                    }
                }
            }
        });
    }
</script>

<style>
    .shadow-sm { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important; }
    .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.1) !important; }
    
    .btn-orange {
        background: linear-gradient(135deg, #f97316, #f59e0b);
        border: none;
        color: #fff;
        font-weight: 600;
        border-radius: 10px;
        padding: 8px 20px;
        transition: all 0.25s ease;
    }
    .btn-orange:hover { background: linear-gradient(135deg, #ea580c, #d97706); transform: translateY(-2px); color: #fff; }
    
    .btn-outline-orange { border: 1px solid #f97316; color: #f97316; background: transparent; }
    .btn-outline-orange:hover { background-color: #f97316; color: #fff; }
    
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #fffbeb; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #f97316, #f59e0b); border-radius: 10px; }
    
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .card { animation: fadeInUp 0.4s ease-out; }
    .progress { border-radius: 10px; }
    .table th, .table td { vertical-align: middle; }
    
    .pagination .page-item .page-link { color: #f97316; border-radius: 8px; }
    .pagination .page-item.active .page-link { background-color: #f97316; border-color: #f97316; color: #fff; }
    .pagination .page-link:hover { background-color: #fed7aa; color: #f97316; }
    
    select.form-select option { background: white; color: #5c4a3a; }
</style>
@endsection