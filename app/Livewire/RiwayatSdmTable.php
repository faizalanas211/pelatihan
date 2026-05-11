<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class RiwayatSdmTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sort = 'total_jp';
    public $direction = 'desc';
    public $tahun = ''; // ✅ TAMBAHKAN INI

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTahun() // ✅ TAMBAHKAN INI
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
    }

    public function render()
    {
        // 🔹 SUBQUERY PELATIHAN (dengan filter tahun)
        $pelatihan = DB::table('pelatihan_peserta')
            ->select('nip', DB::raw('COUNT(*) total_pelatihan'), DB::raw('COALESCE(SUM(jp),0) as total_jp'))
            ->when($this->tahun, function ($q) {
                $q->whereYear('tanggal_mulai', $this->tahun);
            })
            ->groupBy('nip');

        // 🔹 SUBQUERY SERTIFIKASI (dengan filter tahun)
        $sertifikasi = DB::table('sertifikasi_peserta')
            ->select('nip', DB::raw('COUNT(*) total_sertifikasi'))
            ->when($this->tahun, function ($q) {
                $q->whereYear('tanggal_mulai', $this->tahun);
            })
            ->groupBy('nip');

        // 🔹 SUBQUERY TUBEL (dengan filter tahun)
        $tubel = DB::table('tubel_peserta as t')
            ->join('pegawai as p', 't.pegawai_id', '=', 'p.id')
            ->select('p.nip', DB::raw('COUNT(*) total_tubel'))
            ->when($this->tahun, function ($q) {
                $q->whereYear('t.tanggal_mulai', $this->tahun);
            })
            ->groupBy('p.nip');

        $data = DB::table('pegawai as p')
            ->leftJoinSub($pelatihan, 'pl', fn($j) => $j->on('p.nip','=','pl.nip'))
            ->leftJoinSub($sertifikasi, 's', fn($j) => $j->on('p.nip','=','s.nip'))
            ->leftJoinSub($tubel, 't', fn($j) => $j->on('p.nip','=','t.nip'))
            ->where('p.status', 'aktif')
            ->when($this->search, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('p.nama', 'like', "%{$this->search}%")
                            ->orWhere('p.nip', 'like', "%{$this->search}%");
                    });
                })
            ->select(
                'p.id','p.nama','p.nip',
                DB::raw('COALESCE(pl.total_pelatihan,0) as total_pelatihan'),
                DB::raw('COALESCE(s.total_sertifikasi,0) as total_sertifikasi'),
                DB::raw('COALESCE(t.total_tubel,0) as total_tubel'),
                DB::raw('COALESCE(pl.total_jp,0) as total_jp')
            )
            ->orderBy($this->sort, $this->direction)
            ->paginate(30);

        return view('livewire.riwayat-sdm-table', compact('data'));
    }
}