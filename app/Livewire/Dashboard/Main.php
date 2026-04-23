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

    // PERBAIKAN 1: Ganti updatingSort menjadi updatedSort
    public function updatedSort()
    {
        $this->resetPage();
    }

    // PERBAIKAN 2: Tambahkan method untuk handle filter change
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

    // PERBAIKAN 3: Method khusus untuk dispatch chart update
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

        // PERBAIKAN 4: Panggil dispatch di render juga
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

    private function getRekapitulasiJPQuery()
    {
        $pelatihan = DB::table('pelatihan_peserta')
            ->select('nip', DB::raw('COALESCE(SUM(jp),0) as jp_pelatihan'))
            ->groupBy('nip');

        $sertifikasi = DB::table('sertifikasi_peserta as sp')
            ->join('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
            ->join('master_pelatihans as m', 's.master_pelatihan_id', '=', 'm.id')
            ->select('sp.nip', DB::raw('COALESCE(SUM(m.jp),0) as jp_sertifikasi'))
            ->groupBy('sp.nip');

        $tubel = DB::table('tubel_peserta as tp')
            ->join('pegawai as p', 'tp.pegawai_id', '=', 'p.id')
            ->join('master_pelatihans as m', 'tp.master_pelatihan_id', '=', 'm.id')
            ->select('p.nip', DB::raw('COALESCE(SUM(m.jp),0) as jp_tubel'))
            ->groupBy('p.nip');

        return DB::table('pegawai as p')
            ->leftJoinSub($pelatihan, 'pl', fn($j) => $j->on('p.nip','=','pl.nip'))
            ->leftJoinSub($sertifikasi, 's', fn($j) => $j->on('p.nip','=','s.nip'))
            ->leftJoinSub($tubel, 't', fn($j) => $j->on('p.nip','=','t.nip'))
            ->where('p.status', 'aktif')
            ->select(
                'p.id',
                'p.nip',
                'p.nama',
                DB::raw('COALESCE(pl.jp_pelatihan,0) as jp_pelatihan'),
                DB::raw('COALESCE(s.jp_sertifikasi,0) as jp_sertifikasi'),
                DB::raw('COALESCE(t.jp_tubel,0) as jp_tubel'),
                DB::raw('
                    (COALESCE(pl.jp_pelatihan,0)
                    + COALESCE(s.jp_sertifikasi,0)
                    + COALESCE(t.jp_tubel,0)) as jp
                ')
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
        // PERBAIKAN 5: Tambahkan data yang lebih lengkap untuk kegiatan terbaru
        $pelatihan = DB::table('pelatihan_peserta')
            ->select(DB::raw("'Pelatihan' as jenis"), 'tanggal_mulai', DB::raw("NULL as nama"))
            ->orderByDesc('tanggal_mulai')
            ->limit(3);

        $sertifikasi = DB::table('sertifikasi_peserta')
            ->select(DB::raw("'Sertifikasi' as jenis"), 'tanggal_mulai', DB::raw("NULL as nama"))
            ->orderByDesc('tanggal_mulai')
            ->limit(3);

        $tubel = DB::table('tubel_peserta')
            ->select(DB::raw("'Tugas Belajar' as jenis"), 'tanggal_mulai', DB::raw("NULL as nama"))
            ->orderByDesc('tanggal_mulai')
            ->limit(3);

        return $pelatihan
            ->union($sertifikasi)
            ->union($tubel)
            ->orderByDesc('tanggal_mulai')
            ->limit(5)
            ->get();
    }
}