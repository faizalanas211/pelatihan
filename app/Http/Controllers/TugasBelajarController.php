<?php

namespace App\Http\Controllers;

use App\Models\MasterPelatihan;
use App\Models\Pegawai;
use App\Models\TubelPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TugasBelajarController extends Controller
{
    public function index(Request $request)
    {
        $tubel = MasterPelatihan::where('kategori', 'tubel')
                ->when($request->tahun, fn($q) => $q->where('tahun', $request->tahun))
                ->when($request->search, fn($q) =>
                    $q->where('nama_pelatihan', 'like', '%' . $request->search . '%')
                )
                ->paginate(9);

        return view('dashboard.tugas-belajar.index', compact('tubel'));
        
    }

    public function create(Request $request)
{
    $pegawais = Pegawai::all();

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
        'tanggal_selesai.*' => 'required|date',

        'no_sk'             => 'required|array',
        'no_sk.*'           => 'required|string',

        'file_sk'           => 'required|array',
        'file_sk.*'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
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
}
