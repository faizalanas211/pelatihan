<?php

namespace App\Http\Controllers;

use App\Models\MasterPelatihan;
use App\Models\Pegawai;
use App\Models\TubelPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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
                    'no_sk_tubel'         => $request->no_sk[$index],
                    'file_sk_tubel'       => $filePath,
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
            // ✅ CEK APAKAH ADA DATA PESERTA YANG DIKIRIM
            if (empty($request->pegawai_id) || !is_array($request->pegawai_id)) {
                // HAPUS SEMUA PESERTA
                $existingPeserta = DB::table('tubel_peserta')
                    ->where('master_pelatihan_id', $request->master_pelatihan_id)
                    ->get();
                
                foreach ($existingPeserta as $row) {
                    if ($row->file_sk_tubel) {
                        Storage::disk('public')->delete($row->file_sk_tubel);
                    }
                    DB::table('tubel_peserta')->where('id', $row->id)->delete();
                }
                
                DB::commit();
                return redirect()
                    ->route('tugas-belajar.show', $request->master_pelatihan_id)
                    ->with('success', 'Data peserta berhasil diperbarui (semua peserta dihapus)');
            }

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

    /**
     * IMPORT EXCEL: Import peserta dari file Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'master_pelatihan_id' => 'required|exists:master_pelatihans,id',
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $master = MasterPelatihan::findOrFail($request->master_pelatihan_id);

            // 1. BACA FILE EXCEL
            $file = $request->file('file_excel');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Hapus header (baris pertama)
            array_shift($rows);
            
            // Hapus baris kosong
            $rows = array_values(array_filter($rows, function($row) {
                return !(empty($row[0]) && empty($row[1]));
            }));

            $pesertaValid = [];
            $errors = [];

            // Fungsi bantu konversi tanggal
            $parseDate = function($dateString) {
                if (empty($dateString)) return null;
                $dateString = trim($dateString);
                
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                    return $dateString;
                }
                
                if (is_numeric($dateString)) {
                    return ExcelDate::excelToDateTimeObject($dateString)->format('Y-m-d');
                }
                
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
                    $day = $matches[1];
                    $month = $matches[2];
                    $year = $matches[3];
                    if (checkdate($month, $day, $year)) {
                        return sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
                
                if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateString, $matches)) {
                    $day = $matches[1];
                    $month = $matches[2];
                    $year = $matches[3];
                    if (checkdate($month, $day, $year)) {
                        return sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
                
                $timestamp = strtotime($dateString);
                if ($timestamp !== false) {
                    $result = date('Y-m-d', $timestamp);
                    if ($result && $result != '1970-01-01') {
                        return $result;
                    }
                }
                
                return null;
            };

            // ✅ VALIDASI SEMUA BARIS TERLEBIH DAHULU
            foreach ($rows as $rowIndex => $row) {
                $nip = trim($row[0] ?? '');
                $nama = trim($row[1] ?? ''); // tidak dipakai, hanya untuk user
                $tanggalMulaiRaw = $row[2] ?? null;
                $tanggalSelesaiRaw = $row[3] ?? null;
                $noSk = trim($row[4] ?? '');

                if (empty($nip)) {
                    $errors[] = "Baris " . ($rowIndex + 2) . ": NIP kosong";
                    continue;
                }

                // Cek apakah pegawai ada di database
                $pegawai = DB::table('pegawai')->where('nip', $nip)->first();
                if (!$pegawai) {
                    $errors[] = "Baris " . ($rowIndex + 2) . ": NIP '$nip' tidak ditemukan di database pegawai";
                    continue;
                }

                $tanggalMulai = $parseDate($tanggalMulaiRaw);
                $tanggalSelesai = $parseDate($tanggalSelesaiRaw);

                if (!$tanggalMulai) {
                    $errors[] = "Baris " . ($rowIndex + 2) . ": Format tanggal mulai tidak valid ('{$tanggalMulaiRaw}')";
                    continue;
                }
                
                if (!$tanggalSelesai) {
                    $errors[] = "Baris " . ($rowIndex + 2) . ": Format tanggal selesai tidak valid ('{$tanggalSelesaiRaw}')";
                    continue;
                }

                // ✅ DATA VALID, TAMPUNG DULU
                $pesertaValid[] = [
                    'pegawai_id'      => $pegawai->id,
                    'nip'             => $nip,
                    'tanggal_mulai'   => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'no_sk_tubel'     => $noSk,
                ];
            }

            // ✅ JIKA ADA ERROR, BATALKAN
            if (!empty($errors)) {
                DB::rollBack();
                $errorMessage = "Import gagal! Terdapat " . count($errors) . " error.\n";
                $errorMessage .= implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorMessage .= ", dan " . (count($errors) - 5) . " error lainnya.";
                }
                return redirect()->back()->withInput()->with('error', $errorMessage);
            }

            // ✅ CEK NIP YANG SUDAH ADA DI DATABASE
            $existingPegawaiIds = DB::table('tubel_peserta')
                ->where('master_pelatihan_id', $master->id)
                ->pluck('pegawai_id')
                ->map(fn($id) => (int)$id)
                ->toArray();

            // ✅ PISAHKAN DATA BARU VS DUPLIKAT
            $dataBaru = [];
            $dataDuplikat = [];

            foreach ($pesertaValid as $peserta) {
                if (in_array($peserta['pegawai_id'], $existingPegawaiIds)) {
                    $dataDuplikat[] = $peserta;
                } else {
                    $dataBaru[] = $peserta;
                }
            }

            // ✅ JIKA SEMUA DATA DUPLIKAT (TIDAK ADA DATA BARU)
            if (empty($dataBaru) && !empty($dataDuplikat)) {
                DB::commit();
                return redirect()->route('tugas-belajar.show', $master->id)
                    ->with('warning', 'Tidak ada data baru! Semua data (' . count($dataDuplikat) . ' peserta) sudah terdaftar sebelumnya.');
            }

            // ✅ HAPUS DATA LAMA HANYA JIKA ADA DATA BARU
            if (!empty($dataBaru)) {
                // Hapus semua peserta lama
                $oldPeserta = DB::table('tubel_peserta')
                    ->where('master_pelatihan_id', $master->id)
                    ->get();
                
                foreach ($oldPeserta as $row) {
                    if ($row->file_sk_tubel) {
                        Storage::disk('public')->delete($row->file_sk_tubel);
                    }
                }
                
                DB::table('tubel_peserta')
                    ->where('master_pelatihan_id', $master->id)
                    ->delete();
            }

            // ✅ INSERT HANYA DATA BARU (YANG TIDAK DUPLIKAT)
            foreach ($dataBaru as $peserta) {
                DB::table('tubel_peserta')->insert([
                    'master_pelatihan_id' => $master->id,
                    'pegawai_id'          => $peserta['pegawai_id'],
                    'tanggal_mulai'       => $peserta['tanggal_mulai'],
                    'tanggal_selesai'     => $peserta['tanggal_selesai'],
                    'no_sk_tubel'         => $peserta['no_sk_tubel'],
                    'file_sk_tubel'       => null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            DB::commit();

            // ✅ BUAT PESAN SUKSES + WARNING DUPLIKAT
            $message = "Import selesai! " . count($dataBaru) . " peserta berhasil ditambahkan.";
            if (!empty($dataDuplikat)) {
                $message .= " " . count($dataDuplikat) . " peserta duplikat diabaikan.";
            }

            return redirect()->route('tugas-belajar.show', $master->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Excel tugas belajar gagal: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal import Excel: ' . $e->getMessage());
        }
    }

    /**
     * DOWNLOAD TEMPLATE EXCEL
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header kolom (5 kolom untuk tugas belajar - SAMA DENGAN PELATIHAN)
            $sheet->setCellValue('A1', 'NIP');
            $sheet->setCellValue('B1', 'NAMA PESERTA');
            $sheet->setCellValue('C1', 'TANGGAL MULAI');
            $sheet->setCellValue('D1', 'TANGGAL SELESAI');
            $sheet->setCellValue('E1', 'NO SK TUGAS BELAJAR (Opsional)');

            // Contoh data
            $sheet->setCellValue('A2', '123456789012345678');
            $sheet->setCellValue('B2', 'Contoh Pegawai');
            $sheet->setCellValue('C2', '2024-01-01');
            $sheet->setCellValue('D2', '2024-12-31');
            $sheet->setCellValue('E2', 'SK.001/2024');

            // Style header
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);
            $sheet->getStyle('A1:E1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF97316');

            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set response headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_import_tugas_belajar.xlsx"');
            header('Cache-Control: max-age=0');
            header('Expires: Mon, 01 Jan 1990 00:00:00 GMT');
            header('Pragma: public');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            Log::error('Download template tugas belajar gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal download template: ' . $e->getMessage());
        }
    }
}