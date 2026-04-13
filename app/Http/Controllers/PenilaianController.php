<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alihdaya;
use App\Models\PegawaiAlihDaya;
use App\Models\Penilaian;
use App\Imports\AlihDayaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class PenilaianController extends Controller
{
    /**
     * Tampilkan daftar pegawai alihdaya
     */
    public function index()
    {
        $pegawaiAlihdaya = Alihdaya::paginate(10);
        return view('dashboard.penilaian.index', compact('pegawaiAlihdaya'));
    }

    /**
     * Form tambah pegawai alihdaya
     */
    public function create()
    {
        return view('dashboard.penilaian.create');
    }

    /**
     * Simpan pegawai alihdaya baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nama', 'jabatan']);

        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('pegawai', 'public');
        }

        Alihdaya::create($data);

        return redirect()->route('penilaian.index')->with('success', 'Pegawai alihdaya berhasil ditambahkan.');
    }

    /**
     * Form edit pegawai
     */
    public function edit($id)
    {
        $pegawai = Alihdaya::findOrFail($id);
        return view('dashboard.penilaian.edit-pegawai', compact('pegawai'));
    }

    /**
     * Update pegawai
     */
    public function update(Request $request, $id)
    {
        $pegawai = Alihdaya::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nama', 'jabatan']);

        // Jika upload foto baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
                Storage::disk('public')->delete($pegawai->foto);
            }
            $data['foto'] = $request->file('foto')->store('pegawai', 'public');
        }

        $pegawai->update($data);

        return redirect()->route('penilaian.index')->with('success', 'Pegawai alihdaya berhasil diperbarui.');
    }

    /**
     * Hapus pegawai
     */
    public function destroy($id)
    {
        $pegawai = Alihdaya::findOrFail($id);

        // Hapus foto jika ada
        if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
            Storage::disk('public')->delete($pegawai->foto);
        }

        $pegawai->delete();

        return redirect()->route('penilaian.index')->with('success', 'Pegawai alihdaya berhasil dihapus.');
    }

    /**
     * Import Excel pegawai
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new AlihDayaImport, $request->file('file'));

        return back()->with('success', 'Data berhasil diimport!');
    }

    // ==============================
    // Bagian Penilaian Pegawai
    // ==============================

    /**
     * Form tambah penilaian berdasarkan pegawai
     */
    public function createAlihdaya($alihdaya_id)
{
    $alihdaya = Alihdaya::findOrFail($alihdaya_id);
    return view('dashboard.penilaian.create-by-pegawai', compact('alihdaya'));
}

public function storeByPegawai(Request $request, $id)
{
    $request->validate([
        'bulan' => 'required|string',
        'tahun' => 'required|integer',

        'kedisiplinan' => 'required|integer',
        'tanggung_jawab' => 'required|integer',
        'sikap' => 'required|integer',
        'kompetensi' => 'required|integer',
        'penampilan' => 'required|integer',
        'komunikasi' => 'required|integer',
        'kepatuhan_vendor' => 'required|integer',

        'rekomendasi' => 'required|string',

        'penilai_nama' => 'required|string',
        'penilai_jabatan' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    Penilaian::create([
        'pegawai_alihdaya_id' => $id,
        'bulan' => $request->bulan,
        'tahun' => $request->tahun,

        'kedisiplinan' => $request->kedisiplinan,
        'kedisiplinan_catatan' => $request->kedisiplinan_catatan,

        'tanggung_jawab' => $request->tanggung_jawab,
        'tanggung_jawab_catatan' => $request->tanggung_jawab_catatan,

        'sikap' => $request->sikap,
        'sikap_catatan' => $request->sikap_catatan,

        'kompetensi' => $request->kompetensi,
        'kompetensi_catatan' => $request->kompetensi_catatan,

        'penampilan' => $request->penampilan,
        'penampilan_catatan' => $request->penampilan_catatan,

        'komunikasi' => $request->komunikasi,
        'komunikasi_catatan' => $request->komunikasi_catatan,

        'kepatuhan_vendor' => $request->kepatuhan_vendor,
        'kepatuhan_vendor_catatan' => $request->kepatuhan_vendor_catatan,

        'rekomendasi' => $request->rekomendasi,
        'rekomendasi_lain' => $request->rekomendasi_lain,

        'penilai_nama' => $request->penilai_nama,
        'penilai_jabatan' => $request->penilai_jabatan,
        'tanggal' => $request->tanggal,
    ]);

    return redirect()->route('penilaian.index')
        ->with('success', 'Penilaian berhasil disimpan!');
}



}
