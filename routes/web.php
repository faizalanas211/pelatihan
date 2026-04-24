<?php

use App\Exports\NominatifPerjalananExport;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\FingerprintController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PejabatController;
use App\Http\Controllers\PenghasilanController;
use App\Http\Controllers\PerjadinController;
use App\Http\Controllers\PotonganController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\PasswordController;
use App\Models\PerjalananDinas;
use Maatwebsite\Excel\Excel;

// ========== CONTROLLER PELATIHAN & SERTIFIKASI ==========
use App\Http\Controllers\RekapPelatihanController;
use App\Http\Controllers\JadwalPelatihanController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\LaporanPelatihanController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\PelatihController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PelatihanSayaController;
use App\Http\Controllers\SertifikatSayaController;
use App\Http\Controllers\NilaiSayaController;
use App\Http\Controllers\SertifikasiController;
use App\Http\Controllers\MasterPelatihanController;
use App\Http\Controllers\RiwayatSDMController;
use App\Http\Controllers\TugasBelajarController;

/*
|--------------------------------------------------------------------------
| ROUTE DI LUAR MIDDLEWARE (PUBLIC)
|--------------------------------------------------------------------------
*/

// ✅ TEMPLATE DOWNLOAD PELATIHAN - DI LUAR MIDDLEWARE
Route::get('/download-template-pelatihan', [RekapPelatihanController::class, 'downloadTemplate'])->name('rekap-pelatihan.download-template');

// ✅ TEMPLATE DOWNLOAD SERTIFIKASI - DI LUAR MIDDLEWARE
Route::get('/download-template-sertifikasi', [SertifikasiController::class, 'downloadTemplate'])->name('sertifikasi.download-template');

// ✅ TEMPLATE DOWNLOAD TUGAS BELAJAR - DI LUAR MIDDLEWARE
Route::get('/download-template-tugas-belajar', [TugasBelajarController::class, 'downloadTemplate'])->name('tugas-belajar.download-template');

