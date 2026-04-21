<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\MasterPelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RekapPelatihanController extends Controller
{
    /**
     * INDEX: Menampilkan daftar master pelatihan kategori 'pelatihan'
     */
    public function index(Request $request)
    {
        $pelatihan = MasterPelatihan::where('kategori', 'pelatihan')
            ->when($request->tahun, fn($q) => $q->where('tahun', $request->tahun))
            ->when($request->search, fn($q) =>
                $q->where('nama_pelatihan', 'like', '%' . $request->search . '%')
            )
            ->orderBy('id', 'desc')
            ->paginate(9);

        return view('dashboard.rekap-pelatihan.index', compact('pelatihan'));
    }

    /**
     * CREATE: Form tambah data pelatihan
     */
    public function create(Request $request)
    {
        $pegawais = Pegawai::where('status', 'aktif')->get();

        $tahun = $request->tahun;
        $selectedMaster = $request->master_id;

        $masterPelatihan = MasterPelatihan::where('kategori', 'pelatihan')
            ->when($tahun, function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->orderBy('nama_pelatihan', 'asc')
            ->get();

        return view('dashboard.rekap-pelatihan.create', compact(
            'pegawais',
            'masterPelatihan',
            'tahun',
            'selectedMaster'
        ));
    }

    /**
     * STORE: Menyimpan data pelatihan ke tabel pelatihan (header) dan pelatihan_peserta
     */
    public function store(Request $request)
    {
        $request->validate([
            'master_pelatihan_id' => 'required|exists:master_pelatihans,id',
            'instansi'            => 'required|string',
            'pegawai_id'          => 'required|array',
            'pegawai_id.*'        => 'required|string',
            'tanggal_mulai'       => 'required|array',
            'tanggal_mulai.*'     => 'required|date',
            'tanggal_selesai'     => 'required|array',
            'tanggal_selesai.*'   => 'required|date|after_or_equal:tanggal_mulai.*',
            'file_sertifikat'     => 'nullable|array',
            'file_sertifikat.*'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);

            // 1. CEK APAKAH SUDAH ADA HEADER DI TABEL pelatihan
            $existingHeader = DB::table('pelatihan')
                ->where('jenis_pelatihan', $master->nama_pelatihan)
                ->where('tahun', $master->tahun)
                ->first();

            if ($existingHeader) {
                // UPDATE HEADER YANG SUDAH ADA
                DB::table('pelatihan')
                    ->where('id', $existingHeader->id)
                    ->update([
                        'instansi_penyelenggara' => $request->instansi,
                        'status'                 => 'selesai',
                        'updated_at'             => now(),
                    ]);
                $pelatihanId = $existingHeader->id;
            } else {
                // BUAT HEADER BARU
                $pelatihanId = DB::table('pelatihan')->insertGetId([
                    'jenis_pelatihan'        => $master->nama_pelatihan,
                    'tahun'                  => $master->tahun,
                    'jp'                     => $master->jp,
                    'instansi_penyelenggara' => $request->instansi,
                    'status'                 => 'selesai',
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
            }

            // 2. LOOPING UNTUK SETIAP PESERTA
            foreach ($request->pegawai_id as $index => $value) {
                // Parse NIP dan NAMA (format: "nip|nama" dari view)
                $parts = explode('|', $value);
                $nip = $parts[0];
                $nama = $parts[1];

                $filePath = null;

                // UPLOAD FILE SERTIFIKAT
                if ($request->hasFile('file_sertifikat') && isset($request->file('file_sertifikat')[$index])) {
                    $file = $request->file('file_sertifikat')[$index];
                    $ext = $file->getClientOriginalExtension();
                    $timestamp = time();

                    $namaFile = 'sertifikat_pelatihan_' . $nip . '_' . $timestamp . '.' . $ext;
                    $filePath = $file->storeAs('uploads/sertifikat_pelatihan', $namaFile, 'public');
                }

                // INSERT KE TABEL pelatihan_peserta
                DB::table('pelatihan_peserta')->insert([
                    'pelatihan_id'    => $pelatihanId,
                    'nip'             => $nip,
                    'nama_peserta'    => $nama,
                    'tanggal_mulai'   => $request->tanggal_mulai[$index],
                    'tanggal_selesai' => $request->tanggal_selesai[$index],
                    'jp'              => $master->jp,
                    'sertifikat_path' => $filePath,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('rekap-pelatihan.index')
                ->with('success', 'Data pelatihan berhasil disimpan! (' . count($request->pegawai_id) . ' peserta)');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan pelatihan: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * SHOW: Menampilkan detail pelatihan dan peserta
     */
    public function show($id)
    {
        try {
            // ambil master pelatihan
            $pelatihan = MasterPelatihan::where('kategori', 'pelatihan')
                        ->where('id', $id)
                        ->firstOrFail();

            // ambil header dari tabel pelatihan
            $header = DB::table('pelatihan')
                        ->where('jenis_pelatihan', $pelatihan->nama_pelatihan)
                        ->where('tahun', $pelatihan->tahun)
                        ->first();

            // ambil peserta berdasarkan pelatihan_id
            $peserta = [];
            if ($header) {
                $peserta = DB::table('pelatihan_peserta')
                            ->where('pelatihan_id', $header->id)
                            ->get();
            }

            return view('dashboard.rekap-pelatihan.show', compact('pelatihan', 'peserta', 'header'));

        } catch (\Exception $e) {
            Log::error('Gagal show pelatihan: ' . $e->getMessage());
            return redirect()->route('rekap-pelatihan.index')
                ->with('error', 'Gagal memuat detail.');
        }
    }

    /**
     * EDIT: Menampilkan form edit peserta pelatihan
     */
    public function edit($id)
    {
        // ambil master pelatihan
        $pelatihan = MasterPelatihan::where('kategori', 'pelatihan')
                    ->where('id', $id)
                    ->firstOrFail();

        // ambil header dari tabel pelatihan
        $header = DB::table('pelatihan')
                    ->where('jenis_pelatihan', $pelatihan->nama_pelatihan)
                    ->where('tahun', $pelatihan->tahun)
                    ->first();

        // ambil peserta yang sudah ada
        $peserta = [];
        if ($header) {
            $peserta = DB::table('pelatihan_peserta')
                        ->where('pelatihan_id', $header->id)
                        ->get();
        }

        // data pegawai untuk dropdown
        $pegawais = Pegawai::where('status', 'aktif')->get();

        return view('dashboard.rekap-pelatihan.edit', compact('pelatihan', 'peserta', 'pegawais', 'header'));
    }

    /**
     * UPDATE PESERTA (per baris)
     */
    public function updatePeserta(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'sertifikat'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = DB::table('pelatihan_peserta')->where('id', $id)->first();

            if (!$data) {
                return back()->with('error', 'Data tidak ditemukan');
            }

            $update = [
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'updated_at'      => now(),
            ];

            // HANDLE FILE
            if ($request->hasFile('sertifikat')) {
                $file = $request->file('sertifikat');
                $ext = $file->getClientOriginalExtension();
                $timestamp = time();

                if ($data->sertifikat_path) {
                    Storage::disk('public')->delete($data->sertifikat_path);
                }

                $namaFile = 'sertifikat_pelatihan_' . $data->nip . '_' . $timestamp . '.' . $ext;
                $path = $file->storeAs('uploads/sertifikat_pelatihan', $namaFile, 'public');
                $update['sertifikat_path'] = $path;
            }

            DB::table('pelatihan_peserta')->where('id', $id)->update($update);

            DB::commit();

            return back()->with('success', 'Data peserta berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update peserta: ' . $e->getMessage());
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * UPDATE MASSAL (edit semua peserta)
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // ambil header
            $header = DB::table('pelatihan')->where('id', $id)->first();
            
            if (!$header) {
                return back()->with('error', 'Data header tidak ditemukan');
            }

            // ambil master_pelatihan_id dari tabel master_pelatihans
            $master = MasterPelatihan::where('nama_pelatihan', $header->jenis_pelatihan)
                        ->where('tahun', $header->tahun)
                        ->first();

            // ambil semua ID dari form
            $idDariForm = collect($request->id ?? [])->filter()->toArray();

            // ambil data lama
            $dataLamaSemua = DB::table('pelatihan_peserta')
                ->where('pelatihan_id', $id)
                ->get()
                ->keyBy('id');

            // UPDATE header instansi
            DB::table('pelatihan')->where('id', $id)->update([
                'instansi_penyelenggara' => $request->instansi,
                'updated_at' => now(),
            ]);

            // DELETE yang dihapus di UI + hapus file
            $dataYangDihapus = $dataLamaSemua->except($idDariForm);

            foreach ($dataYangDihapus as $row) {
                if ($row->sertifikat_path) {
                    Storage::disk('public')->delete($row->sertifikat_path);
                }
                DB::table('pelatihan_peserta')->where('id', $row->id)->delete();
            }

            foreach ($request->pegawai_id as $i => $pegawaiValue) {
                $rowId = $request->id[$i] ?? null;

                // Parse NIP dan NAMA
                $parts = explode('|', $pegawaiValue);
                $nip = $parts[0];
                $nama = $parts[1];

                $dataLama = $rowId ? ($dataLamaSemua[$rowId] ?? null) : null;

                $dataUpdate = [
                    'pelatihan_id'    => $id,
                    'nip'             => $nip,
                    'nama_peserta'    => $nama,
                    'tanggal_mulai'   => $request->tanggal_mulai[$i],
                    'tanggal_selesai' => $request->tanggal_selesai[$i],
                    'jp'              => $request->jp[$i] ?? 0,
                    'updated_at'      => now(),
                ];

                // HANDLE HAPUS FILE (checkbox)
                if ($rowId && in_array($rowId, $request->hapus_file ?? [])) {
                    if ($dataLama && $dataLama->sertifikat_path) {
                        Storage::disk('public')->delete($dataLama->sertifikat_path);
                    }
                    $dataUpdate['sertifikat_path'] = null;
                }

                // HANDLE UPLOAD FILE BARU
                if ($request->hasFile("file_sertifikat.$i")) {
                    $file = $request->file("file_sertifikat.$i");

                    if ($dataLama && $dataLama->sertifikat_path) {
                        Storage::disk('public')->delete($dataLama->sertifikat_path);
                    }

                    $ext = $file->getClientOriginalExtension();
                    $timestamp = time();

                    $namaFile = 'sertifikat_pelatihan_' . $nip . '_' . $timestamp . '.' . $ext;
                    $path = $file->storeAs('uploads/sertifikat_pelatihan', $namaFile, 'public');
                    $dataUpdate['sertifikat_path'] = $path;
                }

                if ($rowId) {
                    DB::table('pelatihan_peserta')
                        ->where('id', $rowId)
                        ->update($dataUpdate);
                } else {
                    $dataUpdate['created_at'] = now();
                    DB::table('pelatihan_peserta')->insert($dataUpdate);
                }
            }

            DB::commit();

            // ✅ PERBAIKAN: Redirect ke show dengan id master yang benar
            $redirectId = $master->id ?? $request->master_pelatihan_id ?? $id;
            
            return redirect()
                ->route('rekap-pelatihan.show', $redirectId)
                ->with('success', 'Data peserta berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update massal: ' . $e->getMessage());
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * DESTROY: Hapus semua peserta dan header
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // ambil header
            $header = DB::table('pelatihan')->where('id', $id)->first();

            if ($header) {
                // ambil semua peserta
                $data = DB::table('pelatihan_peserta')
                    ->where('pelatihan_id', $header->id)
                    ->get();

                // hapus file
                foreach ($data as $row) {
                    if ($row->sertifikat_path) {
                        Storage::disk('public')->delete($row->sertifikat_path);
                    }
                }

                // hapus data peserta
                DB::table('pelatihan_peserta')
                    ->where('pelatihan_id', $header->id)
                    ->delete();

                // hapus header
                DB::table('pelatihan')->where('id', $header->id)->delete();
            }

            DB::commit();

            return redirect()
                ->route('rekap-pelatihan.index')
                ->with('success', 'Data pelatihan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus: ' . $e->getMessage());
            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}