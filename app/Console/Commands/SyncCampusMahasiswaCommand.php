<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CampusApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncCampusMahasiswaCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'campus:sync-mahasiswa {--limit=20 : Jumlah maksimal mahasiswa yang disinkron}';

    /**
     * The console command description.
     */
    protected $description = 'Sinkronisasi data mahasiswa dari API Kampus ke tabel users lokal';

    /**
     * Execute the console command.
     */
    public function handle(CampusApiService $campusService): int
    {
        $this->info('====================================================');
        $this->info('   SINKRONISASI MAHASISWA DARI API KAMPUS UNUJA    ');
        $this->info('====================================================');

        $this->comment('Mengambil data mahasiswa dari server kampus...');
        $mahasiswaList = $campusService->getReferenceData('mahasiswa', [
            'pagination' => 'on',
        ]);

        if (empty($mahasiswaList)) {
            $this->error('Gagal mengambil data mahasiswa dari API Kampus.');
            return Command::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $items = array_slice($mahasiswaList, 0, $limit);

        $synced = 0;
        $rows = [];

        foreach ($items as $mhs) {
            $nim = (string) ($mhs['nim'] ?? '');
            if (empty($nim)) {
                continue;
            }

            $nama = $mhs['nama'] ?? $mhs['nama_mahasiswa'] ?? 'Mahasiswa';
            $prodi = $mhs['prodi'] ?? 'Teknik';
            $fakultas = $mhs['fakultas'] ?? 'Fakultas Teknik';

            // Sinkronisasi atau buat user baru
            User::updateOrCreate(
                ['nim_nidn' => $nim],
                [
                    'nama_lengkap' => $nama,
                    'role'         => 'mahasiswa',
                    'prodi'        => $prodi,
                    'fakultas'     => $fakultas,
                    'password'     => Hash::make('password123'), // password default
                ]
            );

            $rows[] = [$nim, $nama, $prodi];
            $synced++;
        }

        $this->table(['NIM', 'Nama Mahasiswa', 'Program Studi'], $rows);
        $this->newLine();
        $this->info("✅ Berhasil menyinkronkan {$synced} mahasiswa dari server kampus ke database lokal!");
        $this->line("Semua akun mahasiswa yang disinkron dapat login dengan password: <comment>password123</comment>");

        return Command::SUCCESS;
    }
}

