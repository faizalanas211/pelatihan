@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">
    Data Pegawai
</li>
@endsection

@section('content')

<style>
.btn-success-soft{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    border:none;
    color:#fff;
    font-weight:600;
    box-shadow:0 4px 14px rgba(34,197,94,.35);
}

.btn-success-soft:hover{
    background:linear-gradient(135deg,#16a34a,#15803d);
}

.table-hover tbody tr:hover{
    background:#f0fdf4;
}

.action-icon{
    transition:.2s;
}

.action-icon.edit{color:#16a34a;}
.action-icon.edit:hover{color:#15803d;transform:scale(1.1);}

.action-icon.delete{color:#ef4444;}
.action-icon.delete:hover{color:#dc2626;transform:scale(1.1);}
</style>

<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-people" style="color: #f97316; font-size: 1.5rem;"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color: #4a3728;">Data Pegawai</h4>
                <p class="text-muted mb-0 small">Data Pegawai Aktif Balai Bahasa Provinsi Jawa Tengah</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pegawai.create') }}" class="btn btn-orange"><i class="bi bi-plus-circle me-2"></i> Tambah Data</a>
        </div>
    </div>
    

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-orange">
                <tr>
                    <th>#</th>
                    <th>Nama Pegawai</th>
                    <th>NIP</th>
                    <th>Jabatan</th>
                    <th>Pangkat, Golongan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pegawais as $item)
                    <tr>
                        <td>{{ $pegawais->firstItem() + $loop->index }}</td>

                        <td>
                            <strong class="text-black">
                                {{ ucwords($item->nama) }}
                            </strong>
                        </td>

                        <td>{{ $item->nip }}</td>

                        <td>{{ $item->jabatan }}</td>

                        <td>{{ $item->pangkat_golongan }}</td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center">

                                {{-- EDIT --}}
                                <a href="{{ route('pegawai.edit', [$item->id, 'page' => request()->input('page', 1)]) }}"
                                   class="me-3 action-icon edit"
                                   title="Edit">
                                    <i class="bx bx-edit bx-sm"></i>
                                </a>

                                {{-- DELETE --}}
                                <form action="{{ route('pegawai.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus data pegawai ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0 action-icon delete" title="Hapus">
                                        <i class="bx bx-trash bx-sm"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Tidak ada data pegawai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($pegawais->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
            <div class="mb-3 mb-md-0 text-muted">
                Menampilkan {{ $pegawais->firstItem() ?? 0 }}
                sampai {{ $pegawais->lastItem() ?? 0 }}
                dari {{ $pegawais->total() }} data pegawai
            </div>

            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item {{ $pegawais->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->url(1) }}">
                            <i class="bx bx-chevrons-left"></i>
                        </a>
                    </li>

                    <li class="page-item {{ $pegawais->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->previousPageUrl() }}">
                            <i class="bx bx-chevron-left"></i>
                        </a>
                    </li>

                    @foreach($pegawais->getUrlRange(
                        max(1, $pegawais->currentPage() - 2),
                        min($pegawais->lastPage(), $pegawais->currentPage() + 2)
                    ) as $page => $url)
                        <li class="page-item {{ $page == $pegawais->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    <li class="page-item {{ !$pegawais->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->nextPageUrl() }}">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </li>

                    <li class="page-item {{ !$pegawais->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->url($pegawais->lastPage()) }}">
                            <i class="bx bx-chevrons-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

<script>
document.getElementById('checkAll')?.addEventListener('change', function () {
    document.querySelectorAll('.checkItem').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>

<style>
.table-orange {
    background:#fff7ed;
}

.text-orange {
    color:#f97316;
}

.btn-orange{
    background: linear-gradient(135deg,#f97316,#f59e0b);
    border: none;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(249,115,22,.35);
}

.btn-orange:hover{
    background: linear-gradient(135deg,#ea580c,#d97706);
    color: #fff;
}

.table-hover tbody tr:hover{
    background:#fff7ed;
}

.action-icon{
    transition:.2s;
}

/* EDIT jadi ORANGE */
.action-icon.edit{
    color:#f97316;
}
.action-icon.edit:hover{
    color:#ea580c;
    transform:scale(1.1);
}

/* DELETE biarin merah */
.action-icon.delete{
    color:#ef4444;
}
.action-icon.delete:hover{
    color:#dc2626;
    transform:scale(1.1);
}

/* PAGINATION ORANGE */
.pagination .page-link{
    color:#f97316;
    border-radius:8px;
}

.pagination .page-item.active .page-link{
    background:#f97316;
    border-color:#f97316;
    color:#fff;
}

.pagination .page-link:hover{
    background:#fed7aa;
    color:#f97316;
}
</style>

@endsection
