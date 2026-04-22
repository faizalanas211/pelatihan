<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\MasterPelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SertifikasiController extends Controller
{
    /**
     * INDEX: Menampilkan daftar master sertifikasi kategori 'sertifikasi'
     */
    public function index(Request $request)
    {
        $sertifikasi = MasterPelatihan::where('kategori', 'sertifikasi')
            ->when($request->tahun, fn($q) => $q->where('tahun', $request->tahun))
            ->when($request->search, fn($q) =>
                $q->where('nama_pelatihan', 'like', '%' . $request->search . '%')
            )
            ->orderBy('id', 'desc')
            ->paginate(9);

        return view('dashboard.sertifikasi.index', compact('sertifikasi'));
    }

    /**
     * CREATE: Form tambah data sertifikasi
     */
    public function create(Request $request)
    {
        $pegawais = Pegawai::where('status', 'aktif')->get();

        $tahun = $request->tahun;
        $selectedMaster = $request->master_id;

        $masterSertifikasi = MasterPelatihan::where('kategori', 'sertifikasi')
            ->when($tahun, function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->orderBy('nama_pelatihan', 'asc')
            ->get();

        return view('dashboard.sertifikasi.create', compact(
            'pegawais',
            'masterSertifikasi',
            'tahun',
            'selectedMaster'
        ));
    }

    /**
     * STORE: Menyimpan data sertifikasi ke tabel sertifikasi (header) dan sertifikasi_peserta
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
            'masa_berlaku'        => 'nullable|array',
            'masa_berlaku.*'      => 'nullable|date',
            'file_sertifikat'     => 'nullable|array',
            'file_sertifikat.*'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);

            // 1. CEK APAKAH SUDAH ADA HEADER DI TABEL sertifikasi (diperbaiki)
            $existingHeader = DB::table('sertifikasi')
                ->where('master_pelatihan_id', $master->id)
                ->first();

            if ($existingHeader) {
                // UPDATE HEADER YANG SUDAH ADA
                DB::table('sertifikasi')
                    ->where('id', $existingHeader->id)
                    ->update([
                        'instansi_penerbit' => $request->instansi,
                        'status'            => 'selesai',
                        'updated_at'        => now(),
                    ]);
                $sertifikasiId = $existingHeader->id;
            } else {
                // BUAT HEADER BARU
                $sertifikasiId = DB::table('sertifikasi')->insertGetId([
                    'master_pelatihan_id' => $master->id,
                    'jenis_sertifikasi'   => $master->nama_pelatihan,
                    'instansi_penerbit'   => $request->instansi,
                    'status'              => 'selesai',
                    'created_at'          => now(),
                    'updated_at'          => now(),
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

                    $namaFile = 'sertifikat_sertifikasi_' . $nip . '_' . $timestamp . '.' . $ext;
                    $filePath = $file->storeAs('uploads/sertifikat_sertifikasi', $namaFile, 'public');
                }

                // INSERT KE TABEL sertifikasi_peserta
                DB::table('sertifikasi_peserta')->insert([
                    'sertifikasi_id'  => $sertifikasiId,
                    'nip'             => $nip,
                    'nama_peserta'    => $nama,
                    'tanggal_mulai'   => $request->tanggal_mulai[$index],
                    'tanggal_selesai' => $request->tanggal_selesai[$index],
                    'masa_berlaku'    => $request->masa_berlaku[$index] ?? null,
                    'sertifikat_path' => $filePath,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('sertifikasi.index')
                ->with('success', 'Data sertifikasi berhasil disimpan! (' . count($request->pegawai_id) . ' peserta)');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan sertifikasi: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * SHOW: Menampilkan detail sertifikasi dan peserta (diperbaiki)
     */
    public function show($id)
    {
        try {
            // ambil master sertifikasi
            $sertifikasi = MasterPelatihan::where('kategori', 'sertifikasi')
                        ->where('id', $id)
                        ->firstOrFail();

            // ambil header dari tabel sertifikasi berdasarkan master_pelatihan_id
            $header = DB::table('sertifikasi')
                        ->where('master_pelatihan_id', $sertifikasi->id)
                        ->first();

            // ambil peserta berdasarkan sertifikasi_id
            $peserta = [];
            if ($header) {
                $peserta = DB::table('sertifikasi_peserta')
                            ->where('sertifikasi_id', $header->id)
                            ->get();
            }

            return view('dashboard.sertifikasi.show', compact('sertifikasi', 'peserta', 'header'));

        } catch (\Exception $e) {
            Log::error('Gagal show sertifikasi: ' . $e->getMessage());
            return redirect()->route('sertifikasi.index')
                ->with('error', 'Gagal memuat detail.');
        }
    }

    /**
     * EDIT: Menampilkan form edit peserta sertifikasi (diperbaiki)
     */
    public function edit($id)
    {
        // ambil master sertifikasi
        $sertifikasi = MasterPelatihan::where('kategori', 'sertifikasi')
                    ->where('id', $id)
                    ->firstOrFail();

        // ambil header dari tabel sertifikasi berdasarkan master_pelatihan_id
        $header = DB::table('sertifikasi')
                    ->where('master_pelatihan_id', $sertifikasi->id)
                    ->first();

        // ambil peserta yang sudah ada
        $peserta = [];
        if ($header) {
            $peserta = DB::table('sertifikasi_peserta')
                        ->where('sertifikasi_id', $header->id)
                        ->get();
        }

        // data pegawai untuk dropdown
        $pegawais = Pegawai::where('status', 'aktif')->get();

        return view('dashboard.sertifikasi.edit', compact('sertifikasi', 'peserta', 'pegawais', 'header'));
    }

    /**
     * UPDATE PESERTA (per baris)
     */
    public function updatePeserta(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'masa_berlaku'    => 'nullable|date',
            'sertifikat'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = DB::table('sertifikasi_peserta')->where('id', $id)->first();

            if (!$data) {
                return back()->with('error', 'Data tidak ditemukan');
            }

            $update = [
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'masa_berlaku'    => $request->masa_berlaku,
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

                $namaFile = 'sertifikat_sertifikasi_' . $data->nip . '_' . $timestamp . '.' . $ext;
                $path = $file->storeAs('uploads/sertifikat_sertifikasi', $namaFile, 'public');
                $update['sertifikat_path'] = $path;
            }

            DB::table('sertifikasi_peserta')->where('id', $id)->update($update);

            DB::commit();

            return back()->with('success', 'Data peserta berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update peserta: ' . $e->getMessage());
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * UPDATE MASSAL (edit semua peserta) - DIPERBAIKI UNTUK 0 PESERTA
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // ambil header
            $header = DB::table('sertifikasi')->where('id', $id)->first();
            
            if (!$header) {
                return back()->with('error', 'Data header tidak ditemukan');
            }

            // ambil master_pelatihan_id dari tabel master_pelatihans
            $master = MasterPelatihan::where('id', $header->master_pelatihan_id)->first();

            // UPDATE header instansi
            DB::table('sertifikasi')->where('id', $id)->update([
                'instansi_penerbit' => $request->instansi,
                'updated_at' => now(),
            ]);

            // CEK APAKAH ADA PESERTA YANG DIKIRIM
            if (empty($request->pegawai_id) || !is_array($request->pegawai_id)) {
                // JIKA TIDAK ADA PESERTA, HAPUS SEMUA PESERTA YANG ADA
                $existingPeserta = DB::table('sertifikasi_peserta')
                    ->where('sertifikasi_id', $id)
                    ->get();
                
                foreach ($existingPeserta as $row) {
                    if ($row->sertifikat_path) {
                        Storage::disk('public')->delete($row->sertifikat_path);
                    }
                    DB::table('sertifikasi_peserta')->where('id', $row->id)->delete();
                }
                
                DB::commit();
                return redirect()
                    ->route('sertifikasi.show', $master->id ?? $id)
                    ->with('success', 'Data peserta berhasil diperbarui (semua peserta dihapus)');
            }

            // ambil semua ID dari form
            $idDariForm = collect($request->id ?? [])->filter()->toArray();

            // ambil data lama
            $dataLamaSemua = DB::table('sertifikasi_peserta')
                ->where('sertifikasi_id', $id)
                ->get()
                ->keyBy('id');

            // DELETE yang dihapus di UI + hapus file
            $dataYangDihapus = $dataLamaSemua->except($idDariForm);

            foreach ($dataYangDihapus as $row) {
                if ($row->sertifikat_path) {
                    Storage::disk('public')->delete($row->sertifikat_path);
                }
                DB::table('sertifikasi_peserta')->where('id', $row->id)->delete();
            }

            foreach ($request->pegawai_id as $i => $pegawaiValue) {
                $rowId = $request->id[$i] ?? null;

                // Parse NIP dan NAMA
                $parts = explode('|', $pegawaiValue);
                $nip = $parts[0];
                $nama = $parts[1];

                $dataLama = $rowId ? ($dataLamaSemua[$rowId] ?? null) : null;

                $dataUpdate = [
                    'sertifikasi_id'  => $id,
                    'nip'             => $nip,
                    'nama_peserta'    => $nama,
                    'tanggal_mulai'   => $request->tanggal_mulai[$i],
                    'tanggal_selesai' => $request->tanggal_selesai[$i],
                    'masa_berlaku'    => $request->masa_berlaku[$i] ?? null,
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

                    $namaFile = 'sertifikat_sertifikasi_' . $nip . '_' . $timestamp . '.' . $ext;
                    $path = $file->storeAs('uploads/sertifikat_sertifikasi', $namaFile, 'public');
                    $dataUpdate['sertifikat_path'] = $path;
                }

                if ($rowId) {
                    DB::table('sertifikasi_peserta')
                        ->where('id', $rowId)
                        ->update($dataUpdate);
                } else {
                    $dataUpdate['created_at'] = now();
                    DB::table('sertifikasi_peserta')->insert($dataUpdate);
                }
            }

            DB::commit();

            // Redirect ke show dengan id master yang benar
            $redirectId = $master->id ?? $request->master_pelatihan_id ?? $id;
            
            return redirect()
                ->route('sertifikasi.show', $redirectId)
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
            $header = DB::table('sertifikasi')->where('id', $id)->first();

            if ($header) {
                // ambil semua peserta
                $data = DB::table('sertifikasi_peserta')
                    ->where('sertifikasi_id', $header->id)
                    ->get();

                // hapus file
                foreach ($data as $row) {
                    if ($row->sertifikat_path) {
                        Storage::disk('public')->delete($row->sertifikat_path);
                    }
                }

                // hapus data peserta
                DB::table('sertifikasi_peserta')
                    ->where('sertifikasi_id', $header->id)
                    ->delete();

                // hapus header
                DB::table('sertifikasi')->where('id', $header->id)->delete();
            }

            DB::commit();

            return redirect()
                ->route('sertifikasi.index')
                ->with('success', 'Data sertifikasi berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus: ' . $e->getMessage());
            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}