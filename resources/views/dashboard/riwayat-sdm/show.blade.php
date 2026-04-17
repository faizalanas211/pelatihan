@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('riwayat-sdm.index') }}" style="color: #f97316; text-decoration: none;">
        Riwayat Pengembangan SDM
    </a>
</li>
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Detail Riwayat Pegawai
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER PEGAWAI --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">{{ $pegawai->nama }}</h4>
                    <span class="text-muted">{{ $pegawai->nip }}</span>
                </div>

                <div class="d-flex gap-2">

                    <a href="{{ route('riwayat-sdm.export.detail', $pegawai->id) }}"
                    class="btn btn-orange btn-export">
                        <i class="bi bi-file-earmark-excel"></i> Export
                    </a>

                    <a href="{{ route('riwayat-sdm.index', [
                        'page' => request('page')
                    ]) }}" 
                    class="btn btn-light">
                        ← Kembali
                    </a>

                </div>
            </div>
        </div>
    </div>

    {{-- RINGKASAN --}}
    <div class="col-12">
        <div class="row g-3">

            <div class="col-md-3">
                <div class="card text-center border-0 shadow-sm rounded-4 p-3">
                    <small class="text-muted">Pelatihan</small>
                    <h4 class="fw-bold text-primary mb-0">{{ $summary['pelatihan'] }}</h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center border-0 shadow-sm rounded-4 p-3">
                    <small class="text-muted">Sertifikasi</small>
                    <h4 class="fw-bold text-success mb-0">{{ $summary['sertifikasi'] }}</h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center border-0 shadow-sm rounded-4 p-3">
                    <small class="text-muted">Tubel</small>
                    <h4 class="fw-bold text-warning mb-0">{{ $summary['tubel'] }}</h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center border-0 shadow-sm rounded-4 p-3">
                    <small class="text-muted">Total JP</small>
                    <h4 class="fw-bold mb-0">{{ $summary['jp'] }}</h4>
                </div>
            </div>

        </div>
    </div>

    {{-- PELATIHAN --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header fw-bold">Pelatihan</div>
            <div class="card-body">

                @if($pelatihan->isEmpty())
                    <div class="text-muted">Belum ada data pelatihan</div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelatihan</th>
                                <th>JP</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelatihan as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $row->jenis_pelatihan ?? '-' }}</td>
                                <td>{{ $row->jp }}</td>
                                <td>
                                    {{ $row->waktu_pelaksanaan && $row->tanggal_selesai 
                                        ? $row->waktu_pelaksanaan . ' s/d ' . $row->tanggal_selesai 
                                        : '-' }}
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

    {{-- SERTIFIKASI --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header fw-bold">Sertifikasi</div>
            <div class="card-body">

                @if($sertifikasi->isEmpty())
                    <div class="text-muted">Belum ada data sertifikasi</div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Sertifikasi</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sertifikasi as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $row->jenis_sertifikasi ?? '-' }}</td>
                                <td>{{ $row->tanggal ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- TUBEL --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header fw-bold">Tugas Belajar</div>
            <div class="card-body">

                @if($tubel->isEmpty())
                    <div class="text-muted">Belum ada data tugas belajar</div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Program</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tubel as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $row->nama_pelatihan ?? '-' }}</td>
                                <td>{{ $row->tanggal_mulai }}</td>
                                <td>{{ $row->tanggal_selesai }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>
@endsection