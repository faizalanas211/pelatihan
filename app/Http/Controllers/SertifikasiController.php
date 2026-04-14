<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SertifikasiController extends Controller
{
    /**
     * Tampilkan Index (Hanya status 'selesai')
     * Muncul di menu Sertifikasi
     */
    public function index(Request $request)
    {
        try {
            // Filter: Hanya menampilkan data yang statusnya 'selesai'
            $query = DB::table('sertifikasi')->where('status', 'selesai');
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('jenis_sertifikasi', 'like', "%$search%")
                      ->orWhere('instansi_penerbit', 'like', "%$search%");
                });
            }

            $sertifikasi = $query->orderBy('tgl_terbit', 'desc')->paginate(9);
            return view('dashboard.sertifikasi.index', compact('sertifikasi'));
        } catch (\Exception $e) {
            Log::error('Error Index Sertifikasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data.');
        }
    }

    /**
     * Form Tambah Kolektif
     */
    public function create()
    {
        $pegawais = Pegawai::where('status', 'aktif')->orderBy('nama')->get();
        return view('dashboard.sertifikasi.create', compact('pegawais'));
    }

    /**
     * Simpan Data Induk & List Peserta
     */
    public function store(Request $request)
    {
        // Validasi tanpa 'status' karena kita set otomatis
        $request->validate([
            'peserta'           => 'required|array',
            'jenis_sertifikasi' => 'required|string',
            'tgl_terbit'        => 'required|date',
            'instansi'          => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Simpan ke tabel induk dengan default status 'selesai'
            $sertifikasiId = DB::table('sertifikasi')->insertGetId([
                'jenis_sertifikasi' => $request->jenis_sertifikasi,
                'instansi_penerbit' => $request->instansi,
                'tgl_terbit'        => $request->tgl_terbit,
                'status'            => 'selesai', // Set otomatis selesai agar muncul di menu Sertifikasi
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            foreach ($request->peserta as $p) {
                $parts = explode('|', $p);
                DB::table('sertifikasi_peserta')->insert([
                    'sertifikasi_id' => $sertifikasiId,
                    'nip'            => $parts[0],
                    'nama_peserta'   => $parts[1],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            DB::commit();

            // Karena default adalah 'selesai', langsung arahkan ke index sertifikasi
            return redirect()->route('sertifikasi.index')->with('success', 'Data Sertifikasi berhasil disimpan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Store Sertifikasi: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
        }
    }

    /**
     * Tampilkan Detail
     */
    public function show($id)
    {
        $sertifikasi = DB::table('sertifikasi')->where('id', $id)->first();
        if (!$sertifikasi) abort(404);

        $peserta = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->get();
        
        return view('dashboard.sertifikasi.show', compact('sertifikasi', 'peserta'));
    }

    /**
     * Form Edit
     */
    public function edit($id)
    {
        $sertifikasi = DB::table('sertifikasi')->where('id', $id)->first();
        if (!$sertifikasi) abort(404);

        $pesertaTerpilih = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->get();
        $pegawais = Pegawai::where('status', 'aktif')->orderBy('nama')->get();

        return view('dashboard.sertifikasi.edit', compact('sertifikasi', 'pesertaTerpilih', 'pegawais'));
    }

    /**
     * Update Data Induk & Sinkronisasi Peserta agar File Tidak Hilang
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'peserta'           => 'required|array',
            'jenis_sertifikasi' => 'required|string',
            'tgl_terbit'        => 'required|date',
            'instansi'          => 'required|string',
            'status'            => 'required|in:mendatang,berlangsung,selesai'
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Induk
            DB::table('sertifikasi')->where('id', $id)->update([
                'jenis_sertifikasi' => $request->jenis_sertifikasi,
                'instansi_penerbit' => $request->instansi,
                'tgl_terbit'        => $request->tgl_terbit,
                'status'            => $request->status,
                'updated_at'        => now(),
            ]);

            // 2. Backup data detail (path & masa berlaku) berdasarkan NIP
            $oldDetails = DB::table('sertifikasi_peserta')
                            ->where('sertifikasi_id', $id)
                            ->get()
                            ->keyBy('nip');

            // 3. Reset detail (hapus yang lama)
            DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->delete();

            // 4. Re-insert dengan sinkronisasi agar data upload tidak hilang
            foreach ($request->peserta as $p) {
                $parts = explode('|', $p);
                $nip = $parts[0];
                
                DB::table('sertifikasi_peserta')->insert([
                    'sertifikasi_id'  => $id,
                    'nip'             => $nip,
                    'nama_peserta'    => $parts[1],
                    // Sinkronisasi data lama jika NIP nya sama
                    'masa_berlaku'    => $oldDetails[$nip]->masa_berlaku ?? null,
                    'sertifikat_path' => $oldDetails[$nip]->sertifikat_path ?? null,
                    'created_at'      => $oldDetails[$nip]->created_at ?? now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            // Redirect berdasarkan status akhir
            if($request->status == 'selesai') {
                return redirect()->route('sertifikasi.show', $id)->with('success', 'Data berhasil diperbarui!');
            } else {
                return redirect()->route('jadwal-pelatihan.index')->with('success', 'Data dipindahkan ke Jadwal Pelatihan.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Update Sertifikasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal update data.');
        }
    }

    /**
     * Kelola File & Masa Berlaku per Orang (Modal di halaman Show)
     */
    public function uploadFile(Request $request, $id)
    {
        $request->validate([
            'sertifikat'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'masa_berlaku' => 'nullable|date'
        ]);

        try {
            $peserta = DB::table('sertifikasi_peserta')->where('id', $id)->first();
            if (!$peserta) return redirect()->back()->with('error', 'Data tidak ditemukan.');

            $updateData = [
                'masa_berlaku' => $request->masa_berlaku,
                'updated_at'   => now()
            ];

            if ($request->hasFile('sertifikat')) {
                // Hapus file lama jika ada sebelum ganti baru
                if ($peserta->sertifikat_path) {
                    Storage::disk('public')->delete($peserta->sertifikat_path);
                }

                $file = $request->file('sertifikat');
                $fileName = 'SERTIF_' . time() . '_' . $peserta->nip . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('uploads/sertifikat_pegawai', $fileName, 'public');
                $updateData['sertifikat_path'] = $filePath;
            }

            DB::table('sertifikasi_peserta')->where('id', $id)->update($updateData);

            return redirect()->back()->with('success', 'Data ' . $peserta->nama_peserta . ' berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error Upload/Update Peserta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Hapus Event dan Seluruh File Fisik Peserta
     */
    public function destroy($id)
    {
        try {
            $peserta = DB::table('sertifikasi_peserta')->where('sertifikasi_id', $id)->get();
            
            // Hapus semua file fisik dari storage
            foreach ($peserta as $p) {
                if ($p->sertifikat_path) {
                    Storage::disk('public')->delete($p->sertifikat_path);
                }
            }
            
            DB::table('sertifikasi')->where('id', $id)->delete();
            return redirect()->route('sertifikasi.index')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }
}