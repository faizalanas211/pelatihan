<?php

namespace App\Http\Controllers;

use App\Exports\RiwayatSdmDetailExport;
use App\Exports\RiwayatSdmExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RiwayatSDMController extends Controller
{
    public function index()
    {
        return view('dashboard.riwayat-sdm.index');
    }
    
    public function show($id)
    {
        try {
            $pegawai = DB::table('pegawai')->where('id', $id)->first();

            if (!$pegawai) {
                return back()->with('error', 'Pegawai tidak ditemukan');
            }

            /*
            |--------------------------------
            | PELATIHAN
            |--------------------------------
            */
            $pelatihan = DB::table('pelatihan_peserta as pp')
                ->leftJoin('pelatihan as p', 'pp.pelatihan_id', '=', 'p.id')
                ->leftJoin('master_pelatihans as mp', 'p.master_pelatihan_id', '=', 'mp.id')
                ->where('pp.nip', $pegawai->nip)
                ->select(
                    'p.jenis_pelatihan',
                    'p.tahun',
                    'pp.jp',
                    'pp.tanggal_mulai',
                    'pp.tanggal_selesai',
                    'mp.instansi as instansi_penyelenggara'
                )
                ->get();

            /*
            |--------------------------------
            | SERTIFIKASI
            |--------------------------------
            */
            $sertifikasi = DB::table('sertifikasi_peserta as sp')
                ->leftJoin('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
                ->leftJoin('master_pelatihans as mp', 's.master_pelatihan_id', '=', 'mp.id')
                ->where('sp.nip', $pegawai->nip)
                ->select(
                    's.jenis_sertifikasi',
                    'mp.instansi as instansi_penerbit',
                    'sp.tanggal_perolehan',
                    'sp.masa_berlaku'
                )
                ->get();

            /*
            |--------------------------------
            | TUBEL
            |--------------------------------
            */
            $tubel = DB::table('tubel_peserta as tp')
                ->leftJoin('master_pelatihans as mp', 'tp.master_pelatihan_id', '=', 'mp.id')
                ->where('tp.pegawai_id', $pegawai->id)
                ->select(
                    'mp.nama_pelatihan',
                    'mp.jenjang',
                    'mp.jurusan',
                    'mp.instansi as universitas',
                    'mp.tahun',
                    'tp.tanggal_mulai',
                    'tp.tanggal_selesai',
                    'tp.status'
                )
                ->get();

            $summary = [
                'pelatihan'   => $pelatihan->count(),
                'sertifikasi' => $sertifikasi->count(),
                'tubel'       => $tubel->count(),
                'jp'          => $pelatihan->sum('jp'),
            ];

            return view('dashboard.riwayat-sdm.show', compact(
                'pegawai',
                'pelatihan',
                'sertifikasi',
                'tubel',
                'summary'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail: ' . $e->getMessage());
        }
    }

    /**
     * EXPORT: Export semua pegawai dengan format DETAIL PER PELATIHAN
     * (Setiap pelatihan/sertifikasi/tubel menjadi baris terpisah)
     * ✅ Diurutkan berdasarkan JUMLAH PELATIHAN dari terbanyak ke tersedikit
     * ✅ Mendukung filter tahun
     */
    public function export()
    {
        $search = request('search');
        $tahun = request('tahun');

        // Ambil semua pegawai aktif dengan filter
        $pegawais = DB::table('pegawai')
            ->where('status', 'aktif')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%$search%")
                        ->orWhere('nip', 'like', "%$search%");
                });
            })
            ->select('id', 'nama', 'nip')
            ->get();

        $data = [];
        
        foreach ($pegawais as $pegawai) {
            // ✅ PELATIHAN (ambil tahun juga)
            $pelatihan = DB::table('pelatihan_peserta as pp')
                ->leftJoin('pelatihan as p', 'pp.pelatihan_id', '=', 'p.id')
                ->where('pp.nip', $pegawai->nip)
                ->when($tahun, function ($q) use ($tahun) {
                    $q->whereYear('pp.tanggal_mulai', $tahun);
                })
                ->select('p.jenis_pelatihan', 'pp.jp', 'p.tahun')
                ->get();
            
            // ✅ SERTIFIKASI (ambil masa berlaku)
            $sertifikasi = DB::table('sertifikasi_peserta as sp')
                ->leftJoin('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
                ->where('sp.nip', $pegawai->nip)
                ->when($tahun, function ($q) use ($tahun) {
                    $q->whereYear('sp.tanggal_perolehan', $tahun);
                })
                ->select('s.jenis_sertifikasi', 'sp.masa_berlaku')
                ->get();
            
            // ✅ TUBEL (ambil jenjang, jurusan, instansi, tahun)
            $tubel = DB::table('tubel_peserta as tp')
                ->leftJoin('master_pelatihans as mp', 'tp.master_pelatihan_id', '=', 'mp.id')
                ->where('tp.pegawai_id', $pegawai->id)
                ->when($tahun, function ($q) use ($tahun) {
                    $q->whereYear('tp.tanggal_mulai', $tahun);
                })
                ->select(
                    'mp.nama_pelatihan',
                    'mp.jenjang',
                    'mp.jurusan',
                    'mp.instansi',
                    'mp.tahun'
                )
                ->get();
            
            $data[] = [
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'jumlah_pelatihan' => $pelatihan->count(),
                'pelatihan' => $pelatihan,
                'sertifikasi' => $sertifikasi,
                'tubel' => $tubel,
            ];
        }
        
        // ✅ URUTKAN BERDASARKAN JUMLAH PELATIHAN (TERBANYAK KE TERSEDIKIT)
        usort($data, function($a, $b) {
            return $b['jumlah_pelatihan'] <=> $a['jumlah_pelatihan'];
        });
        
        // ✅ Nama file ditambahkan tahun jika difilter
        $fileName = 'riwayat_sdm' . ($tahun ? '_' . $tahun : '') . '.xlsx';
        
        return Excel::download(new RiwayatSdmExport($data), $fileName);
    }

    public function exportDetail($id)
    {
        $pegawai = DB::table('pegawai')->where('id', $id)->first();

        if (!$pegawai) {
            abort(404, 'Pegawai tidak ditemukan');
        }

        $pelatihan = DB::table('pelatihan_peserta as pp')
            ->leftJoin('pelatihan as p', 'pp.pelatihan_id', '=', 'p.id')
            ->leftJoin('master_pelatihans as mp', 'p.master_pelatihan_id', '=', 'mp.id')
            ->where('pp.nip', $pegawai->nip)
            ->select(
                'p.jenis_pelatihan',
                'p.tahun',
                'mp.instansi as instansi_penyelenggara',
                'pp.jp',
                'pp.tanggal_mulai',
                'pp.tanggal_selesai'
            )
            ->get();

        $sertifikasi = DB::table('sertifikasi_peserta as sp')
            ->leftJoin('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
            ->leftJoin('master_pelatihans as mp', 's.master_pelatihan_id', '=', 'mp.id')
            ->where('sp.nip', $pegawai->nip)
            ->select(
                's.jenis_sertifikasi',
                'mp.instansi as instansi_penerbit',
                'sp.tanggal_perolehan',
                'sp.masa_berlaku'
            )
            ->get();

        $tubel = DB::table('tubel_peserta as t')
            ->leftJoin('master_pelatihans as mp', 't.master_pelatihan_id', '=', 'mp.id')
            ->join('pegawai as p', 't.pegawai_id', '=', 'p.id')
            ->where('p.id', $id)
            ->select(
                't.*',
                'mp.nama_pelatihan',
                'mp.jenjang',
                'mp.jurusan',
                'mp.instansi as universitas',
                'mp.tahun',
                't.status'
            )
            ->get();

        return Excel::download(
            new RiwayatSdmDetailExport($pegawai, $pelatihan, $sertifikasi, $tubel),
            'riwayat_' . $pegawai->nama . '.xlsx'
        );
    }
}