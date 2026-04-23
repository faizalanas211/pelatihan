<?php

namespace App\Http\Controllers;

use App\Models\MasterPelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterPelatihanController extends Controller
{
    /**
     * Menampilkan daftar master dengan Filter Ganda (Tahun & Kategori)
     */
    public function index(Request $request)
    {
        // Gunakan query builder agar filter bisa digabung
        $query = MasterPelatihan::query();

        // 1. Filter Berdasarkan Tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // 2. Filter Berdasarkan Kategori (Pelatihan/Sertifikasi/Tugas Belajar)
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Ambil data terbaru dan pertahankan parameter filter saat pindah halaman (pagination)
        $masterPelatihan = $query->latest()->paginate(10);
        
        return view('dashboard.master-pelatihan.index', compact('masterPelatihan'));
    }

    /**
     * Menyimpan data baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input (JP hanya wajib untuk kategori 'pelatihan')
        $rules = [
            'kategori'       => 'required|in:pelatihan,sertifikasi,tubel',
            'nama_pelatihan' => 'required|string|max:255',
            'tahun'          => 'required|digits:4',
        ];

        // Jika kategori 'pelatihan', JP wajib diisi
        if ($request->kategori == 'pelatihan') {
            $rules['jp'] = 'required|numeric|min:1';
        } else {
            $rules['jp'] = 'nullable|numeric';
        }

        $request->validate($rules);

        try {
            // Simpan secara eksplisit agar kategori tidak tertukar
            MasterPelatihan::create([
                'kategori'       => $request->kategori,
                'nama_pelatihan' => $request->nama_pelatihan,
                'jp'             => $request->jp ?? null,
                'tahun'          => $request->tahun,
            ]);

            return redirect()->route('master-pelatihan.index')
                             ->with('success', 'Data Master berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Gagal simpan master: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
        }
    }

    /**
     * Memperbarui data yang sudah ada
     */
    public function update(Request $request, $id)
    {
        // Validasi input (JP hanya wajib untuk kategori 'pelatihan')
        $rules = [
            'kategori'       => 'required|in:pelatihan,sertifikasi,tubel',
            'nama_pelatihan' => 'required|string|max:255',
            'tahun'          => 'required|digits:4',
        ];

        // Jika kategori 'pelatihan', JP wajib diisi
        if ($request->kategori == 'pelatihan') {
            $rules['jp'] = 'required|numeric|min:1';
        } else {
            $rules['jp'] = 'nullable|numeric';
        }

        $request->validate($rules);

        try {
            $data = MasterPelatihan::findOrFail($id);
            
            // Simpan nilai JP lama untuk perbandingan
            $oldJp = $data->jp;
            $newJp = $request->jp ?? null;
            
            $data->update([
                'kategori'       => $request->kategori,
                'nama_pelatihan' => $request->nama_pelatihan,
                'jp'             => $newJp,
                'tahun'          => $request->tahun,
            ]);

            // ✅ TAMBAHKAN: Jika JP berubah dan kategori adalah 'pelatihan', update JP di semua peserta terkait
            if ($request->kategori == 'pelatihan' && $oldJp != $newJp) {
                // Cari semua header pelatihan yang terkait dengan master ini
                $headers = DB::table('pelatihan')->where('master_pelatihan_id', $data->id)->get();
                
                foreach ($headers as $header) {
                    // Update JP semua peserta di pelatihan tersebut
                    $affected = DB::table('pelatihan_peserta')
                        ->where('pelatihan_id', $header->id)
                        ->update(['jp' => $newJp]);
                    
                    Log::info('Update JP peserta untuk pelatihan_id ' . $header->id . ', affected: ' . $affected);
                }
                
                Log::info('JP Master diubah dari ' . $oldJp . ' ke ' . $newJp . ', semua peserta terkait diupdate');
            }

            return redirect()->route('master-pelatihan.index')
                             ->with('success', 'Data Master berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Gagal update master: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    /**
     * Menghapus data dari database
     */
    public function destroy($id)
    {
        try {
            $data = MasterPelatihan::findOrFail($id);
            $data->delete();

            return redirect()->route('master-pelatihan.index')
                             ->with('success', 'Data Master berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Gagal hapus master: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }
}