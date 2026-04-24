@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active fw-semibold" style="color: #f97316;">Master Pengembangan</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- HEADER & FILTER --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f9731620 0%, #ffedd5 100%); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-database-fill-gear" style="color: #f97316; font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" style="color: #4a3728;">Master Pengembangan</h4>
                        <p class="text-muted mb-0 small">Pelatihan, Sertifikasi, & Tugas Belajar</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- FORM FILTER GANDA --}}
                    <form action="{{ route('master-pelatihan.index') }}" method="GET" class="d-flex gap-2" id="filterForm">
                        <select name="kategori" class="form-select rounded-3 border-0 shadow-sm" style="background: #f8f9fa; min-width: 150px;" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <option value="pelatihan" {{ request('kategori') == 'pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                            <option value="sertifikasi" {{ request('kategori') == 'sertifikasi' ? 'selected' : '' }}>Sertifikasi</option>
                            <option value="tubel" {{ request('kategori') == 'tubel' ? 'selected' : '' }}>Tugas Belajar</option>
                        </select>

                        <select name="tahun" class="form-select rounded-3 border-0 shadow-sm" style="background: #f8f9fa; min-width: 120px;" onchange="this.form.submit()">
                            <option value="">Semua Tahun</option>
                            @for($i = date('Y'); $i >= 2020; $i--)
                                <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </form>

                    <button class="btn rounded-4 px-4 py-2 shadow-sm d-inline-flex align-items-center fw-bold text-white border-0" 
                            style="background: linear-gradient(135deg, #f97316, #f59e0b); font-size: 0.85rem;"
                            data-bs-toggle="modal" data-bs-target="#modalPilihKategori">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #fffcf8;">
                            <tr>
                                <th class="ps-4 py-3" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase; width: 50px;">No</th>
                                <th class="py-3" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase; width: 140px;">Kategori</th>
                                <th class="py-3" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase;">Nama Item</th>
                                <th class="py-3 text-center" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase; width: 100px;">JP</th>
                                <th class="py-3 text-center" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase; width: 100px;">Tahun</th>
                                <th class="py-3 text-center" style="color: #b87a4a; font-size: 0.75rem; text-transform: uppercase; width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($masterPelatihan as $index => $item)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $masterPelatihan->firstItem() + $index }}</td>
                                <td>
                                    @php
                                        $badgeStyle = match($item->kategori) {
                                            'pelatihan' => 'background: #e0f2fe; color: #0369a1;',
                                            'sertifikasi' => 'background: #f0fdf4; color: #166534;',
                                            'tubel' => 'background: #faf5ff; color: #6b21a8;',
                                            default => 'background: #f1f5f9; color: #475569;'
                                        };
                                    @endphp
                                    <span class="badge rounded-pill px-2 py-1 text-uppercase" style="font-size: 0.6rem; {{ $badgeStyle }}">
                                        {{ str_replace('_', ' ', $item->kategori) }}
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark">{{ $item->nama_pelatihan }}</td>
                                <td class="text-center">
                                    {{-- JP HANYA TAMPIL UNTUK KATEGORI 'pelatihan' --}}
                                    @if($item->kategori == 'pelatihan')
                                        <span class="badge rounded-pill px-3 py-2" style="background: #fff3e0; color: #e65100; font-size: 0.8rem;">
                                            {{ $item->jp ? $item->jp . ' JP' : '-' }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center text-muted small fw-bold">{{ $item->tahun }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm rounded-3 shadow-sm bg-white border" style="color: #f97316;" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('master-pelatihan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm rounded-3 shadow-sm bg-white border" style="color: #e53e3e;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- MODAL EDIT --}}
                            <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 rounded-4">
                                        <div class="modal-header border-0 pb-0 ps-4 pt-4">
                                            <h5 class="fw-bold">Edit Master Data</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" style="opacity: 1;"></button>
                                        </div>
                                        <form action="{{ route('master-pelatihan.update', $item->id) }}" method="POST" class="form-edit-master">
                                            @csrf @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase text-muted">Kategori</label>
                                                    <select name="kategori" class="form-select rounded-3 border-0 shadow-sm p-3 kategori-select" style="background: #f8f9fa;" data-item-id="{{ $item->id }}">
                                                        <option value="pelatihan" {{ $item->kategori == 'pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                                        <option value="sertifikasi" {{ $item->kategori == 'sertifikasi' ? 'selected' : '' }}>Sertifikasi</option>
                                                        <option value="tubel" {{ $item->kategori == 'tubel' ? 'selected' : '' }}>Tugas Belajar</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase text-muted">Nama Item</label>
                                                    <input type="text" name="nama_pelatihan" class="form-control rounded-3 border-0 shadow-sm p-3" style="background: #f8f9fa;" value="{{ $item->nama_pelatihan }}" required>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3 area-jp-edit" id="areaJPEdit{{ $item->id }}" style="{{ $item->kategori == 'pelatihan' ? '' : 'display: none;' }}">
                                                        <label class="form-label small fw-bold text-uppercase text-muted">Jumlah JP</label>
                                                        <input type="number" name="jp" class="form-control rounded-3 border-0 shadow-sm p-3" style="background: #f8f9fa;" value="{{ $item->jp }}" placeholder="0">
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label small fw-bold text-uppercase text-muted">Tahun</label>
                                                        <select name="tahun" class="form-select rounded-3 border-0 shadow-sm p-3" style="background: #f8f9fa;">
                                                            @for($i = date('Y'); $i >= 2020; $i--)
                                                                <option value="{{ $i }}" {{ $item->tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pb-4 pe-4">
                                                <button type="button" class="btn btn-light rounded-4 px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn rounded-4 px-4 text-white fw-bold shadow-sm btn-simpan-edit" style="background: #f97316; border: none;" data-item-id="{{ $item->id }}">
                                                    <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                                                    Simpan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr><td colspan="6" class="text-center py-5">Data tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-4">
            {{ $masterPelatihan->appends(request()->input())->links() }}
        </div>
    </div>
</div>

{{-- MODAL PILIH KATEGORI --}}
<div class="modal fade" id="modalPilihKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="fw-bold" style="color: #4a3728;">Pilih Kategori Data Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="opacity: 1; margin: 0;"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <button class="btn btn-light w-100 text-start p-3 rounded-4 border d-flex align-items-center gap-3 kategori-item" onclick="bukaForm('pelatihan')">
                            <div class="p-2 rounded-3" style="background: #fff3e0; color: #f97316;"><i class="bi bi-mortarboard-fill"></i></div>
                            <div><div class="fw-bold">Pelatihan</div><div class="small text-muted">Diklat teknis, fungsional, dll</div></div>
                        </button>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-light w-100 text-start p-3 rounded-4 border d-flex align-items-center gap-3 kategori-item" onclick="bukaForm('sertifikasi')">
                            <div class="p-2 rounded-3" style="background: #fff3e0; color: #f97316;"><i class="bi bi-patch-check-fill"></i></div>
                            <div><div class="fw-bold">Sertifikasi</div><div class="small text-muted">Uji kompetensi, keahlian, BNSP, dll</div></div>
                        </button>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-light w-100 text-start p-3 rounded-4 border d-flex align-items-center gap-3 kategori-item" onclick="bukaForm('tubel')">
                            <div class="p-2 rounded-3" style="background: #fff3e0; color: #f97316;"><i class="bi bi-book-half"></i></div>
                            <div><div class="fw-bold">Tugas Belajar</div><div class="small text-muted">Kuliah S1, S2, atau S3</div></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL FORM TAMBAH --}}
<div class="modal fade" id="modalTambahData" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0 mt-2 ps-4 pt-4 pe-4">
                <h5 class="fw-bold" id="judulModalTambah">Tambah Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="opacity: 1; margin: 0;"></button>
            </div>
            <form action="{{ route('master-pelatihan.store') }}" method="POST" id="formTambahMaster">
                @csrf
                <input type="hidden" name="kategori" id="inputKategori">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted" id="labelNama">Nama Item</label>
                        <input type="text" name="nama_pelatihan" class="form-control rounded-3 border-0 shadow-sm p-3" style="background: #f8f9fa;" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3" id="areaJP">
                            <label class="form-label small fw-bold text-uppercase text-muted">Jumlah JP</label>
                            <input type="number" name="jp" class="form-control rounded-3 border-0 shadow-sm p-3" style="background: #f8f9fa;" placeholder="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Tahun</label>
                            <select name="tahun" class="form-select rounded-3 border-0 shadow-sm p-3" style="background: #f8f9fa;">
                                @for($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 pe-4">
                    <button type="button" class="btn btn-light rounded-4 px-4" onclick="kembaliPilih()">Kembali</button>
                    <button type="submit" class="btn rounded-4 px-5 text-white fw-bold shadow-sm" style="background: #f97316; border: none;" id="btnSimpanTambah">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true" id="spinnerTambah"></span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function bukaForm(kat) {
        const modalPilih = bootstrap.Modal.getInstance(document.getElementById('modalPilihKategori'));
        if (modalPilih) modalPilih.hide();
        
        const formModal = new bootstrap.Modal(document.getElementById('modalTambahData'));
        document.getElementById('inputKategori').value = kat;
        
        if(kat === 'pelatihan') {
            document.getElementById('judulModalTambah').innerText = 'Tambah Master Pelatihan';
            document.getElementById('labelNama').innerText = 'Nama Pelatihan';
            document.getElementById('areaJP').style.display = 'block';
        } else if(kat === 'sertifikasi') {
            document.getElementById('judulModalTambah').innerText = 'Tambah Master Sertifikasi';
            document.getElementById('labelNama').innerText = 'Nama Sertifikasi / Sertifikat';
            document.getElementById('areaJP').style.display = 'none';
            document.querySelector('#modalTambahData input[name="jp"]').value = '';
        } else {
            document.getElementById('judulModalTambah').innerText = 'Tambah Master Tugas Belajar';
            document.getElementById('labelNama').innerText = 'Nama Universitas / Jenjang';
            document.getElementById('areaJP').style.display = 'none';
            document.querySelector('#modalTambahData input[name="jp"]').value = '';
        }
        
        // Reset button state saat modal dibuka
        const btn = document.getElementById('btnSimpanTambah');
        const spinner = document.getElementById('spinnerTambah');
        if (btn) {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
        
        formModal.show();
    }

    function kembaliPilih() {
        bootstrap.Modal.getInstance(document.getElementById('modalTambahData')).hide();
        new bootstrap.Modal(document.getElementById('modalPilihKategori')).show();
    }

    // Untuk modal edit: sembunyikan field JP jika kategori bukan pelatihan
    document.querySelectorAll('.kategori-select').forEach(select => {
        select.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const areaJP = document.getElementById('areaJPEdit' + itemId);
            if (this.value === 'pelatihan') {
                areaJP.style.display = 'block';
            } else {
                areaJP.style.display = 'none';
                // Kosongkan nilai JP jika kategori bukan pelatihan
                areaJP.querySelector('input[name="jp"]').value = '';
            }
        });
    });

    // ✅ LOADING STATE UNTUK FORM TAMBAH (CEGAH KLIK GANDA)
    const formTambah = document.getElementById('formTambahMaster');
    if (formTambah) {
        formTambah.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnSimpanTambah');
            const spinner = document.getElementById('spinnerTambah');
            if (btn) {
                btn.disabled = true;
                spinner.classList.remove('d-none');
            }
        });
    }

    // ✅ LOADING STATE UNTUK SEMUA FORM EDIT (CEGAH KLIK GANDA)
    document.querySelectorAll('.form-edit-master').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('.btn-simpan-edit');
            if (btn) {
                const spinner = btn.querySelector('.spinner-border');
                btn.disabled = true;
                if (spinner) spinner.classList.remove('d-none');
            }
        });
    });
</script>

<style>
    .kategori-item:hover { background: #fff7ed !important; border-color: #f97316 !important; transform: scale(1.02); transition: 0.2s; }
    
    /* ✅ PERBAIKI POSISI TOMBOL CLOSE */
    .modal-header {
        position: relative;
        align-items: center;
    }
    .btn-close {
        opacity: 1;
        transition: none;
        margin: 0 !important;
    }
    .btn-close:hover {
        opacity: 1;
        background-color: transparent;
        transform: none;
    }
</style>
@endsection