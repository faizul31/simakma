<?php

use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - API Gateway Frontend (Port 8000)
|--------------------------------------------------------------------------
|
| Gateway ini tidak memiliki database SQL sendiri, melainkan merender Blade
| view dan berkomunikasi dengan Microservices lain melalui HTTP Client.
|
*/

// Rute Publik (Authentication Web UI)
Route::get('/', [WebController::class, 'showLogin'])->name('home');
Route::get('/login', [WebController::class, 'showLogin'])->name('web.login');
Route::post('/login', [WebController::class, 'processLogin'])->name('web.login.process');
Route::match(['get', 'post'], '/logout', [WebController::class, 'logout'])->name('web.logout');

// Rute Terproteksi: Mahasiswa
Route::middleware(['gateway.auth:mahasiswa'])->group(function () {
    Route::get('/mahasiswa/dashboard', [WebController::class, 'dashboardMahasiswa'])->name('web.mahasiswa.dashboard');
    Route::post('/mahasiswa/daftar', [WebController::class, 'daftarKegiatan'])->name('web.mahasiswa.daftar');
    Route::post('/mahasiswa/prestasi', [WebController::class, 'ajukanPrestasi'])->name('web.mahasiswa.prestasi');
});

// Rute Terproteksi: Dosen
Route::middleware(['gateway.auth:dosen'])->group(function () {
    Route::get('/dosen/dashboard', [WebController::class, 'dashboardDosen'])->name('web.dosen.dashboard');
    Route::put('/dosen/pendaftaran/{id}/rekomendasi', [WebController::class, 'rekomendasiPendaftaran'])->name('web.dosen.pendaftaran.rekomendasi');
    Route::put('/dosen/prestasi/{id}/validasi', [WebController::class, 'validasiPrestasiDosen'])->name('web.dosen.prestasi.validasi');
});

// Rute Terproteksi: Admin Kemahasiswaan & Ormawa
Route::middleware(['gateway.auth:admin'])->group(function () {
    Route::get('/admin/dashboard', [WebController::class, 'dashboardAdmin'])->name('web.admin.dashboard');

    // Manajemen Master Data Kegiatan & Kategori
    Route::post('/admin/kegiatan', [WebController::class, 'tambahKegiatan'])->name('web.admin.kegiatan.store');
    Route::put('/admin/kegiatan/{id}', [WebController::class, 'updateKegiatan'])->name('web.admin.kegiatan.update');
    Route::post('/admin/kegiatan/{id}/toggle', [WebController::class, 'togglePublikasiKegiatan'])->name('web.admin.kegiatan.toggle');
    Route::delete('/admin/kegiatan/{id}', [WebController::class, 'hapusKegiatan'])->name('web.admin.kegiatan.destroy');
    Route::post('/admin/kategori', [WebController::class, 'tambahKategori'])->name('web.admin.kategori.store');

    // Verifikasi Transaksi
    Route::put('/admin/pendaftaran/{id}/verifikasi', [WebController::class, 'verifikasiAkhirPendaftaran'])->name('web.admin.pendaftaran.verifikasi');
    Route::put('/admin/prestasi/{id}/validasi', [WebController::class, 'validasiAkhirPrestasi'])->name('web.admin.prestasi.validasi');
});
