<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\MasterPelatihan; // Pastikan model ini ada
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
        $pegawais = Pegawai::all();
        $masterPelatihan = MasterPelatihan::all();
        
        // REVISI: Sesuaikan path view dengan folder dashboard kamu
        return view('dashboard.rekap-pelatihan.create', compact('pegawais', 'masterPelatihan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'peserta'               => 'required|array',
            'peserta.*'             => 'required',
            'master_pelatihan_id'   => 'required|exists:master_pelatihans,id',
            'waktu_pelaksanaan'     => 'required|date',
            'instansi'              => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Ambil data dari Master
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);
            $statusOtomatis = $this->determineStatus($request->waktu_pelaksanaan);

            // Simpan data pelatihan (Nama, Tahun, JP diambil dari Master)
            $pelatihanId = DB::table('pelatihan')->insertGetId([
                'jenis_pelatihan'        => $master->nama_pelatihan,
                'tahun'                  => $master->tahun,
                'jp'                     => $master->jp,
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

    public function edit($id)
    {
        $pelatihan = DB::table('pelatihan')->where('id', $id)->first();
        if (!$pelatihan) abort(404);
        
        $pesertaTerpilih = DB::table('pelatihan_peserta')->where('pelatihan_id', $id)->get();
        $pegawais = Pegawai::all();
        $masterPelatihan = MasterPelatihan::all();
        
        return view('dashboard.rekap-pelatihan.edit', compact('pelatihan', 'pesertaTerpilih', 'pegawais', 'masterPelatihan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'peserta'               => 'required|array',
            'master_pelatihan_id'   => 'required|exists:master_pelatihans,id',
            'waktu_pelaksanaan'     => 'required|date',
            'instansi'              => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);
            $statusOtomatis = $this->determineStatus($request->waktu_pelaksanaan);

            DB::table('pelatihan')->where('id', $id)->update([
                'jenis_pelatihan'        => $master->nama_pelatihan,
                'tahun'                  => $master->tahun,
                'jp'                     => $master->jp,
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