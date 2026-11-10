<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PrestasiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Microservices Architecture (1 Gateway + 4 Services)
|--------------------------------------------------------------------------
|
| Port 8001: Auth Service (Database: db_auth)
| Port 8002: Kegiatan Service (Database: db_kegiatan)
| Port 8003: Pendaftaran Service (Database: db_pendaftaran)
| Port 8004: Prestasi Service (Database: db_prestasi)
|
*/

// =========================================================================
// SERVICE 1: AUTH SERVICE (Port 8001, Database: db_auth)
// =========================================================================
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
});

// =========================================================================
// SERVICE 2: KEGIATAN SERVICE (Port 8002, Database: db_kegiatan)
// =========================================================================
Route::prefix('kegiatan')->group(function () {
    Route::get('/kategori', [KegiatanController::class, 'kategoriIndex'])->name('api.kegiatan.kategori.index');
    Route::post('/kategori', [KegiatanController::class, 'kategoriStore'])->name('api.kegiatan.kategori.store');
    Route::get('/', [KegiatanController::class, 'index'])->name('api.kegiatan.index');
    Route::get('/published', [KegiatanController::class, 'index'])->name('api.kegiatan.published');
    Route::get('/semua', [KegiatanController::class, 'semua'])->name('api.kegiatan.semua');
    Route::post('/', [KegiatanController::class, 'store'])->name('api.kegiatan.store');
    Route::get('/{id}/cek-kuota', [KegiatanController::class, 'cekKuota'])->name('api.kegiatan.cek-kuota');
    Route::post('/{id}/tambah-kuota-terisi', [KegiatanController::class, 'tambahKuotaTerisi'])->name('api.kegiatan.tambah-kuota');
    Route::get('/{id}', [KegiatanController::class, 'show'])->name('api.kegiatan.show');
    Route::put('/{id}', [KegiatanController::class, 'update'])->name('api.kegiatan.update');
    Route::delete('/{id}', [KegiatanController::class, 'destroy'])->name('api.kegiatan.destroy');
});

// =========================================================================
// SERVICE 3: PENDAFTARAN SERVICE (Port 8003, Database: db_pendaftaran)
// =========================================================================
Route::post('/daftar', [PendaftaranController::class, 'daftar'])->name('api.daftar');

Route::prefix('pendaftaran')->group(function () {
    Route::get('/', [PendaftaranController::class, 'index'])->name('api.pendaftaran.index');
    Route::put('/{id}/rekomendasi', [PendaftaranController::class, 'rekomendasi'])->name('api.pendaftaran.rekomendasi');
    Route::put('/{id}/verifikasi-akhir', [PendaftaranController::class, 'verifikasiAkhir'])->name('api.pendaftaran.verifikasi-akhir');
    Route::get('/mahasiswa/{mahasiswa_id}', [PendaftaranController::class, 'riwayatMahasiswa'])->name('api.pendaftaran.riwayat-mahasiswa');
});

// =========================================================================
// SERVICE 4 / TAHAP 5: PRESTASI SERVICE (Port 8004, Database: db_prestasi)
// =========================================================================
Route::prefix('prestasi')->group(function () {
    // A. Mahasiswa: Unggah klaim prestasi & sertifikat
    Route::post('/', [PrestasiController::class, 'store'])->name('api.prestasi.store');

    // B. Mahasiswa: Portofolio prestasi mahasiswa
    Route::get('/mahasiswa/{mahasiswa_id}', [PrestasiController::class, 'riwayatMahasiswa'])->name('api.prestasi.mahasiswa');

    // C. Dosen Pembimbing: Validasi prestasi (Disetujui / Ditolak + Catatan)
    Route::put('/{id}/validasi-dosen', [PrestasiController::class, 'validasiDosen'])->name('api.prestasi.validasi-dosen');

    // D. Admin Kemahasiswaan: Validasi final (Hanya bisa setuju jika dosen sudah setuju)
    Route::put('/{id}/validasi-admin', [PrestasiController::class, 'validasiAdmin'])->name('api.prestasi.validasi-admin');

    // Endpoints Pendukung: List all & Detail prestasi
    Route::get('/', [PrestasiController::class, 'index'])->name('api.prestasi.index');
    Route::get('/{id}', [PrestasiController::class, 'show'])->name('api.prestasi.show');
});
