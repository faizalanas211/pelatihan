<div>

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-orange">Dashboard</h3>

        <select wire:model.live="sort" class="form-select w-auto">
            <option value="desc">🏆 JP Terbanyak</option>
            <option value="asc">📉 JP Tersedikit</option>
        </select>
    </div>

    {{-- TABEL JP --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#fffbeb;">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>JP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapitulasiJPPaginated as $i => $row)
                        @php
                            $maxJP = 30;
                            $status = $row->jp >= $maxJP
                                ? 'Maksimal'
                                : 'Kurang ' . ($maxJP - $row->jp) . ' JP';
                        @endphp
                        <tr>
                            <td class="ps-4">{{ $loop->iteration + ($rekapitulasiJPPaginated->currentPage() - 1) * $rekapitulasiJPPaginated->perPage() }}</td>
                            <td>{{ $row->nip }}</td>
                            <td class="fw-semibold">{{ $row->nama }}</td>
                            <td>
                                <span class="badge bg-warning-subtle text-warning">
                                    {{ $row->jp }} / {{ $maxJP }}
                                </span>
                            </td>
                            <td>
                                @if($row->jp >= $maxJP)
                                    <span class="badge bg-success-subtle text-success">{{ $status }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">{{ $status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Tidak ada data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $rekapitulasiJPPaginated->links() }}
            </div>
        </div>
    </div>

<style>
    .text-orange { color:#f97316; }
    
    .btn-orange {
        background:#f97316;
        color:white;
        border:none;
    }
    .btn-orange:hover {
        background:#ea580c;
    }
    
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
</style>