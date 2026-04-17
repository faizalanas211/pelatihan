<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatSDMController extends Controller
{
    public function index()
    {
        // ambil semua pegawai
        $pegawais = DB::table('pegawai')
            ->select('id', 'nama', 'nip')
            ->get();

        // rekap pelatihan
        $pelatihan = DB::table('pelatihan_peserta')
            ->select(
                'nip',
                DB::raw('COUNT(*) as total_pelatihan'),
                DB::raw('COALESCE(SUM(jp),0) as total_jp')
            )
            ->groupBy('nip')
            ->get()
            ->keyBy('nip');

        // rekap sertifikasi
        $sertifikasi = DB::table('sertifikasi_peserta')
            ->select(
                'nip',
                DB::raw('COUNT(*) as total_sertifikasi')
            )
            ->groupBy('nip')
            ->get()
            ->keyBy('nip');

        // rekap tubel (join ke pegawai biar dapet nip)
        $tubel = DB::table('tubel_peserta as t')
            ->join('pegawai as p', 't.pegawai_id', '=', 'p.id')
            ->select(
                'p.nip',
                DB::raw('COUNT(t.id) as total_tubel')
            )
            ->groupBy('p.nip')
            ->get()
            ->keyBy('nip');

        // gabungkan semua ke pegawai
        $data = $pegawais->map(function ($p) use ($pelatihan, $sertifikasi, $tubel) {

            $pPelatihan = $pelatihan[$p->nip] ?? null;
            $pSertifikasi = $sertifikasi[$p->nip] ?? null;
            $pTubel = $tubel[$p->nip] ?? null;

            return (object) [
                'id' => $p->id,
                'nama' => $p->nama,
                'nip' => $p->nip,

                'total_pelatihan' => $pPelatihan->total_pelatihan ?? 0,
                'total_sertifikasi' => $pSertifikasi->total_sertifikasi ?? 0,
                'total_tubel' => $pTubel->total_tubel ?? 0,
                'total_jp' => $pPelatihan->total_jp ?? 0,
            ];
        });

        return view('dashboard.riwayat-sdm.index', compact('data'));
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
            ->where('pp.nip', $pegawai->nip)
            ->select(
                'p.jenis_pelatihan',
                'pp.jp',
                'p.waktu_pelaksanaan',
                'p.tanggal_selesai'
            )
            ->get();

        /*
        |--------------------------------
        | SERTIFIKASI
        |--------------------------------
        */
        $sertifikasi = DB::table('sertifikasi_peserta as sp')
            ->leftJoin('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
            ->where('sp.nip', $pegawai->nip)
            ->select(
                's.jenis_sertifikasi',
                's.instansi_penerbit',
                's.tgl_terbit',
                's.tanggal_selesai',
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
                'tp.tanggal_mulai',
                'tp.tanggal_selesai',
                'tp.no_sk_tubel'
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
}
