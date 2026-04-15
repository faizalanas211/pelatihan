<?php

namespace App\Http\Controllers;

use App\Models\MasterPelatihan;
use Illuminate\Http\Request;
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
        // Validasi input
        $request->validate([
            'kategori'       => 'required|in:pelatihan,sertifikasi,tubel',
            'nama_pelatihan' => 'required|string|max:255',
            'jp'             => 'nullable|numeric',
            'tahun'          => 'required|digits:4',
        ]);

        try {
            // Simpan secara eksplisit agar kategori tidak tertukar
            MasterPelatihan::create([
                'kategori'       => $request->kategori,
                'nama_pelatihan' => $request->nama_pelatihan,
                'jp'             => $request->jp,
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
        $request->validate([
            'kategori'       => 'required|in:pelatihan,sertifikasi,tubel',
            'nama_pelatihan' => 'required|string|max:255',
            'jp'             => 'nullable|numeric',
            'tahun'          => 'required|digits:4',
        ]);

        try {
            $data = MasterPelatihan::findOrFail($id);
            
            $data->update([
                'kategori'       => $request->kategori,
                'nama_pelatihan' => $request->nama_pelatihan,
                'jp'             => $request->jp,
                'tahun'          => $request->tahun,
            ]);

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