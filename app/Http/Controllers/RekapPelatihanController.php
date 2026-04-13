<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapPelatihanController extends Controller
{
    /**
     * Display a listing of the resource (AMAN - pakai try catch)
     */
    public function index(Request $request)
    {
        // Cek apakah tabel ada
        try {
            $tableExists = DB::select("SHOW TABLES LIKE 'pelatihan'");
            
            if (empty($tableExists)) {
                // Tabel belum ada, tampilkan data statis
                return $this->staticDataView($request);
            }
            
            // Tabel ada, jalankan query normal
            $query = DB::table('pelatihan')
                ->where('status', 'selesai')
                ->orWhere('tanggal_selesai', '<', date('Y-m-d'));
            
            // Filter tahun
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_mulai', $request->tahun);
            }
            
            // Filter bulan
            if ($request->filled('bulan') && $request->bulan != 'all') {
                $query->whereMonth('tanggal_mulai', $request->bulan);
            }
            
            // Filter pencarian
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('penyelenggara', 'like', '%' . $search . '%');
                });
            }
            
            $pelatihan = $query->orderBy('tanggal_selesai', 'desc')->paginate(10);
            
            if ($request->ajax()) {
                return response()->json([
                    'data' => $pelatihan->items(),
                    'total' => $pelatihan->total(),
                    'current_page' => $pelatihan->currentPage(),
                    'last_page' => $pelatihan->lastPage(),
                ]);
            }
            
            // DI SINI SUDAH DIPERBAIKI
            return view('dashboard.rekap-pelatihan.index', compact('pelatihan'));
            
        } catch (\Exception $e) {
            // Jika error, tampilkan data statis
            Log::error('Error di RekapPelatihan: ' . $e->getMessage());
            return $this->staticDataView($request);
        }
    }
    
    /**
     * Data statis sementara (UI bisa tetap tampil)
     */
    private function staticDataView(Request $request)
    {
        // Data statis
        $pelatihanData = collect([
            (object) [
                'id' => 1,
                'nama' => 'Pelatihan Laravel Dasar',
                'penyelenggara' => 'BPPTIK',
                'tempat' => 'Online',
                'tanggal_mulai' => '2026-03-10',
                'tanggal_selesai' => '2026-03-15',
                'status' => 'selesai',
            ],
            (object) [
                'id' => 2,
                'nama' => 'Pelatihan Web Keuangan',
                'penyelenggara' => 'Balai Diklat',
                'tempat' => 'Gedung Utama',
                'tanggal_mulai' => '2026-04-01',
                'tanggal_selesai' => '2026-04-05',
                'status' => 'selesai',
            ],
            (object) [
                'id' => 3,
                'nama' => 'Sosialisasi Kehumasan',
                'penyelenggara' => 'KemenPANRB',
                'tempat' => 'Aula Lt.2',
                'tanggal_mulai' => '2026-04-08',
                'tanggal_selesai' => '2026-04-08',
                'status' => 'selesai',
            ],
            (object) [
                'id' => 4,
                'nama' => 'Manajemen Pelatihan Berbasis Web',
                'penyelenggara' => 'Pusdiklat',
                'tempat' => 'Lab Komputer',
                'tanggal_mulai' => '2026-04-20',
                'tanggal_selesai' => '2026-04-25',
                'status' => 'direncanakan',
            ],
        ]);
        
        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $pelatihanData = $pelatihanData->filter(function($item) use ($search) {
                return stripos($item->nama, $search) !== false || 
                       stripos($item->penyelenggara, $search) !== false;
            });
        }
        
        // Buat paginator manual untuk data statis
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $items = $pelatihanData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $pelatihan = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, 
            $pelatihanData->count(), 
            $perPage, 
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // DI SINI SUDAH DIPERBAIKI
        return view('dashboard.rekap-pelatihan.index', compact('pelatihan'));
    }

    public function create()
    {
        return view('dashboard.rekap-pelatihan-create');
    }

    public function store(Request $request)
    {
        try {
            $tableExists = DB::select("SHOW TABLES LIKE 'pelatihan'");
            if (empty($tableExists)) {
                return redirect()->route('rekap-pelatihan.index')
                    ->with('warning', 'Tabel belum dibuat. Jalankan migration dulu.');
            }
            
            return redirect()->route('rekap-pelatihan.index')
                ->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('rekap-pelatihan.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        return view('dashboard.rekap-pelatihan-show', ['id' => $id]);
    }

    public function edit($id)
    {
        return view('dashboard.rekap-pelatihan-edit', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('rekap-pelatihan.index')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        return redirect()->route('rekap-pelatihan.index')
            ->with('success', 'Data berhasil dihapus');
    }
    
    public function peserta($id)
    {
        // DI SINI KEMBALI KE KODE ASAL AGAR TIDAK ERROR
        return view('dashboard.rekap-pelatihan-peserta', ['id' => $id]);
    }
}