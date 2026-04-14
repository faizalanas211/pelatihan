<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalPelatihanController extends Controller
{
    /**
     * Tampilkan Gabungan Jadwal dari Pelatihan & Sertifikasi
     */
    public function index(Request $request)
    {
        try {
            $search = $request->search;

            // 1. Ambil data dari tabel PELATIHAN (Status: mendatang & berlangsung)
            $queryPelatihan = DB::table('pelatihan')
                ->select(
                    'id', 
                    'jenis_pelatihan as nama_kegiatan', 
                    'instansi_penyelenggara as instansi', 
                    'waktu_pelaksanaan as tanggal', 
                    'status',
                    DB::raw("'pelatihan' as tipe_kegiatan") // Label pembeda
                )
                ->whereIn('status', ['mendatang', 'berlangsung']);

            if ($request->filled('search')) {
                $queryPelatihan->where(function($q) use ($search) {
                    $q->where('jenis_pelatihan', 'like', "%$search%")
                      ->orWhere('instansi_penyelenggara', 'like', "%$search%");
                });
            }

            // 2. Ambil data dari tabel SERTIFIKASI (Status: mendatang & berlangsung)
            $querySertifikasi = DB::table('sertifikasi')
                ->select(
                    'id', 
                    'jenis_sertifikasi as nama_kegiatan', 
                    'instansi_penerbit as instansi', 
                    'tgl_terbit as tanggal', 
                    'status',
                    DB::raw("'sertifikasi' as tipe_kegiatan") // Label pembeda
                )
                ->whereIn('status', ['mendatang', 'berlangsung']);

            if ($request->filled('search')) {
                $querySertifikasi->where(function($q) use ($search) {
                    $q->where('jenis_sertifikasi', 'like', "%$search%")
                      ->orWhere('instansi_penerbit', 'like', "%$search%");
                });
            }

            // 3. GABUNGKAN (UNION) Kedua Tabel
            // Kita urutkan berdasarkan tanggal terdekat (ASC)
            $combinedJadwal = $queryPelatihan->union($querySertifikasi)
                ->orderBy('tanggal', 'asc')
                ->paginate(10);

            return view('dashboard.jadwal-pelatihan.index', [
                'jadwal' => $combinedJadwal
            ]);

        } catch (\Exception $e) {
            Log::error('Error di JadwalPelatihan Index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat jadwal kegiatan.');
        }
    }
}