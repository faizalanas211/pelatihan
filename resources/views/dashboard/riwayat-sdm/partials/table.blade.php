<div id="table-container">

    <div class="table-responsive">
        <table class="table align-middle table-hover">
            
            {{-- ✅ HEADER --}}
            <thead class="table-light">
                <tr class="text-center">
                    <th>No</th>

                    <th class="text-start">
                        <a href="{{ request()->fullUrlWithQuery([
                            'sort' => 'nama',
                            'direction' => request('sort') == 'nama' && request('direction') == 'asc' ? 'desc' : 'asc'
                        ]) }}" class="text-decoration-none text-dark">
                            Nama
                            @if(request('sort') == 'nama')
                                <i class="bi {{ request('direction') == 'asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                            @endif
                        </a>
                    </th>

                    <th>Pelatihan</th>
                    <th>Sertifikasi</th>
                    <th>Tubel</th>

                    <th>
                        <a href="{{ request()->fullUrlWithQuery([
                            'sort' => 'jp',
                            'direction' => request('sort') == 'jp' && request('direction') == 'asc' ? 'desc' : 'asc'
                        ]) }}" class="text-decoration-none text-dark">
                            JP
                            @if(request('sort') == 'jp')
                                <i class="bi {{ request('direction') == 'asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                            @endif
                        </a>
                    </th>

                    <th>Aksi</th>
                </tr>
            </thead>

            {{-- ✅ BODY --}}
            <tbody>
                @foreach($data as $i => $row)
                <tr>
                    <td class="text-center">
                        {{ $data->firstItem() + $i }}
                    </td>

                    <td>
                        <div class="fw-semibold">{{ $row->nama }}</div>
                        <small class="text-muted">{{ $row->nip }}</small>
                    </td>

                    <td class="text-center">{{ $row->total_pelatihan }}</td>
                    <td class="text-center">{{ $row->total_sertifikasi }}</td>
                    <td class="text-center">{{ $row->total_tubel }}</td>
                    <td class="text-center fw-bold">{{ $row->total_jp }}</td>

                    <td class="text-center">
                        <a href="{{ route('riwayat-sdm.show', [
    $row->id,
    'page' => request('page')
]) }}"
class="btn btn-sm btn-outline-orange no-ajax">
    Detail
</a>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    {{-- ✅ PAGINATION (pakai punyamu) --}}
    @if($data->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
        <div class="mb-3 mb-md-0 text-muted">
            Menampilkan {{ $data->firstItem() ?? 0 }}
            sampai {{ $data->lastItem() ?? 0 }}
            dari {{ $data->total() }} data pegawai
        </div>

        <nav>
            <ul class="pagination mb-0">

                <li class="page-item {{ $data->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $data->url(1) }}">
                        <i class="bx bx-chevrons-left"></i>
                    </a>
                </li>

                <li class="page-item {{ $data->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $data->previousPageUrl() }}">
                        <i class="bx bx-chevron-left"></i>
                    </a>
                </li>

                @foreach($data->getUrlRange(
                    max(1, $data->currentPage() - 2),
                    min($data->lastPage(), $data->currentPage() + 2)
                ) as $page => $url)
                    <li class="page-item {{ $page == $data->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                <li class="page-item {{ !$data->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $data->nextPageUrl() }}">
                        <i class="bx bx-chevron-right"></i>
                    </a>
                </li>

                <li class="page-item {{ !$data->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $data->url($data->lastPage()) }}">
                        <i class="bx bx-chevrons-right"></i>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
    @endif

</div>