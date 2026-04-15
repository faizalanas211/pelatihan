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
            return 'selesai'; // Sudah melewati tanggal selesai
        } elseif ($today->between($mulai, $selesai)) {
            return 'berlangsung'; // Sedang dalam masa pelatihan
        } else {
            return 'mendatang'; // Belum mulai
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
            // Cek berdasarkan tanggal_selesai untuk mengubah status ke selesai
            DB::table('pelatihan')
                ->where('tanggal_selesai', '<', $today)
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

    /**
     * Tampilkan form tambah rekap (HANYA KATEGORI PELATIHAN)
     */
    public function create() 
    {
        $pegawais = Pegawai::all();

        // Filter hanya yang kategorinya 'pelatihan'
        $masterPelatihan = MasterPelatihan::where('kategori', 'pelatihan')
                            ->orderBy('nama_pelatihan', 'asc')
                            ->get();
        
        return view('dashboard.rekap-pelatihan.create', compact('pegawais', 'masterPelatihan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'peserta'               => 'required|array',
            'peserta.*'             => 'required',
            'master_pelatihan_id'   => 'required|exists:master_pelatihans,id',
            'waktu_pelaksanaan'     => 'required|date', // Bertindak sebagai Tanggal Mulai
            'tanggal_selesai'       => 'required|date|after_or_equal:waktu_pelaksanaan',
            'instansi'              => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);
            $statusOtomatis = $this->determineStatus($request->waktu_pelaksanaan, $request->tanggal_selesai);

            $pelatihanId = DB::table('pelatihan')->insertGetId([
                'jenis_pelatihan'        => $master->nama_pelatihan,
                'tahun'                  => $master->tahun,
                'jp'                     => $master->jp,
                'waktu_pelaksanaan'      => $request->waktu_pelaksanaan,
                'tanggal_selesai'        => $request->tanggal_selesai,
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
                    'jp'           => $master->jp, 
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();

            $targetRoute = ($statusOtomatis == 'selesai') ? 'rekap-pelatihan.index' : 'jadwal-pelatihan.index';
            
            return redirect()->route($targetRoute)
                             ->with('success', "Pelatihan berhasil disimpan!");

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
                    ->where('pelatihan_id', $peserta->pelatihan_id)
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

    /**
     * Tampilkan form edit rekap (HANYA KATEGORI PELATIHAN)
     */
    public function edit($id)
    {
        $pelatihan = DB::table('pelatihan')->where('id', $id)->first();
        if (!$pelatihan) abort(404);
        
        $pesertaTerpilih = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();
        $pegawais = Pegawai::all();

        $masterPelatihan = MasterPelatihan::where('kategori', 'pelatihan')
                            ->orderBy('nama_pelatihan', 'asc')
                            ->get();
        
        return view('dashboard.rekap-pelatihan.edit', compact('pelatihan', 'pesertaTerpilih', 'pegawais', 'masterPelatihan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'peserta'               => 'required|array',
            'master_pelatihan_id'   => 'required|exists:master_pelatihans,id',
            'waktu_pelaksanaan'     => 'required|date',
            'tanggal_selesai'       => 'required|date|after_or_equal:waktu_pelaksanaan',
            'instansi'              => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);
            $statusOtomatis = $this->determineStatus($request->waktu_pelaksanaan, $request->tanggal_selesai);

            DB::table('pelatihan')->where('id', $id)->update([
                'jenis_pelatihan'        => $master->nama_pelatihan,
                'tahun'                  => $master->tahun,
                'jp'                     => $master->jp,
                'waktu_pelaksanaan'      => $request->waktu_pelaksanaan,
                'tanggal_selesai'        => $request->tanggal_selesai,
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
            DB::table('pelatihan')->where('id', $id)->delete();
            return redirect()->route('rekap-pelatihan.index')->with('success', 'Data dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus.');
        }
    }
}