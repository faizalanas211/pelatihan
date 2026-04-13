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
use App\Models\PerjalananDinas;
use Maatwebsite\Excel\Excel;

// ========== CONTROLLER PELATIHAN (BARU) ==========
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

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD (ADMIN & PEGAWAI)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ================= PELATIHAN (BARU) =================
    |--------------------------------------------------------------------------
    */
    
    // Rekap Pelatihan
    // Rekap Pelatihan (CRUD)
    Route::resource('rekap-pelatihan', RekapPelatihanController::class);
    Route::get('rekap-pelatihan/{id}/peserta', [RekapPelatihanController::class, 'peserta'])->name('rekap-pelatihan.peserta');
    // Jadwal Pelatihan
    Route::resource('jadwal-pelatihan', JadwalPelatihanController::class);
    
    // Sertifikat
    Route::resource('sertifikat', SertifikatController::class);
    Route::get('sertifikat/cetak/{id}', [SertifikatController::class, 'cetak'])->name('sertifikat.cetak');
    
    // Laporan Pelatihan
    Route::get('laporan-pelatihan', [LaporanPelatihanController::class, 'index'])->name('laporan-pelatihan.index');
    Route::get('laporan-pelatihan/export-excel', [LaporanPelatihanController::class, 'exportExcel'])->name('laporan-pelatihan.export-excel');
    Route::get('laporan-pelatihan/export-pdf', [LaporanPelatihanController::class, 'exportPdf'])->name('laporan-pelatihan.export-pdf');
    
    // Master Data Pelatihan
    Route::resource('instansi', InstansiController::class);
    Route::resource('pelatih', PelatihController::class);
    Route::resource('materi', MateriController::class);
    
    // Manajemen Role
    Route::resource('role', RoleController::class);
    
    /*
    |--------------------------------------------------------------------------
    | ================= PELATIHAN UNTUK PEGAWAI =================
    |--------------------------------------------------------------------------
    */
    Route::get('pelatihan-saya', [PelatihanSayaController::class, 'index'])->name('pelatihan-saya.index');
    Route::get('pelatihan-saya/{id}/detail', [PelatihanSayaController::class, 'detail'])->name('pelatihan-saya.detail');
    
    Route::get('sertifikat-saya', [SertifikatSayaController::class, 'index'])->name('sertifikat-saya.index');
    Route::get('sertifikat-saya/cetak/{id}', [SertifikatSayaController::class, 'cetak'])->name('sertifikat-saya.cetak');
    
    Route::get('nilai-saya', [NilaiSayaController::class, 'index'])->name('nilai-saya.index');

    /*
    |--------------------------------------------------------------------------
    | ================= DATA MASTER (ADMIN) - YANG MASIH DIPERLUKAN
    |--------------------------------------------------------------------------
    */

    // Pegawai
    Route::resource('pegawai', PegawaiController::class);
    Route::post('pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
    Route::post('/pegawai/generate-akun', [PegawaiController::class, 'generateAkun'])->name('pegawai.generateAkun');
    Route::post('/pegawai/generate-akun-semua', [PegawaiController::class, 'generateAkunSemua'])->name('pegawai.generateAkunSemua');

    // Pejabat
    Route::resource('pejabat', PejabatController::class);

    // Profile & Password
    Route::get('/ganti-password', [App\Http\Controllers\PasswordController::class, 'edit'])->name('password.edit');
    Route::post('/ganti-password', [App\Http\Controllers\PasswordController::class, 'update'])->name('password.update');
    Route::post('/profile/update-photo', [App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo.update');

    // Pengaturan Template
    Route::resource('template', TemplateController::class);
    Route::get('/dashboard/templates/{id}/preview', [TemplateController::class, 'preview'])->name('template.preview');

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});