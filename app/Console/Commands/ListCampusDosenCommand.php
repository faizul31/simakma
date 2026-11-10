<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CampusApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ListCampusDosenCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'campus:list-dosen 
                            {--search= : Cari dosen berdasarkan nama atau NIDN}
                            {--limit=15 : Batas jumlah dosen yang ditampilkan (default: 15)}
                            {--all : Tampilkan semua dosen tanpa batasan limit}
                            {--sync : Sinkronkan langsung dosen yang ditampilkan ke database lokal users}';

    /**
     * The console command description.
     */
    protected $description = 'Melihat daftar Dosen dan NIDN dari API Kampus UNUJA serta sinkronisasi ke DB lokal';

    /**
     * Execute the console command.
     */
    public function handle(CampusApiService $campusService): int
    {
        $this->info('====================================================');
        $this->info('      DAFTAR DOSEN & NIDN DARI API KAMPUS UNUJA    ');
        $this->info('====================================================');

        $search = $this->option('search');
        if (!empty($search)) {
            $this->comment("Mencari dosen dengan kata kunci: [{$search}]...");
        } else {
            $this->comment('Mengambil daftar dosen dari API Kampus...');
        }

        $dosenList = $campusService->getDaftarDosen($search);

        if (empty($dosenList)) {
            $this->error('Tidak ada data dosen yang ditemukan dari API Kampus.');
            return Command::FAILURE;
        }

        // Jika pencarian diberikan, filter juga secara lokal untuk memastikan match nama atau NIDN
        if (!empty($search)) {
            $keyword = strtolower($search);
            $dosenList = array_filter($dosenList, function ($d) use ($keyword) {
                return str_contains(strtolower($d['nama_lengkap'] ?? ''), $keyword)
                    || str_contains(strtolower($d['nim_nidn'] ?? ''), $keyword)
                    || str_contains(strtolower($d['prodi'] ?? ''), $keyword);
            });
        }

        $totalFound = count($dosenList);
        $this->info("Ditemukan {$totalFound} dosen.");

        $limit = $this->option('all') ? $totalFound : (int) $this->option('limit');
        $items = array_slice($dosenList, 0, $limit);

        $doSync = (bool) $this->option('sync');
        $rows = [];
        $syncedCount = 0;

        foreach ($items as $index => $dsn) {
            $nidn = (string) ($dsn['nim_nidn'] ?? '-');
            $nama = $dsn['nama_lengkap'] ?? 'Dosen';
            $prodi = $dsn['prodi'] ?? 'Teknik';

            // Cek apakah akun sudah terdaftar di database lokal
            $localUser = ($nidn !== '-' && !empty($nidn))
                ? User::where('nim_nidn', $nidn)->first()
                : null;

            if ($doSync && $nidn !== '-' && !empty($nidn)) {
                User::updateOrCreate(
                    ['nim_nidn' => $nidn],
                    [
                        'nama_lengkap' => $nama,
                        'role'         => 'dosen',
                        'prodi'        => $prodi,
                        'fakultas'     => 'Fakultas Teknik',
                        'password'     => Hash::make('password123'),
                    ]
                );
                $statusDb = '✅ Baru Disinkron';
                $syncedCount++;
            } else {
                $statusDb = $localUser ? '🟢 Ada di DB Lokal' : '⚪ Belum ada di DB (JIT)';
            }

            $rows[] = [
                $index + 1,
                $nidn,
                $nama,
                $prodi,
                $statusDb,
            ];
        }

        $this->table(['No', 'NIDN', 'Nama Dosen & Gelar', 'Program Studi', 'Status DB Lokal'], $rows);

        $this->newLine();
        if ($doSync) {
            $this->info("✅ Berhasil menyinkronkan {$syncedCount} dosen ke database lokal (db_auth)!");
            $this->line("Akun dosen dapat langsung login dengan NIDN dan password: <comment>password123</comment>");
        } else {
            $this->comment('💡 TIPS LOGIN DOSEN:');
            $this->line('1. Buka halaman login web di: <info>http://localhost:8000/login</info> atau <info>http://localhost:8001/login</info>');
            $this->line('2. Masukkan salah satu <comment>NIDN</comment> di atas pada kolom NIDN / NIM.');
            $this->line('3. Masukkan password:');
            $this->line('   - Jika dosen baru pertama kali login: masukkan password apa saja (misal: <comment>password123</comment>), sistem otomatis memvalidasi ke API kampus & membuat akun.');
            $this->line('   - Atau jalankan <comment>php artisan campus:list-dosen --sync</comment> untuk membuatkan akun dosen dengan password <comment>password123</comment>.');
        }

        return Command::SUCCESS;
    }
}

