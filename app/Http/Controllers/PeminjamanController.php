<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Barang;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    /**
     * Tampilkan daftar peminjaman (filter bulanan)
     */
    public function index(Request $request)
    {
        // default bulan & tahun (ANTI ERROR)
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        // data peminjaman dengan pagination 5 per halaman
        $peminjamans = Peminjaman::with('barang')
            ->whereMonth('tanggal_pinjam', $bulan)
            ->whereYear('tanggal_pinjam', $tahun)
            ->latest()
            ->paginate(5); // <-- PAGINATE

        // summary menggunakan query terpisah agar akurat (tidak terbatas halaman)
        $total = Peminjaman::whereMonth('tanggal_pinjam', $bulan)
            ->whereYear('tanggal_pinjam', $tahun)
            ->count();

        $dipinjam = Peminjaman::whereMonth('tanggal_pinjam', $bulan)
            ->whereYear('tanggal_pinjam', $tahun)
            ->where('status', 'dipinjam')
            ->count();

        $dikembalikan = Peminjaman::whereMonth('tanggal_pinjam', $bulan)
            ->whereYear('tanggal_pinjam', $tahun)
            ->where('status', 'dikembalikan')
            ->count();

        return view('dashboard.peminjaman.index', compact(
            'peminjamans',
            'bulan',
            'tahun',
            'total',
            'dipinjam',
            'dikembalikan'
        ));
    }

    /**
     * Form peminjaman baru
     */
    public function create()
    {
        // hanya barang yang tersedia
        $barangs = Barang::where('status', 'tersedia')
            ->orderBy('nama_barang', 'asc') // 🔥 URUT A–Z
            ->get();
        return view('dashboard.peminjaman.create', compact('barangs'));
    }

    /**
     * Simpan peminjaman
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_peminjam' => 'required',
            'kelas' => 'required',
            'barang_id' => 'required|exists:barangs,id',
            'tanggal_pinjam' => 'required|date'
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        // pengaman double pinjam
        if ($barang->status == 'dipinjam') {
            return back()->with('error', 'Barang sedang dipinjam');
        }

        // simpan peminjaman
        Peminjaman::create([
            'nama_peminjam' => $request->nama_peminjam,
            'kelas' => $request->kelas,
            'barang_id' => $barang->id,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'status' => 'dipinjam'
        ]);

        // update status barang
        $barang->update([
            'status' => 'dipinjam'
        ]);

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil ditambahkan');
    }

    /**
     * Detail peminjaman
     */
    public function show(Peminjaman $peminjaman)
    {
        return view('dashboard.peminjaman.show', compact('peminjaman'));
    }

    /**
     * Kembalikan barang
     */
    public function kembalikan(Peminjaman $peminjaman)
    {
        $peminjaman->update([
            'status' => 'dikembalikan',
            'tanggal_kembali' => now()
        ]);

        $peminjaman->barang->update([
            'status' => 'tersedia'
        ]);

        return back()->with('success', 'Barang berhasil dikembalikan');
    }
}
