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

        <div class="mt-3 mb-4"
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
                                    <td class="text-center">{{ $i+1 }}</td>

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
                                        <a href="{{ route('riwayat-sdm.show', $row->id) }}"
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
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

</div>
@endsection