<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;

class Main extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $sort = 'desc';
    public $tahun;
    public $jenis = 'semua';

    public function mount()
    {
        $this->tahun = date('Y');
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function updatedTahun()
    {
        $this->resetPage();
        $this->dispatchChartUpdate();
    }

    public function updatedJenis()
    {
        $this->resetPage();
        $this->dispatchChartUpdate();
    }

    private function dispatchChartUpdate()
    {
        $statistik = $this->getStatistikKegiatan($this->tahun, $this->jenis);
        $this->dispatch('updateChart', [
            'labels' => $statistik['labels'],
            'values' => $statistik['values']
        ]);
    }

    public function render()
    {
        $query = $this->getRekapitulasiJPQuery();
        $query->orderBy('jp', $this->sort);
        $rekapitulasiJPPaginated = $query->paginate(10);

        $statistik = $this->getStatistikKegiatan($this->tahun, $this->jenis);
        $kegiatanTerbaru = $this->getKegiatanTerbaru();

        $this->dispatch('updateChart', [
            'labels' => $statistik['labels'],
            'values' => $statistik['values']
        ]);

        return view('livewire.dashboard.main', [
            'rekapitulasiJPPaginated' => $rekapitulasiJPPaginated,
            'statistik' => $statistik,
            'kegiatanTerbaru' => $kegiatanTerbaru,
        ]);
    }

    /**
     * Get rekapitulasi JP Pegawai
     * ✅ JP hanya dari tabel pelatihan_peserta (sertifikasi dan tubel tidak punya JP)
     */
    private function getRekapitulasiJPQuery()
    {
        // ✅ JP hanya dari pelatihan
        $pelatihan = DB::table('pelatihan_peserta')
            ->select('nip', DB::raw('COALESCE(SUM(jp),0) as jp_pelatihan'))
            ->groupBy('nip');

        // ✅ Sertifikasi tidak memiliki JP (nilai 0)
        // ✅ Tugas Belajar tidak memiliki JP (nilai 0)

        return DB::table('pegawai as p')
            ->leftJoinSub($pelatihan, 'pl', fn($j) => $j->on('p.nip', '=', 'pl.nip'))
            ->where('p.status', 'aktif')
            ->select(
                'p.id',
                'p.nip',
                'p.nama',
                DB::raw('COALESCE(pl.jp_pelatihan,0) as jp_pelatihan'),
                DB::raw('0 as jp_sertifikasi'),
                DB::raw('0 as jp_tubel'),
                DB::raw('COALESCE(pl.jp_pelatihan,0) as jp')
            );
    }

    private function getStatistikKegiatan($tahun, $jenis)
    {
        $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $data = array_fill(1, 12, 0);

        if ($jenis == 'semua' || $jenis == 'pelatihan') {
            $rows = DB::table('pelatihan_peserta')
                ->whereYear('tanggal_mulai', $tahun)
                ->selectRaw('MONTH(tanggal_mulai) as bulan, COUNT(*) as total')
                ->groupBy('bulan')->get();

            foreach ($rows as $r) $data[$r->bulan] += $r->total;
        }

        if ($jenis == 'semua' || $jenis == 'sertifikasi') {
            $rows = DB::table('sertifikasi_peserta')
                ->whereYear('tanggal_mulai', $tahun)
                ->selectRaw('MONTH(tanggal_mulai) as bulan, COUNT(*) as total')
                ->groupBy('bulan')->get();

            foreach ($rows as $r) $data[$r->bulan] += $r->total;
        }

        if ($jenis == 'semua' || $jenis == 'tubel') {
            $rows = DB::table('tubel_peserta')
                ->whereYear('tanggal_mulai', $tahun)
                ->selectRaw('MONTH(tanggal_mulai) as bulan, COUNT(*) as total')
                ->groupBy('bulan')->get();

            foreach ($rows as $r) $data[$r->bulan] += $r->total;
        }

        return [
            'labels' => $bulan,
            'values' => array_values($data)
        ];
    }

    private function getKegiatanTerbaru()
    {
        // Ambil 5 kegiatan terbaru dari pelatihan, sertifikasi, dan tugas belajar
        $pelatihan = DB::table('pelatihan_peserta as pp')
            ->join('pelatihan as p', 'pp.pelatihan_id', '=', 'p.id')
            ->select(
                DB::raw("'Pelatihan' as jenis"),
                'p.jenis_pelatihan as nama',
                'pp.tanggal_mulai'
            )
            ->orderByDesc('pp.tanggal_mulai');

        $sertifikasi = DB::table('sertifikasi_peserta as sp')
            ->join('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
            ->select(
                DB::raw("'Sertifikasi' as jenis"),
                's.jenis_sertifikasi as nama',
                'sp.tanggal_mulai'
            )
            ->orderByDesc('sp.tanggal_mulai');

        $tubel = DB::table('tubel_peserta as tp')
            ->join('master_pelatihans as m', 'tp.master_pelatihan_id', '=', 'm.id')
            ->select(
                DB::raw("'Tugas Belajar' as jenis"),
                'm.nama_pelatihan as nama',
                'tp.tanggal_mulai'
            )
            ->orderByDesc('tp.tanggal_mulai');

        $kegiatan = $pelatihan->union($sertifikasi)->union($tubel)
            ->orderByDesc('tanggal_mulai')
            ->limit(5)
            ->get();

        return $kegiatan;
    }
}