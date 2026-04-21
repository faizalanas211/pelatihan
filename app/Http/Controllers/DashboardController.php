<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. REKAPITULASI JP PEGAWAI (dengan pagination & filter)
        $sort = $request->sort ?? 'desc';
        $rekapitulasiJP = $this->getRekapitulasiJP($sort);
        
        // Pagination manual untuk 10 data per halaman
        $currentPage = $request->page ?? 1;
        $perPage = 10; // DIUBAH JADI 10
        $offset = ($currentPage - 1) * $perPage;
        $totalData = count($rekapitulasiJP);
        $totalPages = ceil($totalData / $perPage);
        
        $rekapitulasiJPPaginated = array_slice($rekapitulasiJP, $offset, $perPage);
        
        // 2. STATISTIK KEGIATAN (filter tahun & jenis)
        $tahun = $request->tahun ?? date('Y');
        $jenis = $request->jenis ?? 'semua';
        $statistik = $this->getStatistikKegiatan($tahun, $jenis);
        
        // 3. 5 KEGIATAN TERBARU
        $kegiatanTerbaru = $this->getKegiatanTerbaru();
        
        // 4. TOTAL KESELURUHAN (tetap dihitung tapi tidak ditampilkan di card)
        $totalPelatihan = DB::table('pelatihan_peserta')->count();
        $totalSertifikasi = DB::table('sertifikasi_peserta')->count();
        $totalTubel = DB::table('tubel_peserta')->count();
        
        // 5. UNTUK ROLE PEGAWAI
        $jpPegawai = 0;
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'Admin') {
            $pegawai = Pegawai::where('user_id', auth()->user()->id)->first();
            if ($pegawai) {
                $jpPelatihan = DB::table('pelatihan_peserta')
                    ->where('nip', $pegawai->nip)
                    ->sum('jp');
                
                $jpSertifikasi = DB::table('sertifikasi_peserta as sp')
                    ->join('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
                    ->join('master_pelatihans as m', 's.master_pelatihan_id', '=', 'm.id')
                    ->where('sp.nip', $pegawai->nip)
                    ->sum('m.jp');
                
                $jpTubel = DB::table('tubel_peserta as tp')
                    ->join('master_pelatihans as m', 'tp.master_pelatihan_id', '=', 'm.id')
                    ->where('tp.pegawai_id', $pegawai->id)
                    ->sum('m.jp');
                
                $jpPegawai = $jpPelatihan + $jpSertifikasi + $jpTubel;
            }
        }
        
        return view('dashboard.main', compact(
            'rekapitulasiJPPaginated',
            'rekapitulasiJP',
            'statistik',
            'kegiatanTerbaru',
            'totalPelatihan',
            'totalSertifikasi',
            'totalTubel',
            'tahun',
            'jenis',
            'jpPegawai',
            'sort',
            'currentPage',
            'totalPages',
            'totalData',
            'perPage'
        ));
    }
    
    private function getRekapitulasiJP($sort = 'desc')
    {
        $pegawais = Pegawai::where('status', 'aktif')->get();
        
        $data = [];
        foreach ($pegawais as $pegawai) {
            $jpPelatihan = DB::table('pelatihan_peserta')
                ->where('nip', $pegawai->nip)
                ->sum('jp');
            
            $jpSertifikasi = DB::table('sertifikasi_peserta as sp')
                ->join('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
                ->join('master_pelatihans as m', 's.master_pelatihan_id', '=', 'm.id')
                ->where('sp.nip', $pegawai->nip)
                ->sum('m.jp');
            
            $jpTubel = DB::table('tubel_peserta as tp')
                ->join('master_pelatihans as m', 'tp.master_pelatihan_id', '=', 'm.id')
                ->where('tp.pegawai_id', $pegawai->id)
                ->sum('m.jp');
            
            $totalJP = $jpPelatihan + $jpSertifikasi + $jpTubel;
            $maxJP = 30;
            
            $data[] = (object)[
                'id' => $pegawai->id,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'jp' => $totalJP,
                'max_jp' => $maxJP,
                'status' => $totalJP >= $maxJP ? 'Maksimal' : 'Kurang ' . ($maxJP - $totalJP) . ' JP',
                'persen' => min(($totalJP / $maxJP) * 100, 100)
            ];
        }
        
        if ($sort == 'desc') {
            usort($data, fn($a, $b) => $b->jp <=> $a->jp);
        } else {
            usort($data, fn($a, $b) => $a->jp <=> $b->jp);
        }
        
        return $data;
    }
    
    private function getStatistikKegiatan($tahun, $jenis)
    {
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $data[$i] = 0;
        }
        
        if ($jenis == 'semua' || $jenis == 'pelatihan') {
            $pelatihan = DB::table('pelatihan_peserta')
                ->whereYear('tanggal_mulai', $tahun)
                ->select(DB::raw('MONTH(tanggal_mulai) as bulan'), DB::raw('COUNT(*) as total'))
                ->groupBy('bulan')
                ->get();
            foreach ($pelatihan as $item) {
                $data[$item->bulan] += $item->total;
            }
        }
        
        if ($jenis == 'semua' || $jenis == 'sertifikasi') {
            $sertifikasi = DB::table('sertifikasi_peserta')
                ->whereYear('tanggal_mulai', $tahun)
                ->select(DB::raw('MONTH(tanggal_mulai) as bulan'), DB::raw('COUNT(*) as total'))
                ->groupBy('bulan')
                ->get();
            foreach ($sertifikasi as $item) {
                $data[$item->bulan] += $item->total;
            }
        }
        
        if ($jenis == 'semua' || $jenis == 'tubel') {
            $tubel = DB::table('tubel_peserta')
                ->whereYear('tanggal_mulai', $tahun)
                ->select(DB::raw('MONTH(tanggal_mulai) as bulan'), DB::raw('COUNT(*) as total'))
                ->groupBy('bulan')
                ->get();
            foreach ($tubel as $item) {
                $data[$item->bulan] += $item->total;
            }
        }
        
        return [
            'labels' => $bulan,
            'values' => array_values($data)
        ];
    }
    
    private function getKegiatanTerbaru()
    {
        $kegiatan = collect();
        $tanggalSudah = [];
        
        $pelatihan = DB::table('pelatihan_peserta as pp')
            ->join('pelatihan as p', 'pp.pelatihan_id', '=', 'p.id')
            ->select(
                DB::raw("'Pelatihan' as jenis"),
                'p.jenis_pelatihan as nama',
                'pp.tanggal_mulai'
            )
            ->orderBy('pp.tanggal_mulai', 'desc')
            ->get();
        
        $sertifikasi = DB::table('sertifikasi_peserta as sp')
            ->join('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
            ->select(
                DB::raw("'Sertifikasi' as jenis"),
                's.jenis_sertifikasi as nama',
                'sp.tanggal_mulai'
            )
            ->orderBy('sp.tanggal_mulai', 'desc')
            ->get();
        
        $tubel = DB::table('tubel_peserta as tp')
            ->join('master_pelatihans as m', 'tp.master_pelatihan_id', '=', 'm.id')
            ->select(
                DB::raw("'Tugas Belajar' as jenis"),
                'm.nama_pelatihan as nama',
                'tp.tanggal_mulai'
            )
            ->orderBy('tp.tanggal_mulai', 'desc')
            ->get();
        
        $kegiatan = $pelatihan->concat($sertifikasi)->concat($tubel);
        $kegiatan = $kegiatan->sortByDesc('tanggal_mulai');
        
        $result = [];
        foreach ($kegiatan as $item) {
            $tanggalKey = date('Y-m-d', strtotime($item->tanggal_mulai));
            if (!in_array($tanggalKey, $tanggalSudah)) {
                $tanggalSudah[] = $tanggalKey;
                $result[] = $item;
            }
            if (count($result) >= 5) break;
        }
        
        return collect($result);
    }
}