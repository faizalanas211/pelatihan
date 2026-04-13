<?php

namespace App\Http\Controllers;

use App\Imports\KehadiranImport;
use App\Models\Kehadiran;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class KehadiranController extends Controller
{
    public function dashboard()
{
    $today = now()->toDateString();

    // Ambil kehadiran beserta data pegawai
    $kehadiranHariIni = Kehadiran::with('pegawai')
        ->whereDate('tanggal', $today)
        ->orderBy('jam_masuk', 'asc')
        ->take(8)
        ->get();

    // Hitung total status
    $statuses = Kehadiran::whereDate('tanggal', $today)
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

        $totalPegawai = Pegawai::count();

    return view('dashboard.main', compact(
        'statuses',
        'totalPegawai',
        'kehadiranHariIni'
    ));
}

    public function index(Request $request)
    {
        $today = now()->toDateString();

        $query = Kehadiran::with('pegawai');

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        } else {
            $query->whereDate('tanggal', today());
        }

        // Sorting
        $query->orderByRaw("CASE WHEN jam_masuk IS NULL THEN 1 ELSE 0 END ASC") // yg ada jam duluan
            ->orderBy('jam_masuk', 'asc')                                      // yg paling pagi di atas
            ->orderBy('status', 'asc');                                        // untuk yg jamnya null

        // Pagination
        $kehadiranHariIni = $query->paginate(20);

        // Summary status
        $statuses = Kehadiran::whereDate('tanggal', $today)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalPegawai = Pegawai::where('status', 'aktif')->count();

        return view('dashboard.kehadiran.index', compact(
            'kehadiranHariIni',
            'statuses',
            'totalPegawai'
        ));
    }

    public function create()
    {
        $today = now()->toDateString();

        // Pegawai yang sudah punya kehadiran hari ini
        $pegawaiSudahHadir = Kehadiran::whereDate('tanggal', $today)
            ->pluck('pegawai_id');

        // Pegawai aktif yang belum presensi hari ini
        $pegawaiBelumHadir = Pegawai::where('status', 'aktif')
            ->whereNotIn('id', $pegawaiSudahHadir)
            ->orderBy('nama')
            ->get();

        // dd($pegawaiBelumHadir);

        return view('dashboard.kehadiran.create', compact('pegawaiBelumHadir'));
    }

    public function store(Request $request)
    {
        $rules = [
            'pegawai_id' => 'required|exists:pegawai,id',
            'status' => 'required|in:hadir,dinas_luar,dinas_dalam,cuti,sakit',
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date',
        ];

        // Jika status hadir → jam_masuk wajib
        if ($request->status === 'hadir') {
            $rules['jam_masuk'] = 'required|date_format:H:i';
        } else {
            $rules['jam_masuk'] = 'nullable';
        }

        $validated = $request->validate($rules);

        // Simpan data
        Kehadiran::create([
            'pegawai_id' => $validated['pegawai_id'],
            'tanggal'    => $validated['tanggal'],
            'status'     => $validated['status'],
            'jam_masuk'  => $validated['jam_masuk'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()
            ->route('kehadiran.index')
            ->with('success', 'Data kehadiran berhasil ditambahkan!');
    }

    public function show(Kehadiran $kehadiran) {}

    public function edit(Kehadiran $kehadiran) {
        return view('dashboard.kehadiran.edit', compact('kehadiran'));
    }

    public function update(Request $request, Kehadiran $kehadiran)
    {
        // Validasi input
        $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|string',
            'jam_masuk' => 'nullable',
            'keterangan' => 'nullable|string'
        ]);

        $status = $request->status;

        // Jika status bukan hadir → jam masuk harus dibersihkan
        if ($status !== 'hadir') {
            $request->merge([
                'jam_masuk' => null
            ]);
        }

        // Update data kehadiran
        $kehadiran->update([
            'tanggal' => $request->tanggal,
            'status' => $status,
            'jam_masuk' => $request->jam_masuk,
            'keterangan' => $request->keterangan,
        ]);

        // Ambil page dari request
        $page = $request->input('page', 1);

        return redirect()
            ->route('kehadiran.index', ['page' => $page])
            ->with('success', 'Data kehadiran berhasil diperbarui!');
    }

    public function destroy(Kehadiran $kehadiran)
    {
        // Hapus data
        $kehadiran->delete();

        // Redirect
        return redirect()
            ->route('kehadiran.index')
            ->with('success', 'Data kehadiran berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new KehadiranImport, $request->file('file'));

        return redirect()
            ->route('kehadiran.index')
            ->with('success', 'Data kehadiran berhasil diimpor!');
    }
}
