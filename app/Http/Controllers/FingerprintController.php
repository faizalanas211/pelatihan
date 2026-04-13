<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rats\Zkteco\Lib\ZKTeco;
use App\Models\Pegawai;
use App\Models\Kehadiran;
use Carbon\Carbon;

class FingerprintController extends Controller
{
    /**
     * Test koneksi & lihat raw data dari fingerprint
     */
    public function testBuffer()
    {
        $zk = new ZKTeco('10.11.8.164');

        if (!$zk->connect()) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal konek ke fingerprint'
            ]);
        }

        // Ambil raw data attendance
        $data = $zk->getAttendance();

        // Jika kosong
        if (!$data || count($data) == 0) {
            return response()->json([
                'status' => true,
                'message' => 'Koneksi berhasil, tapi data attendance kosong',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data attendance berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * Sinkronisasi log fingerprint ke database
     */
    public function sync()
    {
        $zk = new ZKTeco('10.11.8.164');

        if (!$zk->connect()) {
            return "❌ Gagal konek ke fingerprint";
        }

        $logs = $zk->getAttendance();

        foreach ($logs as $log) {

            $nip = $log['id'];
            $waktu = Carbon::parse($log['timestamp']);
            $tanggal = $waktu->format('Y-m-d');
            $jam = $waktu->format('H:i:s');

            // Cari pegawai berdasarkan NIP
            $pegawai = Pegawai::where('nip', $nip)->first();

            if (!$pegawai) {
                continue;
            }

            // Cek sudah absen atau belum
            $sudahAda = Kehadiran::where('pegawai_id', $pegawai->id)
                        ->where('tanggal', $tanggal)
                        ->exists();

            if ($sudahAda) {
                continue;
            }

            // Simpan ke DB
            Kehadiran::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => $tanggal,
                'status' => 'hadir',
                'jam_masuk' => $jam,
                'keterangan' => null,
            ]);
        }

        return "✔ Sinkronisasi absensi berhasil!";
    }
}
