<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\MasterPelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RekapPelatihanController extends Controller
{
    /**
     * Helper untuk menentukan status berdasarkan rentang tanggal
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

    /**
     * Tampilkan daftar event pelatihan yang sudah SELESAI
     */
    public function index(Request $request)
    {
        try {
            // Kita tidak bisa sinkronisasi otomatis per-tabel pelatihan lagi karena tanggal ada di peserta.
            // Untuk index rekap, kita filter yang statusnya 'selesai'.
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
            
            $pelatihan = $query->orderBy('id', 'desc')->paginate(10);
            
            return view('dashboard.rekap-pelatihan.index', compact('pelatihan'));
            
        } catch (\Exception $e) {
            Log::error('Error di RekapPelatihan Index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    /**
     * Tampilkan form tambah rekap
     */
    public function create() 
    {
        $pegawais = Pegawai::all();

        $daftarTahun = MasterPelatihan::where('kategori', 'pelatihan')
                        ->select('tahun')
                        ->distinct()
                        ->orderBy('tahun', 'desc')
                        ->pluck('tahun');

        $masterPelatihan = MasterPelatihan::where('kategori', 'pelatihan')
                            ->orderBy('tahun', 'desc')
                            ->orderBy('nama_pelatihan', 'asc')
                            ->get();
        
        return view('dashboard.rekap-pelatihan.create', compact('pegawais', 'masterPelatihan', 'daftarTahun'));
    }

    /**
     * STORE: Menggunakan logika SATU HEADER, BANYAK PESERTA
     */
    public function store(Request $request)
    {
        $request->validate([
            'master_pelatihan_id' => 'required|exists:master_pelatihans,id',
            'instansi'            => 'required|string',
            'pegawai_id'          => 'required|array',
            'tanggal_mulai'       => 'required|array',
            'tanggal_selesai'     => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);

            // 1. Simpan ke tabel pelatihan HANYA SEKALI (Header)
            $pelatihanId = DB::table('pelatihan')->insertGetId([
                'jenis_pelatihan'        => $master->nama_pelatihan,
                'tahun'                  => $master->tahun,
                'jp'                     => $master->jp,
                'instansi_penyelenggara' => $request->instansi,
                'status'                 => 'selesai', 
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            // 2. Looping Peserta (Detail)
            foreach ($request->pegawai_id as $key => $p) {
                $parts = explode('|', $p);
                $nip = $parts[0];
                $nama = $parts[1];

                $filePath = null;
                if ($request->hasFile("file_sertifikat.$key")) {
                    $file = $request->file("file_sertifikat.$key");
                    $fileName = time() . '_' . $nip . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('uploads/sertifikat_peserta', $fileName, 'public');
                }

                DB::table('pelatihan_peserta')->insert([
                    'pelatihan_id'    => $pelatihanId,
                    'nip'             => $nip,
                    'nama_peserta'    => $nama,
                    'tanggal_mulai'   => $request->tanggal_mulai[$key],
                    'tanggal_selesai' => $request->tanggal_selesai[$key],
                    'jp'              => $master->jp, 
                    'sertifikat_path' => $filePath,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('rekap-pelatihan.index')->with('success', "Pelatihan berhasil disimpan!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan pelatihan: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $pelatihan = DB::table('pelatihan')->where('id', $id)->first();
            if (!$pelatihan) {
                return redirect()->route('rekap-pelatihan.index')->with('error', 'Data tidak ditemukan.');
            }

            $peserta = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();

            return view('dashboard.rekap-pelatihan.rekap-pelatihan-show', compact('pelatihan', 'peserta'));
            
        } catch (\Exception $e) {
            return redirect()->route('rekap-pelatihan.index')->with('error', 'Gagal memuat detail.');
        }
    }

    /**
     * FUNGSI ASLI: Upload Sertifikat Satuan di Halaman Show
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

            if ($request->filled('jp')) {
                DB::table('pelatihan_peserta')
                    ->where('id', $id)
                    ->update(['jp' => $request->jp, 'updated_at' => now()]);
            }

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
            return redirect()->back()->with('success', 'Berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update.');
        }
    }

    public function edit($id)
    {
        $pelatihan = DB::table('pelatihan')->where('id', $id)->first();
        if (!$pelatihan) abort(404);
        
        $pesertaTerpilih = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();
        $pegawais = Pegawai::all();

        $daftarTahun = MasterPelatihan::where('kategori', 'pelatihan')
                        ->select('tahun')
                        ->distinct()
                        ->orderBy('tahun', 'desc')
                        ->pluck('tahun');

        $masterPelatihan = MasterPelatihan::where('kategori', 'pelatihan')
                            ->orderBy('tahun', 'desc')
                            ->orderBy('nama_pelatihan', 'asc')
                            ->get();
        
        return view('dashboard.rekap-pelatihan.edit', compact('pelatihan', 'pesertaTerpilih', 'pegawais', 'masterPelatihan', 'daftarTahun'));
    }

    /**
     * UPDATE: Menyesuaikan struktur baru
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'pegawai_id'    => 'required|array',
            'tanggal_mulai' => 'required|array',
            'instansi'      => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);

            // Update Header
            DB::table('pelatihan')->where('id', $id)->update([
                'jenis_pelatihan'        => $master->nama_pelatihan,
                'tahun'                  => $master->tahun,
                'jp'                     => $master->jp,
                'instansi_penyelenggara' => $request->instansi,
                'updated_at'             => now(),
            ]);

            // Untuk Update Peserta: Biasanya lebih aman hapus detail lama lalu insert baru 
            // (Atau sesuaikan dengan logika edit row kamu)
            $oldDataPeserta = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get()->keyBy('nip');
            DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->delete();

            foreach ($request->pegawai_id as $key => $p) {
                $parts = explode('|', $p);
                $nip = $parts[0];
                
                DB::table('pelatihan_peserta')->insert([
                    'pelatihan_id'    => $id,
                    'nip'             => $nip,
                    'nama_peserta'    => $parts[1],
                    'tanggal_mulai'   => $request->tanggal_mulai[$key],
                    'tanggal_selesai' => $request->tanggal_selesai[$key],
                    'jp'              => $oldDataPeserta[$nip]->jp ?? $master->jp,
                    'sertifikat_path' => $oldDataPeserta[$nip]->sertifikat_path ?? null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('rekap-pelatihan.index')->with('success', 'Data diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal update.');
        }
    }

    public function destroy($id)
    {
        try {
            $peserta = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();
            foreach ($peserta as $p) {
                if ($p->sertifikat_path) Storage::disk('public')->delete($p->sertifikat_path);
            }
            DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->delete();
            DB::table('pelatihan')->where('id', $id)->delete();
            return redirect()->route('rekap-pelatihan.index')->with('success', 'Data dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus.');
        }
    }
}