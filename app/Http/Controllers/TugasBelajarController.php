<?php

namespace App\Http\Controllers;

use App\Models\MasterPelatihan;
use App\Models\Pegawai;
use App\Models\TubelPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TugasBelajarController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->tahun;
        $search = $request->search;

        $tahunList = MasterPelatihan::where('kategori', 'tubel')
                    ->select('tahun')
                    ->distinct()
                    ->orderBy('tahun', 'desc')
                    ->pluck('tahun');

        $tubel = MasterPelatihan::where('kategori', 'tubel')

            // 🔹 FILTER TAHUN
            ->when($tahun, function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })

            // 🔹 SEARCH
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_pelatihan', 'like', "%{$search}%");
                    // kalau mau tambah:
                    // ->orWhere('penyelenggara', 'like', "%{$search}%");
                });
            })

            ->orderBy('tahun', 'desc')
            ->paginate(3)
            ->withQueryString(); 

        return view('dashboard.tugas-belajar.index', compact('tubel', 'tahunList', 'tahun'));
    }

    public function create(Request $request)
    {
        $pegawais = Pegawai::where('status','aktif')->get();

        $tahunList = MasterPelatihan::where('kategori', 'tubel')
                    ->select('tahun')
                    ->distinct()
                    ->orderBy('tahun', 'desc')
                    ->pluck('tahun');

        $tahun = $request->tahun;
        $selectedMaster = $request->master_id;

        $masterTubel = MasterPelatihan::where('kategori', 'tubel')
            ->when($tahun, function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->orderBy('nama_pelatihan', 'asc')
            ->get();

        return view('dashboard.tugas-belajar.create', compact(
            'pegawais',
            'masterTubel',
            'tahun',
            'tahunList',
            'selectedMaster'
        ));
    }

    public function show($id)
    {
        try {
            // ambil master tubel
            $tubel = MasterPelatihan::where('kategori', 'tubel')
                        ->where('id', $id)
                        ->first();

            if (!$tubel) {
                return redirect()->route('tugas-belajar.index')
                    ->with('error', 'Data tidak ditemukan.');
            }

            // ambil peserta berdasarkan master
            $peserta = TubelPeserta::with('pegawai')
                        ->where('master_pelatihan_id', $id)
                        ->get();

            return view('dashboard.tugas-belajar.show', compact('tubel', 'peserta'));

        } catch (\Exception $e) {
            return redirect()->route('tugas-belajar.index')
                ->with('error', 'Gagal memuat detail.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'master_pelatihan_id' => 'required|exists:master_pelatihans,id',

            'pegawai_id'        => 'required|array',
            'pegawai_id.*'      => 'required|exists:pegawai,id',

            'tanggal_mulai'     => 'required|array',
            'tanggal_mulai.*'   => 'required|date',

            'tanggal_selesai'   => 'required|array',
            'tanggal_selesai.*' => 'required|date|after_or_equal:tanggal_mulai.*',

            'no_sk'             => 'nullable|array',
            'no_sk.*'           => 'nullable|string',

            'file_sk'           => 'nullable|array',
            'file_sk.*'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);

            foreach ($request->pegawai_id as $index => $pegawaiId) {

                $filePath = null;

                if ($request->hasFile('file_sk') && isset($request->file('file_sk')[$index])) {

                    $file = $request->file('file_sk')[$index];

                    // ambil data pegawai
                    $pegawai = Pegawai::find($pegawaiId);

                    $ext = $file->getClientOriginalExtension();
                    $timestamp = time();

                    $namaTubel = strtolower(str_replace(' ', '_', $master->nama_pelatihan));

                    $namaFile = 'tubel_' 
                                . $namaTubel . '_' 
                                . $pegawai->nip . '_' 
                                . $master->tahun . '_' 
                                . $timestamp . '.' . $ext;

                    $filePath = $file->storeAs('tubel_sk', $namaFile, 'public');
                }

                DB::table('tubel_peserta')->insert([
                    'master_pelatihan_id' => $master->id,
                    'pegawai_id'          => $pegawaiId,
                    'tanggal_mulai'       => $request->tanggal_mulai[$index],
                    'tanggal_selesai'     => $request->tanggal_selesai[$index],
                    'no_sk_tubel'               => $request->no_sk[$index],
                    'file_sk_tubel'             => $filePath,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('tugas-belajar.index')
                ->with('success', 'Data tugas belajar berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal simpan tubel: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // ambil master tubel
        $tubel = \App\Models\MasterPelatihan::where('kategori', 'tubel')
                    ->where('id', $id)
                    ->firstOrFail();

        // ambil peserta tubel + relasi pegawai
        $peserta = \App\Models\TubelPeserta::with('pegawai')
                    ->where('master_pelatihan_id', $id)
                    ->get();

        // data pegawai (untuk dropdown)
        $pegawais = Pegawai::where('status','aktif')->get();

        return view('dashboard.tugas-belajar.edit', compact('tubel', 'peserta', 'pegawais'));
    }

    public function updatePeserta(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'no_sk_tubel'     => 'nullable|string',
            'file_sk_tubel'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = DB::table('tubel_peserta')->where('id', $id)->first();

            if (!$data) {
                return back()->with('error', 'Data tidak ditemukan');
            }

            $update = [
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'no_sk_tubel'     => $request->no_sk_tubel,
                'updated_at'      => now(),
            ];

            // HANDLE FILE
            if ($request->hasFile('file_sk_tubel')) {
                $file = $request->file('file_sk_tubel');

                $ext = $file->getClientOriginalExtension();
                $timestamp = time();

                // opsional: ambil master buat nama
                $master = DB::table('master_pelatihans')
                            ->where('id', $data->master_pelatihan_id)
                            ->first();

                $namaFile = 'tubel_' 
                    . strtolower(str_replace(' ', '_', $master->nama_pelatihan ?? 'tubel')) . '_'
                    . $data->pegawai_id . '_'
                    . $timestamp . '.' . $ext;

                $path = $file->storeAs('tubel_sk', $namaFile, 'public');

                $update['file_sk_tubel'] = $path;
            }

            DB::table('tubel_peserta')->where('id', $id)->update($update);

            DB::commit();

            return back()->with('success', 'Data peserta berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // ambil semua ID dari form
            $idDariForm = collect($request->id ?? [])->filter()->toArray();

            // ✅ ambil data lama (buat handle file)
            $dataLamaSemua = DB::table('tubel_peserta')
                ->where('master_pelatihan_id', $request->master_pelatihan_id)
                ->get()
                ->keyBy('id');

            // ✅ DELETE yang dihapus di UI + hapus file
            $dataYangDihapus = $dataLamaSemua->except($idDariForm);

            foreach ($dataYangDihapus as $row) {
                if ($row->file_sk_tubel) {
                    Storage::disk('public')->delete($row->file_sk_tubel);
                }

                DB::table('tubel_peserta')->where('id', $row->id)->delete();
            }

            foreach ($request->pegawai_id as $i => $pegawaiId) {

                $rowId = $request->id[$i] ?? null;

                // ambil data lama per baris
                $dataLama = $rowId ? ($dataLamaSemua[$rowId] ?? null) : null;

                $dataUpdate = [
                    'master_pelatihan_id' => $request->master_pelatihan_id,
                    'pegawai_id'          => $pegawaiId,
                    'tanggal_mulai'       => $request->tanggal_mulai[$i],
                    'tanggal_selesai'     => $request->tanggal_selesai[$i],
                    'no_sk_tubel'         => $request->no_sk[$i] ?? null,
                    'updated_at'          => now(),
                ];

                // ✅ HANDLE HAPUS FILE (checkbox)
                if ($rowId && in_array($rowId, $request->hapus_file ?? [])) {
                    if ($dataLama && $dataLama->file_sk_tubel) {
                        Storage::disk('public')->delete($dataLama->file_sk_tubel);
                    }
                    $dataUpdate['file_sk_tubel'] = null;
                }

                // ✅ HANDLE UPLOAD FILE BARU
                if ($request->hasFile("file_sk.$i")) {
                    $file = $request->file("file_sk.$i");

                    // hapus file lama dulu
                    if ($dataLama && $dataLama->file_sk_tubel) {
                        Storage::disk('public')->delete($dataLama->file_sk_tubel);
                    }

                    $ext = $file->getClientOriginalExtension();
                    $timestamp = time();

                    $master = DB::table('master_pelatihans')
                        ->where('id', $request->master_pelatihan_id)
                        ->first();

                    $namaFile = 'tubel_' 
                        . strtolower(str_replace(' ', '_', $master->nama_pelatihan ?? 'tubel')) . '_'
                        . $pegawaiId . '_'
                        . $timestamp . '.' . $ext;

                    $path = $file->storeAs('tubel_sk', $namaFile, 'public');

                    $dataUpdate['file_sk_tubel'] = $path;
                }

                if ($rowId) {
                    DB::table('tubel_peserta')
                        ->where('id', $rowId)
                        ->update($dataUpdate);
                } else {
                    $dataUpdate['created_at'] = now();
                    DB::table('tubel_peserta')->insert($dataUpdate);
                }
            }

            DB::commit();

            return redirect()
                ->route('tugas-belajar.show', $request->master_pelatihan_id)
                ->with('success', 'Data peserta berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // ambil semua peserta berdasarkan master
            $data = DB::table('tubel_peserta')
                ->where('master_pelatihan_id', $id)
                ->get();

            // hapus file satu-satu
            foreach ($data as $row) {
                if ($row->file_sk_tubel) {
                    Storage::disk('public')->delete($row->file_sk_tubel);
                }
            }

            // hapus data peserta
            DB::table('tubel_peserta')
                ->where('master_pelatihan_id', $id)
                ->delete();

            DB::commit();

            return redirect()
                ->route('tugas-belajar.show', $id)
                ->with('success', 'Data peserta berhasil dihapus semua');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}
