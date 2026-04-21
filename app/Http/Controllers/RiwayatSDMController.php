<?php

namespace App\Http\Controllers;

use App\Exports\RiwayatSdmDetailExport;
use App\Exports\RiwayatSdmExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RiwayatSDMController extends Controller
{
    public function index()
{
    $search = request('search');
    $sort = request('sort', 'jp');
    $direction = request('direction', 'desc');

    // 🔹 SUBQUERY PELATIHAN
    $pelatihan = DB::table('pelatihan_peserta')
        ->select(
            'nip',
            DB::raw('COUNT(*) as total_pelatihan'),
            DB::raw('COALESCE(SUM(jp),0) as total_jp')
        )
        ->groupBy('nip');

    // 🔹 SUBQUERY SERTIFIKASI
    $sertifikasi = DB::table('sertifikasi_peserta')
        ->select(
            'nip',
            DB::raw('COUNT(*) as total_sertifikasi')
        )
        ->groupBy('nip');

    // 🔹 SUBQUERY TUBEL
    $tubel = DB::table('tubel_peserta as t')
        ->join('pegawai as p', 't.pegawai_id', '=', 'p.id')
        ->select(
            'p.nip',
            DB::raw('COUNT(t.id) as total_tubel')
        )
        ->groupBy('p.nip');

    // 🔹 QUERY UTAMA
    $query = DB::table('pegawai as p')
        ->leftJoinSub($pelatihan, 'pl', function ($join) {
            $join->on('p.nip', '=', 'pl.nip');
        })
        ->leftJoinSub($sertifikasi, 's', function ($join) {
            $join->on('p.nip', '=', 's.nip');
        })
        ->leftJoinSub($tubel, 't', function ($join) {
            $join->on('p.nip', '=', 't.nip');
        })
        ->where('p.status', 'aktif')
        ->select(
            'p.id',
            'p.nama',
            'p.nip',
            DB::raw('COALESCE(pl.total_pelatihan,0) as total_pelatihan'),
            DB::raw('COALESCE(s.total_sertifikasi,0) as total_sertifikasi'),
            DB::raw('COALESCE(t.total_tubel,0) as total_tubel'),
            DB::raw('COALESCE(pl.total_jp,0) as total_jp')
        );

    // 🔍 SEARCH
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('p.nama', 'like', "%$search%")
              ->orWhere('p.nip', 'like', "%$search%");
        });
    }

    // 🔽 SORTING
    switch ($sort) {
        case 'jp':
            $query->orderBy('total_jp', $direction);
            break;

        case 'pelatihan':
            $query->orderBy('total_pelatihan', $direction);
            break;

        case 'sertifikasi':
            $query->orderBy('total_sertifikasi', $direction);
            break;

        case 'tubel':
            $query->orderBy('total_tubel', $direction);
            break;

        default:
            $query->orderBy('p.nama', $direction);
            break;
    }

    $data = $query->paginate(30)->withQueryString();

    return view('dashboard.riwayat-sdm.index', compact('data'));
}

    public function show($id)
{
    try {
        $pegawai = DB::table('pegawai')->where('id', $id)->first();

        if (!$pegawai) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }

        /*
        |--------------------------------
        | PELATIHAN
        |--------------------------------
        */
        $pelatihan = DB::table('pelatihan_peserta as pp')
            ->leftJoin('pelatihan as p', 'pp.pelatihan_id', '=', 'p.id')
            ->where('pp.nip', $pegawai->nip)
            ->select(
                'p.jenis_pelatihan',
                'pp.jp',
                'pp.tanggal_mulai',
                'pp.tanggal_selesai',
            )
            ->get();

        /*
        |--------------------------------
        | SERTIFIKASI
        |--------------------------------
        */
        $sertifikasi = DB::table('sertifikasi_peserta as sp')
            ->leftJoin('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
            ->where('sp.nip', $pegawai->nip)
            ->select(
                's.jenis_sertifikasi',
                's.instansi_penerbit',
                'sp.tanggal_mulai',
                'sp.tanggal_selesai',
                'sp.masa_berlaku'
            )
            ->get();

        /*
        |--------------------------------
        | TUBEL
        |--------------------------------
        */
        $tubel = DB::table('tubel_peserta as tp')
            ->leftJoin('master_pelatihans as mp', 'tp.master_pelatihan_id', '=', 'mp.id')
            ->where('tp.pegawai_id', $pegawai->id)
            ->select(
                'mp.nama_pelatihan',
                'tp.tanggal_mulai',
                'tp.tanggal_selesai',
                'tp.no_sk_tubel'
            )
            ->get();

        $summary = [
            'pelatihan'   => $pelatihan->count(),
            'sertifikasi' => $sertifikasi->count(),
            'tubel'       => $tubel->count(),
            'jp'          => $pelatihan->sum('jp'),
        ];

        return view('dashboard.riwayat-sdm.show', compact(
            'pegawai',
            'pelatihan',
            'sertifikasi',
            'tubel',
            'summary'
        ));

    } catch (\Exception $e) {
        return back()->with('error', 'Gagal memuat detail: ' . $e->getMessage());
    }
}