/*
|--------------------------------------------------------------------------
| Guest Routes (BELUM LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginAction'])->name('loginAction');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerAction'])->name('registerAction');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (SUDAH LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('dashboard')->group(function () {

    // DASHBOARD UTAMA
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | PELATIHAN & SERTIFIKASI
    |--------------------------------------------------------------------------
    */
    
    // 1. Rekap Pelatihan
    Route::resource('rekap-pelatihan', RekapPelatihanController::class);
    Route::get('rekap-pelatihan/{id}/peserta', [RekapPelatihanController::class, 'peserta'])->name('rekap-pelatihan.peserta');
    Route::post('rekap-pelatihan/upload-sertifikat-peserta/{id}', [RekapPelatihanController::class, 'uploadSertifikatPeserta'])->name('rekap-pelatihan.upload-sertifikat-peserta');
    Route::put('rekap-pelatihan/peserta/{id}', [RekapPelatihanController::class, 'updatePeserta'])->name('rekap-pelatihan.updatePeserta');
    
    // ✅ ROUTE IMPORT EXCEL PELATIHAN
    Route::post('rekap-pelatihan/import-excel', [RekapPelatihanController::class, 'importExcel'])->name('rekap-pelatihan.import-excel');
    
    // 2. Jadwal Pelatihan
    Route::resource('jadwal-pelatihan', JadwalPelatihanController::class);
    
    // 3. Sertifikasi (BARU & KOLEKTIF)
    Route::resource('sertifikasi', SertifikasiController::class);
    Route::post('sertifikasi/upload-file/{id}', [SertifikasiController::class, 'uploadFile'])->name('sertifikasi.upload-file');
    Route::put('sertifikasi/peserta/{id}', [SertifikasiController::class, 'updatePeserta'])->name('sertifikasi.updatePeserta');
    
    // ✅ TAMBAH: ROUTE IMPORT EXCEL SERTIFIKASI
    Route::post('sertifikasi/import-excel', [SertifikasiController::class, 'importExcel'])->name('sertifikasi.import-excel');
    
    // 4. Sertifikat Event
    Route::resource('sertifikat', SertifikatController::class);
    Route::get('sertifikat/cetak/{id}', [SertifikatController::class, 'cetak'])->name('sertifikat.cetak');
    
    // 5. Laporan Pelatihan
    Route::get('laporan-pelatihan', [LaporanPelatihanController::class, 'index'])->name('laporan-pelatihan.index');
    Route::get('laporan-pelatihan/export-excel', [LaporanPelatihanController::class, 'exportExcel'])->name('laporan-pelatihan.export-excel');
    Route::get('laporan-pelatihan/export-pdf', [LaporanPelatihanController::class, 'exportPdf'])->name('laporan-pelatihan.export-pdf');
    
    // 6. Master Data Pelatihan
    Route::resource('instansi', InstansiController::class);
    Route::resource('pelatih', PelatihController::class);
    Route::resource('materi', MateriController::class);
    Route::resource('role', RoleController::class);
    Route::resource('master-pelatihan', MasterPelatihanController::class);

    // 7. Tugas Belajar
    Route::resource('tugas-belajar', TugasBelajarController::class);
    Route::get('/tugas-belajar/get-tubel', [TugasBelajarController::class, 'getTubelByTahun'])->name('tugas-belajar.get-tubel');
    Route::put('tugas-belajar/peserta/{id}', [TugasBelajarController::class, 'updatePeserta'])->name('tugas-belajar.updatePeserta');
    Route::delete('tugas-belajar/{id}/peserta', [TugasBelajarController::class, 'destroyPeserta'])->name('tugas-belajar.destroyPeserta');
    
    // ✅ ROUTE IMPORT EXCEL TUGAS BELAJAR (POST SAJA, GET SUDAH DIPINDAHKAN KE LUAR)
    Route::post('tugas-belajar/import-excel', [TugasBelajarController::class, 'importExcel'])->name('tugas-belajar.import-excel');
    
    // 8. Riwayat SDM
    Route::get('/riwayat-sdm/export', [RiwayatSDMController::class, 'export'])->name('riwayat-sdm.export');
    Route::get('/riwayat-sdm/{id}/export', [RiwayatSDMController::class, 'exportDetail'])->name('riwayat-sdm.export.detail');
    Route::resource('riwayat-sdm', RiwayatSDMController::class);

    /*
    |--------------------------------------------------------------------------
    | MENU PEGAWAI (USER)
    |--------------------------------------------------------------------------
    */
    Route::get('pelatihan-saya', [PelatihanSayaController::class, 'index'])->name('pelatihan-saya.index');
    Route::get('pelatihan-saya/{id}/detail', [PelatihanSayaController::class, 'detail'])->name('pelatihan-saya.detail');
    
    Route::get('sertifikat-saya', [SertifikatSayaController::class, 'index'])->name('sertifikat-saya.index');
    Route::get('sertifikat-saya/cetak/{id}', [SertifikatSayaController::class, 'cetak'])->name('sertifikat-saya.cetak');
    
    Route::get('nilai-saya', [NilaiSayaController::class, 'index'])->name('nilai-saya.index');

    /*
    |--------------------------------------------------------------------------
    | DATA MASTER (ADMIN)
    |--------------------------------------------------------------------------
    */
    // Pegawai
    Route::resource('pegawai', PegawaiController::class);
    Route::post('pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
    Route::post('pegawai/generate-akun', [PegawaiController::class, 'generateAkun'])->name('pegawai.generateAkun');
    Route::post('pegawai/generate-akun-semua', [PegawaiController::class, 'generateAkunSemua'])->name('pegawai.generateAkunSemua');

    // Pejabat
    Route::resource('pejabat', PejabatController::class);

    // Profile & Password
    Route::get('ganti-password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::post('ganti-password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('profile/update-photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');

    // Pengaturan Template
    Route::resource('template', TemplateController::class);
    Route::get('templates/{id}/preview', [TemplateController::class, 'preview'])->name('template.preview');

    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});