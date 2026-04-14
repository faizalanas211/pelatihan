<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RekapPelatihanController extends Controller
{
    /**
     * Helper untuk menentukan status berdasarkan tanggal
     */
    private function determineStatus($waktu_pelaksanaan)
    {
        $today = Carbon::today();
        $pelaksanaan = Carbon::parse($waktu_pelaksanaan);

        if ($pelaksanaan->isPast() && !$pelaksanaan->isToday()) {
            return 'selesai';
        } elseif ($pelaksanaan->isToday()) {
            return 'berlangsung';
        } else {
            return 'mendatang';
        }
    }

    /**
     * Tampilkan daftar event pelatihan yang sudah SELESAI
     */
    public function index(Request $request)
    {
        try {
            $today = Carbon::today()->toDateString();

            // SINKRONISASI OTOMATIS: 
            DB::table('pelatihan')
                ->where('waktu_pelaksanaan', '<', $today)
                ->where('status', '!=', 'selesai')
                ->update(['status' => 'selesai', 'updated_at' => now()]);

            $query = DB::table('pelatihan')->where('status', 'selesai');
            
            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('jenis_pelatihan', 'like', '%' . $search . '%')
                      ->orWhere('instansi_penyelenggara', 'like', '%' . $search . '%');
                });
            }
            
            $pelatihan = $query->orderBy('waktu_pelaksanaan', 'desc')->paginate(10);
            
            return view('dashboard.rekap-pelatihan.index', compact('pelatihan'));
            
        } catch (\Exception $e) {
            Log::error('Error di RekapPelatihan Index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function create()
    {
        $pegawais = Pegawai::where('status', 'aktif')->orderBy('nama')->get();
        return view('dashboard.rekap-pelatihan.create', compact('pegawais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'peserta'           => 'required|array',
            'peserta.*'         => 'required',
            'jenis_pelatihan'   => 'required|string',
            'tahun_pelatihan'   => 'required',
            'waktu_pelaksanaan' => 'required|date',
            'instansi'          => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $statusOtomatis = $this->determineStatus($request->waktu_pelaksanaan);

            $pelatihanId = DB::table('pelatihan')->insertGetId([
                'jenis_pelatihan'        => $request->jenis_pelatihan,
                'tahun'                  => $request->tahun_pelatihan,
                'waktu_pelaksanaan'      => $request->waktu_pelaksanaan,
                'instansi_penyelenggara' => $request->instansi,
                'status'                 => $statusOtomatis, 
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            foreach ($request->peserta as $p) {
                $parts = explode('|', $p);
                DB::table('pelatihan_peserta')->insert([
                    'pelatihan_id' => $pelatihanId,
                    'nip'          => $parts[0],
                    'nama_peserta' => $parts[1],
                    'jp'           => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();

            $targetRoute = ($statusOtomatis == 'selesai') ? 'rekap-pelatihan.index' : 'jadwal-pelatihan.index';
            
            return redirect()->route($targetRoute)
                             ->with('success', "Pelatihan berhasil disimpan dengan status: $statusOtomatis");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan pelatihan: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * PERBAIKAN UTAMA: Mengarah ke file rekap-pelatihan-show.blade.php
     */
    public function show($id)
    {
        try {
            $pelatihan = DB::table('pelatihan')->where('id', $id)->first();
            
            if (!$pelatihan) {
                return redirect()->route('rekap-pelatihan.index')->with('error', 'Data pelatihan tidak ditemukan.');
            }

            $peserta = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();

            // Di sini kita sesuaikan dengan nama file kamu: rekap-pelatihan-show.blade.php
            return view('dashboard.rekap-pelatihan.rekap-pelatihan-show', compact('pelatihan', 'peserta'));
            
        } catch (\Exception $e) {
            Log::error('Error Detail Rekap: ' . $e->getMessage());
            return redirect()->route('rekap-pelatihan.index')->with('error', 'Gagal memuat detail.');
        }
    }

    /**
     * Update Sertifikat & JP
     */
    public function uploadSertifikatPeserta(Request $request, $id)
    {
        $request->validate([
            'jp'         => 'nullable|numeric',
            'sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $peserta = DB::table('pelatihan_peserta')->where('id', $id)->first();
            if (!$peserta) return redirect()->back()->with('error', 'Data tidak ditemukan.');

            // Sinkronisasi JP massal (Opsional, sesuai logika kamu)
            if ($request->filled('jp')) {
                DB::table('pelatihan_peserta')
                    ->where('pelatihan_id', $peserta->pelatihan_id)
                    ->update(['jp' => $request->jp, 'updated_at' => now()]);
            }

            // Upload Sertifikat
            if ($request->hasFile('sertifikat')) {
                if ($peserta->sertifikat_path) {
                    Storage::disk('public')->delete($peserta->sertifikat_path);
                }

                $file = $request->file('sertifikat');
                $fileName = time() . '_' . $peserta->nip . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('uploads/sertifikat_peserta', $fileName, 'public');
                
                DB::table('pelatihan_peserta')->where('id', $id)->update([
                    'sertifikat_path' => $filePath,
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Berhasil memperbarui data.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function edit($id)
    {
        $pelatihan = DB::table('pelatihan')->where('id', $id)->first();
        if (!$pelatihan) abort(404);
        
        $pesertaTerpilih = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();
        $pegawais = Pegawai::where('status', 'aktif')->orderBy('nama')->get();
        
        return view('dashboard.rekap-pelatihan.edit', compact('pelatihan', 'pesertaTerpilih', 'pegawais'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'peserta'           => 'required|array',
            'jenis_pelatihan'   => 'required|string',
            'tahun_pelatihan'   => 'required',
            'waktu_pelaksanaan' => 'required|date',
            'instansi'          => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $statusOtomatis = $this->determineStatus($request->waktu_pelaksanaan);

            DB::table('pelatihan')->where('id', $id)->update([
                'jenis_pelatihan'        => $request->jenis_pelatihan,
                'tahun'                  => $request->tahun_pelatihan,
                'waktu_pelaksanaan'      => $request->waktu_pelaksanaan,
                'instansi_penyelenggara' => $request->instansi,
                'status'                 => $statusOtomatis,
                'updated_at'             => now(),
            ]);

            $oldDataPeserta = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get()->keyBy('nip');
            DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->delete();

            foreach ($request->peserta as $p) {
                $parts = explode('|', $p);
                $nip = $parts[0];
                DB::table('pelatihan_peserta')->insert([
                    'pelatihan_id'    => $id,
                    'nip'             => $nip,
                    'nama_peserta'    => $parts[1],
                    'jp'              => $oldDataPeserta[$nip]->jp ?? null,
                    'sertifikat_path' => $oldDataPeserta[$nip]->sertifikat_path ?? null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('rekap-pelatihan.index')->with('success', 'Data diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan.');
        }
    }

    public function destroy($id)
    {
        try {
            $peserta = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();
            foreach ($peserta as $p) {
                if ($p->sertifikat_path) Storage::disk('public')->delete($p->sertifikat_path);
            }
            DB::table('pelatihan')->where('id', $id)->delete();
            return redirect()->route('rekap-pelatihan.index')->with('success', 'Data dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }
}