public function export()
{
    $search = request('search');
    $sort = request('sort');
    $direction = request('direction', 'asc');

    $pegawais = DB::table('pegawai')
        ->where('status', 'aktif')
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('nama', 'like', "%$search%")
                    ->orWhere('nip', 'like', "%$search%");
            });
        })
        ->select('id', 'nama', 'nip')
        ->get();

    // ambil data agregat (SAMA kayak index kamu)
    $pelatihan = DB::table('pelatihan_peserta')
        ->select('nip', DB::raw('COUNT(*) as total_pelatihan'), DB::raw('SUM(jp) as total_jp'))
        ->groupBy('nip')->get()->keyBy('nip');

    $sertifikasi = DB::table('sertifikasi_peserta')
        ->select('nip', DB::raw('COUNT(*) as total_sertifikasi'))
        ->groupBy('nip')->get()->keyBy('nip');

    $tubel = DB::table('tubel_peserta as t')
        ->join('pegawai as p', 't.pegawai_id', '=', 'p.id')
        ->select('p.nip', DB::raw('COUNT(t.id) as total_tubel'))
        ->groupBy('p.nip')->get()->keyBy('nip');

    // mapping
    $data = $pegawais->map(function ($p) use ($pelatihan, $sertifikasi, $tubel) {
        return (object)[
            'nama' => $p->nama,
            'nip' => $p->nip,
            'total_pelatihan' => $pelatihan[$p->nip]->total_pelatihan ?? 0,
            'total_sertifikasi' => $sertifikasi[$p->nip]->total_sertifikasi ?? 0,
            'total_tubel' => $tubel[$p->nip]->total_tubel ?? 0,
            'total_jp' => $pelatihan[$p->nip]->total_jp ?? 0,
        ];
    });

    // SORT (manual karena ini collection)
    if ($sort) {
        $data = $data->sortBy($sort, SORT_REGULAR, $direction === 'desc')->values();
    }

    return Excel::download(new RiwayatSdmExport($data), 'riwayat_bangkom_sdm.xlsx');
}

public function exportDetail($id)
{
    $pegawai = DB::table('pegawai')->where('id', $id)->first();

    if (!$pegawai) {
        abort(404, 'Pegawai tidak ditemukan');
    }

    $pelatihan = DB::table('pelatihan_peserta as pp')
    ->leftJoin('pelatihan as p', 'pp.pelatihan_id', '=', 'p.id')
    ->where('pp.nip', $pegawai->nip)
    ->select(
        'p.jenis_pelatihan',
        'p.tahun',
        'p.instansi_penyelenggara',
        'pp.jp',
        'pp.tanggal_mulai',
        'pp.tanggal_selesai'
    )
    ->get();

    $sertifikasi = DB::table('sertifikasi_peserta as sp')
            ->leftJoin('sertifikasi as s', 'sp.sertifikasi_id', '=', 's.id')
            ->where('sp.nip', $pegawai->nip)
            ->select(
                's.jenis_sertifikasi',
                's.instansi_penerbit',
                'sp.tanggal_mulai',
                'sp.tanggal_selesai',
                'sp.masa_berlaku'
            )
            ->get();

    $tubel = DB::table('tubel_peserta as t')
            ->leftJoin('master_pelatihans as mp', 't.master_pelatihan_id', '=', 'mp.id')
            ->join('pegawai as p', 't.pegawai_id', '=', 'p.id')
            ->where('p.id', $id)
            ->select(
                't.*',
                'mp.nama_pelatihan'
            )
            ->get();

    return Excel::download(
        new RiwayatSdmDetailExport($pegawai, $pelatihan, $sertifikasi, $tubel),
        'riwayat_'.$pegawai->nama.'.xlsx'
    );
}
}
