<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\MasterPelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SertifikasiController extends Controller
{
    /**
     * Helper status otomatis
     */
    private function determineStatus($tglMulai, $tglSelesai)
    {
        $today = Carbon::today();
        $mulai = Carbon::parse($tglMulai);
        $selesai = Carbon::parse($tglSelesai);

        if ($today->gt($selesai)) {
            return 'selesai'; 
        } elseif ($today->between($mulai, $selesai)) {
            return 'berlangsung';
        } else {
            return 'mendatang';
        }
    }

    public function index(Request $request)
    {
        try {
            $query = DB::table('sertifikasi')->where('status', 'selesai');
            
            if ($request->filled('tahun')) { 
                $query->whereYear('created_at', $request->tahun); 
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('jenis_sertifikasi', 'like', '%' . $search . '%')
                      ->orWhere('instansi_penerbit', 'like', '%' . $search . '%');
                });
            }

            // Menggunakan 'id' untuk pengurutan karena tgl_terbit tidak ada di tabel
            $sertifikasi = $query->orderBy('id', 'desc')->paginate(9);
            
            return view('dashboard.sertifikasi.index', compact('sertifikasi'));
        } catch (\Exception $e) {
            Log::error('Error Index Sertifikasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * METHOD CREATE (MENANGANI HALAMAN TAMBAH)
     */
    public function create()
    {
        $pegawais = Pegawai::where('status', 'aktif')->orderBy('nama')->get();
        
        // Mengambil daftar tahun dari MasterPelatihan kategori sertifikasi
        $daftarTahun = MasterPelatihan::where('kategori', 'sertifikasi')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');
            
        $masterSertifikasi = MasterPelatihan::where('kategori', 'sertifikasi')
            ->orderBy('tahun', 'desc')
            ->get();
            
        return view('dashboard.sertifikasi.create', compact('pegawais', 'masterSertifikasi', 'daftarTahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|array',
            'tanggal_mulai' => 'required|array',
            'tanggal_selesai' => 'required|array',
            'master_pelatihan_id' => 'required|exists:master_pelatihans,id',
            'instansi' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);
            
            $minStart = min($request->tanggal_mulai);
            $maxEnd = max($request->tanggal_selesai);
            $statusOtomatis = $this->determineStatus($minStart, $maxEnd);

            $sertifikasiId = DB::table('sertifikasi')->insertGetId([
                'master_pelatihan_id' => $master->id,
                'jenis_sertifikasi' => $master->nama_pelatihan,
                'instansi_penerbit' => $request->instansi,
                'status' => $statusOtomatis, 
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->pegawai_id as $index => $p) {
                $parts = explode('|', $p);
                DB::table('sertifikasi_peserta')->insert([
                    'sertifikasi_id' => $sertifikasiId,
                    'nip' => $parts[0],
                    'nama_peserta' => $parts[1],
                    'tanggal_mulai' => $request->tanggal_mulai[$index],
                    'tanggal_selesai' => $request->tanggal_selesai[$index],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('sertifikasi.index')->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Store Sertifikasi: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $sertifikasi = DB::table('sertifikasi')->where('id', $id)->first();
        if (!$sertifikasi) abort(404);
        $peserta = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->get();
        return view('dashboard.sertifikasi.show', compact('sertifikasi', 'peserta'));
    }

    public function edit($id)
    {
        $sertifikasi = DB::table('sertifikasi')->where('id', $id)->first();
        if (!$sertifikasi) abort(404);
        $pesertaTerpilih = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->get();
        $pegawais = Pegawai::where('status', 'aktif')->orderBy('nama')->get();
        $masterSertifikasi = MasterPelatihan::where('kategori', 'sertifikasi')->get();

        return view('dashboard.sertifikasi.edit', compact('sertifikasi', 'pesertaTerpilih', 'pegawais', 'masterSertifikasi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pegawai_id' => 'required|array',
            'tanggal_mulai' => 'required|array',
            'tanggal_selesai' => 'required|array',
            'instansi' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $minStart = min($request->tanggal_mulai);
            $maxEnd = max($request->tanggal_selesai);
            $statusOtomatis = $this->determineStatus($minStart, $maxEnd);

            DB::table('sertifikasi')->where('id', $id)->update([
                'instansi_penerbit' => $request->instansi,
                'status' => $statusOtomatis,
                'updated_at' => now(),
            ]);

            $oldDetails = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->get()->keyBy('nip');
            DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->delete();

            foreach ($request->pegawai_id as $index => $p) {
                $parts = explode('|', $p);
                $nip = $parts[0];
                
                DB::table('sertifikasi_peserta')->insert([
                    'sertifikasi_id' => $id,
                    'nip' => $nip,
                    'nama_peserta' => $parts[1],
                    'tanggal_mulai' => $request->tanggal_mulai[$index],
                    'tanggal_selesai' => $request->tanggal_selesai[$index],
                    'masa_berlaku' => $oldDetails[$nip]->masa_berlaku ?? null,
                    'sertifikat_path' => $oldDetails[$nip]->sertifikat_path ?? null,
                    'created_at' => $oldDetails[$nip]->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('sertifikasi.index')->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Update Sertifikasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function uploadFile(Request $request, $id)
    {
        $request->validate([
            'sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', 
            'masa_berlaku' => 'nullable|date'
        ]);

        try {
            $peserta = DB::table('sertifikasi_peserta')->where('id', $id)->first();
            if (!$peserta) return redirect()->back()->with('error', 'Data tidak ditemukan.');

            $updateData = [
                'masa_berlaku' => $request->masa_berlaku, 
                'updated_at' => now()
            ];

            if ($request->hasFile('sertifikat')) {
                if ($peserta->sertifikat_path) {
                    Storage::disk('public')->delete($peserta->sertifikat_path);
                }
                $file = $request->file('sertifikat');
                $fileName = 'SERTIF_' . time() . '_' . $peserta->nip . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('uploads/sertifikat_pegawai', $fileName, 'public');
                $updateData['sertifikat_path'] = $filePath;
            }

            DB::table('sertifikasi_peserta')->where('id', $id)->update($updateData);
            return redirect()->back()->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) { return redirect()->back()->with('error', 'Terjadi kesalahan.'); }
    }

    public function destroy($id)
    {
        try {
            $peserta = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->get();
            foreach ($peserta as $p) { 
                if ($p->sertifikat_path) Storage::disk('public')->delete($p->sertifikat_path); 
            }
            DB::table('sertifikasi')->where('id', $id)->delete();
            return redirect()->route('sertifikasi.index')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) { return redirect()->back()->with('error', 'Gagal menghapus.'); }
    }
}