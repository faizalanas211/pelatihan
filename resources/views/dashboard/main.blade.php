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
                            <i class="bi bi-graph-up"></i> Ringkasan data pelatihan pegawai
                        </p>
                    </div>
                </div>
            </div>
            <div class="text-end" style="background: #fffbeb; padding: 10px 18px; border-radius: 20px;">
                <p class="mb-0 small" style="color: #f59e0b;">
                    <i class="bi bi-calendar3"></i> Jumat, 10 April 2026
                </p>
                <p class="small mb-0" style="color: #b87a4a;">
                    <i class="bi bi-person-circle"></i> Admin: Administrator
                </p>
            </div>
        </div>
        <div class="mt-2 mb-4" style="height: 4px; background: linear-gradient(90deg, #f97316, #f59e0b, #fbbf24, #fef3c7); border-radius: 2px;"></div>
    </div>

    {{-- TOMBOL PELATIHAN, SERTIFIKASI, TUGAS BELAJAR --}}
    <div class="col-12 mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 rounded-4 overflow-hidden shadow-sm text-center p-4" style="background: linear-gradient(135deg, #fff5eb, #ffe4cc); transition: all 0.3s ease; cursor: pointer;" onclick="alert('Menu Pelatihan akan segera hadir')">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; background: #f9731620;">
                        <i class="bi bi-journal-bookmark-fill fs-2" style="color: #f97316;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: #5c4a3a;">Pelatihan</h5>
                    <p class="small text-muted mb-0">Lihat daftar pelatihan yang tersedia</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 rounded-4 overflow-hidden shadow-sm text-center p-4" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); transition: all 0.3s ease; cursor: pointer;" onclick="alert('Menu Sertifikasi akan segera hadir')">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; background: #10b98120;">
                        <i class="bi bi-award-fill fs-2" style="color: #10b981;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: #065f46;">Sertifikasi</h5>
                    <p class="small text-muted mb-0">Lihat daftar sertifikasi pegawai</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 rounded-4 overflow-hidden shadow-sm text-center p-4" style="background: linear-gradient(135deg, #fffbeb, #fef3c7); transition: all 0.3s ease; cursor: pointer;" onclick="alert('Menu Tugas Belajar akan segera hadir')">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; background: #f59e0b20;">
                        <i class="bi bi-book-half fs-2" style="color: #f59e0b;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: #b45309;">Tugas Belajar</h5>
                    <p class="small text-muted mb-0">Lihat daftar tugas belajar pegawai</p>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL REKAPITULASI JP PEGAWAI --}}
    <div class="col-12">
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0" style="color: #5c4a3a;">
                    <i class="bi bi-table me-2" style="color: #f97316;"></i>
                    Rekapitulasi JP Pegawai
                </h5>
                <p class="text-muted small mb-0">Data Jam Pelatihan (JP) seluruh pegawai - Maksimal 5 JP</p>
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
                            @php
                                $pegawaiList = [
                                    ['nip' => '198210102006042003', 'nama' => 'Dwi Laily Sukmawati, S.Pd., M.Hum.', 'jp' => 5, 'status' => 'Maksimal'],
                                    ['nip' => '197807152002121012', 'nama' => 'Andy Rahmadi Santoso, S.Kom.', 'jp' => 4, 'status' => 'Kurang 1 JP'],
                                    ['nip' => '197809232002121003', 'nama' => 'Kahar Dwi Prihantono, S.S., M.S.', 'jp' => 5, 'status' => 'Maksimal'],
                                    ['nip' => '197903142005012001', 'nama' => 'Ika Inayati, S.S., M.Li.', 'jp' => 3, 'status' => 'Kurang 2 JP'],
                                    ['nip' => '196905251998022001', 'nama' => 'Sunarti, S.S., M.Hum.', 'jp' => 5, 'status' => 'Maksimal'],
                                    ['nip' => '198006112005012003', 'nama' => 'Ema Rahardian, S.S., M.Hum.', 'jp' => 4, 'status' => 'Kurang 1 JP'],
                                    ['nip' => '197911182005012003', 'nama' => 'Shintya, S.S., M.S.', 'jp' => 5, 'status' => 'Maksimal'],
                                    ['nip' => '198203182005012003', 'nama' => 'Afritta Dwi Martyawati, S.S., M.Hum.', 'jp' => 3, 'status' => 'Kurang 2 JP'],
                                    ['nip' => '197912212005012004', 'nama' => 'Citra Aniendita Sari, S.S., M.Hum', 'jp' => 2, 'status' => 'Kurang 3 JP'],
                                    ['nip' => '197106232002121001', 'nama' => 'Agus Sudono, S.S., M.Hum.', 'jp' => 2, 'status' => 'Kurang 3 JP'],
                                ];
                            @endphp
                            @foreach($pegawaiList as $index => $item)
                            <tr class="border-bottom" style="border-color: #fef3c7;">
                                <td class="ps-4 fw-bold" style="color: #b87a4a;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="font-monospace small" style="color: #5c4a3a;">{{ $item['nip'] }}</td>
                                <td class="fw-semibold" style="color: #5c4a3a;">{{ $item['nama'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold" style="color: #f97316;">{{ $item['jp'] }}</span>
                                        <span class="text-muted small">/ 5 JP</span>
                                        <div class="progress" style="height: 5px; width: 80px; background: #fed7aa;">
                                            <div class="progress-bar" style="width: {{ ($item['jp'] / 5) * 100 }}%; background: #f97316;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pe-4">
                                    @if($item['status'] == 'Maksimal')
                                        <span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">
                                            <i class="bi bi-check-circle-fill me-1 small"></i> {{ $item['status'] }}
                                        </span>
                                    @elseif($item['status'] == 'Kurang 1 JP')
                                        <span class="badge rounded-pill" style="background: #fef3c7; color: #d97706;">
                                            <i class="bi bi-exclamation-triangle-fill me-1 small"></i> {{ $item['status'] }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill" style="background: #fee2e2; color: #dc2626;">
                                            <i class="bi bi-x-circle-fill me-1 small"></i> {{ $item['status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background: #fffbeb;">
                            <tr>
                                <td colspan="4" class="ps-4 py-3 fw-semibold" style="color: #5c4a3a;">Total Pegawai</td>
                                <td class="pe-4 fw-semibold" style="color: #f97316;">{{ count($pegawaiList) }} Orang</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- GRAFIK PELATIHAN PERTAHUN --}}
    <div class="col-12 mt-4">
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #5c4a3a;">
                            <i class="bi bi-graph-up me-2" style="color: #f97316;"></i>
                            Statistik Pelatihan per Tahun
                        </h5>
                        <p class="text-muted small mb-0">Jumlah pelatihan yang dilaksanakan setiap tahun</p>
                    </div>
                    <select class="form-select rounded-4 w-auto shadow-sm" id="tahunGrafik" style="background: #fffbeb; border-color: #fed7aa;">
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="grafikPelatihan" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    {{-- PELATIHAN TERBARU --}}
    <div class="col-12 mt-2">
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0" style="color: #5c4a3a;">
                    <i class="bi bi-clock-history me-2" style="color: #f97316;"></i>
                    Pelatihan Terbaru
                </h5>
                <p class="text-muted small mb-0">5 pelatihan terakhir yang dilaksanakan</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead style="background: #fffbeb;">
                            <tr>
                                <th class="ps-4 py-3">Nama Pelatihan</th>
                                <th>Tanggal</th>
                                <th>Tempat</th>
                                <th>Peserta</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-semibold" style="color: #5c4a3a;">Leadership Training</td>
                                <td>15-17 Jan 2024</td>
                                <td>Ruang Serbaguna</td>
                                <td>12 orang</td>
                                <td class="pe-4"><span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">✅ Selesai</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-semibold" style="color: #5c4a3a;">Teknis IT</td>
                                <td>10-12 Feb 2024</td>
                                <td>Lab Komputer</td>
                                <td>8 orang</td>
                                <td class="pe-4"><span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">✅ Selesai</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-semibold" style="color: #5c4a3a;">Manajerial Skill</td>
                                <td>5-7 Mar 2024</td>
                                <td>Ruang Rapat</td>
                                <td>15 orang</td>
                                <td class="pe-4"><span class="badge rounded-pill" style="background: #d1fae5; color: #059669;">✅ Selesai</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-semibold" style="color: #5c4a3a;">Public Speaking</td>
                                <td>20-22 Mar 2024</td>
                                <td>Aula</td>
                                <td>20 orang</td>
                                <td class="pe-4"><span class="badge rounded-pill" style="background: #fef3c7; color: #d97706;">🔄 Berlangsung</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-semibold" style="color: #5c4a3a;">Digital Marketing</td>
                                <td>1-3 Apr 2024</td>
                                <td>Lab Multimedia</td>
                                <td>10 orang</td>
                                <td class="pe-4"><span class="badge rounded-pill" style="background: #dbeafe; color: #2563eb;">⏳ Akan Datang</span></td>
                            </tr>
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
                            <h2 class="fw-bold" style="color: #f97316;">8</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 border" style="background: white; border-color: #fed7aa;">
                            <i class="bi bi-award-fill fs-2 mb-2 d-block" style="color: #10b981;"></i>
                            <h6 class="text-muted mb-1">Sertifikat</h6>
                            <h2 class="fw-bold" style="color: #10b981;">8</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded-4 border" style="background: white; border-color: #fed7aa;">
                            <i class="bi bi-clock-history fs-2 mb-2 d-block" style="color: #f59e0b;"></i>
                            <h6 class="text-muted mb-1">JP Pelatihan</h6>
                            <h2 class="fw-bold" style="color: #f59e0b;">120</h2>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mt-4">
                    <div class="col-md-4">
                        <div class="btn w-100 rounded-4 py-2" style="background: linear-gradient(135deg, #f97316, #f59e0b); color: white; cursor: pointer;" onclick="alert('Menu Pelatihan Saya akan segera hadir')">
                            <i class="bi bi-journal-bookmark-fill me-2"></i>Pelatihan Saya
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="btn w-100 rounded-4 py-2" style="background: #fffbeb; color: #f97316; border: 1px solid #fed7aa; cursor: pointer;" onclick="alert('Menu Sertifikat Saya akan segera hadir')">
                            <i class="bi bi-award-fill me-2"></i>Sertifikat Saya
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="btn w-100 rounded-4 py-2" style="background: #fffbeb; color: #f59e0b; border: 1px solid #fed7aa; cursor: pointer;" onclick="alert('Menu Tugas Belajar Saya akan segera hadir')">
                            <i class="bi bi-book-half me-2"></i>Tugas Belajar
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
    // Grafik Pelatihan
    const ctx = document.getElementById('grafikPelatihan')?.getContext('2d');
    if(ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Pelatihan',
                    data: [5, 3, 8, 6, 4, 7, 5, 9, 4, 6, 5, 7],
                    backgroundColor: 'rgba(249, 115, 22, 0.7)',
                    borderRadius: 8,
                    barPercentage: 0.65
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#fef3c7'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
</script>

<style>
    .shadow-sm {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }
    
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.1) !important;
    }
    
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    ::-webkit-scrollbar-track {
        background: #fffbeb;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #f97316, #f59e0b);
        border-radius: 10px;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        animation: fadeInUp 0.4s ease-out;
    }
</style>

@endsection