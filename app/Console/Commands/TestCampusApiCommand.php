<?php

namespace App\Console\Commands;

use App\Services\CampusApiService;
use Illuminate\Console\Command;

class TestCampusApiCommand extends Command
{
    /**
     * Nama dan signature command console.
     */
    protected $signature = 'campus:test {--refresh : Paksa refresh access token dari server kampus}';

    /**
     * Deskripsi command console.
     */
    protected $description = 'Uji coba konektivitas dan autentikasi ke API Kampus (Authorize & Get Reference Data)';

    /**
     * Eksekusi console command.
     */
    public function handle(CampusApiService $campusService): int
    {
        $this->info('====================================================');
        $this->info('    UJI KONEKSI API KAMPUS (MASTER REFERENCE DATA)  ');
        $this->info('====================================================');

        $baseUrl = config('services.campus.base_url');
        $devId   = config('services.campus.dev_id');
        $token   = config('services.campus.token');

        $this->line("• Base URL  : <comment>{$baseUrl}</comment>");
        $this->line("• Dev ID    : <comment>{$devId}</comment>");
        $this->line("• X-Token   : <comment>" . substr($token, 0, 4) . '***' . substr($token, -3) . "</comment>");
        $this->newLine();

        if ($this->option('refresh')) {
            $this->comment('Menghapus cache token access lokal...');
            $campusService->clearTokenCache();
        }

        // 1. Tes Authorize
        $this->info('1. Menguji Authorize Data Access (Mendapatkan Access Token 32 Karakter)...');
        $accessToken = $campusService->getAccessToken();

        if ($accessToken) {
            $this->info("   ✅ BERHASIL! Mendapatkan Access Token: [{$accessToken}]");
            $this->line('   Masa berlaku token: 6 Jam (tersimpan di server cache).');
        } else {
            $this->error('   ❌ Gagal mendapatkan Access Token dari endpoint /authorize.');
            $this->line('   Kemungkinan penyebab: Base URL belum aktif atau kredensial dev_id / X-Token salah.');
            $this->comment('   (Aplikasi saat ini menggunakan fallback lokal agar UI tetap berjalan lancar)');
            return Command::FAILURE;
        }

        $this->newLine();

        // 2. Tes Get Reference Data: Dosen
        $this->info('2. Menguji Pengambilan Data Referensi Dosen Kampus (Filter: dosen)...');
        $daftarDosen = $campusService->getDaftarDosen();

        if (!empty($daftarDosen)) {
            $this->info("   ✅ BERHASIL! Ditemukan " . count($daftarDosen) . " Dosen Resmi dari Server Kampus:");
            $rows = [];
            foreach (array_slice($daftarDosen, 0, 6) as $d) {
                $rows[] = [
                    'NIDN'     => $d['nim_nidn'] ?? '-',
                    'Nama'     => $d['nama_lengkap'] ?? '-',
                    'Prodi'    => $d['prodi'] ?? '-',
                ];
            }
            $this->table(['NIDN', 'Nama Dosen & Gelar', 'Program Studi'], $rows);
        } else {
            $this->warn('   ⚠️ Data referensi dosen kosong atau menggunakan fallback lokal.');
        }

        $this->newLine();
        $this->info('====================================================');
        $this->info('   INTEGRASI API KAMPUS UNUJA BERJALAN DENGAN SUKSES ');
        $this->info('====================================================');

        return Command::SUCCESS;
    }
}
