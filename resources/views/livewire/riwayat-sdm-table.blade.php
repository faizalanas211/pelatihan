<div>

    <div class="d-flex justify-content-between align-items-center mb-3">
    
    {{-- SEARCH --}}
    <input type="text"
        wire:model.live.debounce.400ms="search"
        class="form-control w-50"
        placeholder="Cari nama / NIP...">

    {{-- EXPORT --}}
    <a href="{{ route('riwayat-sdm.export', [
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction
        ]) }}"
        class="btn btn-orange d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-excel"></i>
        Export Excel
    </a>

</div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle">

            {{-- HEADER --}}
            <thead class="table-light">
                <tr>
                    <th>No</th>

                    {{-- NAMA --}}
                    <th wire:click="sortBy('nama')" style="cursor:pointer;">
                        Nama
                        @if($sort=='nama')
                            <i class="bi {{ $direction=='asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </th>

                    {{-- JP --}}
                    <th wire:click="sortBy('total_jp')" class="text-center" style="cursor:pointer;">
                        JP
                        @if($sort=='total_jp')
                            <i class="bi {{ $direction=='asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </th>

                    {{-- PELATIHAN --}}
                    <th wire:click="sortBy('total_pelatihan')" class="text-center" style="cursor:pointer;">
                        Pelatihan
                        @if($sort=='total_pelatihan')
                            <i class="bi {{ $direction=='asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </th>

                    {{-- SERTIFIKASI --}}
                    <th wire:click="sortBy('total_sertifikasi')" class="text-center" style="cursor:pointer;">
                        Sertifikasi
                        @if($sort=='total_sertifikasi')
                            <i class="bi {{ $direction=='asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </th>

                    {{-- TUBEL --}}
                    <th wire:click="sortBy('total_tubel')" class="text-center" style="cursor:pointer;">
                        Tubel
                        @if($sort=='total_tubel')
                            <i class="bi {{ $direction=='asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </th>

                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            {{-- BODY --}}
            <tbody>
                @forelse($data as $i => $row)
                <tr>
                    <td>{{ $data->firstItem() + $i }}</td>

                    {{-- NAMA --}}
                    <td>
                        <div class="fw-semibold">{{ $row->nama }}</div>
                        <small class="text-muted">{{ $row->nip }}</small>
                    </td>

                    {{-- JP --}}
                    <td class="text-center">
                        <span class="badge bg-dark-subtle text-dark px-3 py-2 rounded-pill">
                            {{ $row->total_jp }}
                        </span>
                    </td>

                    {{-- PELATIHAN --}}
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                            {{ $row->total_pelatihan }}
                        </span>
                    </td>

                    {{-- SERTIFIKASI --}}
                    <td class="text-center">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                            {{ $row->total_sertifikasi }}
                        </span>
                    </td>

                    {{-- TUBEL --}}
                    <td class="text-center">
                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                            {{ $row->total_tubel }}
                        </span>
                    </td>

                    <td class="text-center">
                        <a href="{{ route('riwayat-sdm.show', $row->id) }}"
                        class="btn btn-sm btn-outline-orange d-inline-flex align-items-center gap-1 px-2 py-1">
                            <i class="bi bi-eye"></i>
                            <span class="d-none d-md-inline">Detail</span>
                        </a>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Tidak ada data
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $data->links() }}
    </div>

</div>