<?php

namespace Tests\Feature;

use App\Services\CampusApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CampusApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->baseUrl = rtrim(config('services.campus.base_url', 'https://sso.unuja.ac.id/portal/data'), '/');
    }

    public function test_campus_api_authorize_and_fetches_dosen_reference_data(): void
    {
        $mockToken = 'a1b2c3d4e5f60718293a4b5c6d7e8f90'; // 32 chars random token

        // Mock HTTP calls to Campus API
        Http::fake([
            $this->baseUrl . '/authorize' => Http::response([
                'success' => true,
                'data'    => [
                    'info' => [
                        'waktu_berlaku' => '2026-11-08 03:00:00',
                        'urls'          => [
                            'data' => $this->baseUrl . '/data/' . $mockToken,
                        ],
                    ],
                ],
            ], 200),
            $this->baseUrl . '/data/' . $mockToken => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'id_penduduk'   => 'aaaa101',
                        'nama_penduduk' => 'M. Hatta',
                        'gelar_depan'   => 'Prof. Dr.',
                        'gelar_belakang'=> 'M.Eng',
                        'nidn'          => '0412345601',
                        'prodi'         => 'Teknik Informatika',
                    ],
                    [
                        'id_penduduk'   => 'aaaa102',
                        'nama_penduduk' => 'Siti Rahmawati',
                        'gelar_depan'   => 'Dr.',
                        'gelar_belakang'=> 'M.Cs',
                        'nidn'          => '0423456702',
                        'prodi'         => 'Sistem Informasi',
                    ],
                ],
            ], 200),
        ]);

        $service = app(CampusApiService::class);

        // 1. Tes Authorize Token
        $token = $service->getAccessToken(true);
        $this->assertEquals($mockToken, $token);

        // 2. Tes Ambil Data Dosen
        $dosenList = $service->getDaftarDosen();
        $this->assertCount(2, $dosenList);
        $this->assertStringContainsString('M. Hatta', $dosenList[0]['nama_lengkap']);
        $this->assertEquals('0412345601', $dosenList[0]['nim_nidn']);
        $this->assertEquals('Teknik Informatika', $dosenList[0]['prodi']);
    }

    public function test_campus_api_falls_back_gracefully_when_server_is_offline(): void
    {
        // Simulasi error koneksi / timeout ke server kampus
        Http::fake([
            $this->baseUrl . '/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            },
            '*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            },
        ]);

        $service = app(CampusApiService::class);
        $service->clearTokenCache();

        // Harus mengembalikan fallback lokal tanpa memicu unhandled exception
        $dosenList = $service->getDaftarDosen();

        $this->assertNotEmpty($dosenList);
        $this->assertEquals('Dr. Budi Santoso, M.Kom', $dosenList[0]['nama_lengkap']);
    }

    public function test_artisan_campus_test_command_runs_successfully(): void
    {
        $mockToken = 'x1y2z3a4b5c6d7e8f901234567890123';

        Http::fake([
            $this->baseUrl . '/authorize' => Http::response([
                'success' => true,
                'data'    => [
                    'info' => [
                        'urls' => [
                            'data' => $this->baseUrl . '/data/' . $mockToken,
                        ],
                    ],
                ],
            ], 200),
            $this->baseUrl . '/data/' . $mockToken => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'id_penduduk'   => 'aaaa001',
                        'nama_penduduk' => 'Dosen Penguji Kampus',
                        'nidn'          => '0011223344',
                        'prodi'         => 'Informatika',
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('campus:test', ['--refresh' => true])
            ->expectsOutputToContain('UJI KONEKSI API KAMPUS')
            ->expectsOutputToContain('Mendapatkan Access Token')
            ->assertExitCode(0);
    }

    public function test_artisan_campus_list_dosen_command_runs_and_syncs(): void
    {
        $mockToken = 'dosen123456789012345678901234567';

        Http::fake([
            $this->baseUrl . '/authorize' => Http::response([
                'success' => true,
                'data'    => [
                    'info' => [
                        'urls' => [
                            'data' => $this->baseUrl . '/data/' . $mockToken,
                        ],
                    ],
                ],
            ], 200),
            $this->baseUrl . '/data/' . $mockToken => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'id_penduduk'   => 'aaaa999',
                        'nama_penduduk' => 'Ir. Bambang Trihatmojo',
                        'gelar_belakang'=> 'M.T.',
                        'nidn'          => '0701018001',
                        'prodi'         => 'Teknik Sipil',
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('campus:list-dosen', ['--limit' => 5, '--sync' => true])
            ->expectsOutputToContain('DAFTAR DOSEN & NIDN DARI API KAMPUS UNUJA')
            ->expectsOutputToContain('0701018001')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'nim_nidn'     => '0701018001',
            'nama_lengkap' => 'Ir. Bambang Trihatmojo, M.T.',
            'role'         => 'dosen',
        ]);
    }

    public function test_get_daftar_dosen_can_filter_and_group_by_prodi(): void
    {
        $mockToken = 'grp12345678901234567890123456789';

        Http::fake([
            $this->baseUrl . '/authorize' => Http::response([
                'success' => true,
                'data'    => [
                    'info' => [
                        'urls' => [
                            'data' => $this->baseUrl . '/data/' . $mockToken,
                        ],
                    ],
                ],
            ], 200),
            $this->baseUrl . '/data/' . $mockToken => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'id'            => 101,
                        'nama_penduduk' => 'Dosen Elektro Satu',
                        'nidn'          => '0711111101',
                        'prodi'         => 'Teknik Elektro',
                    ],
                    [
                        'id'            => 102,
                        'nama_penduduk' => 'Dosen Informatika Satu',
                        'nidn'          => '0722222202',
                        'prodi'         => 'Teknik Informatika',
                    ],
                    [
                        'id'            => 103,
                        'nama_penduduk' => 'Dosen Elektro Dua',
                        'nidn'          => '0733333303',
                        'prodi'         => 'Teknik Elektro',
                    ],
                ],
            ], 200),
        ]);

        $service = app(CampusApiService::class);

        // 1. Tes filter langsung dengan parameter $prodi
        $dosenElektro = $service->getDaftarDosen(null, 'Teknik Elektro');
        $this->assertCount(2, $dosenElektro);
        $this->assertEquals('Dosen Elektro Satu', $dosenElektro[0]['nama_lengkap']);
        $this->assertEquals('Dosen Elektro Dua', $dosenElektro[1]['nama_lengkap']);

        // 2. Tes getDaftarDosenGrouped untuk mahasiswa Teknik Informatika
        $grouped = $service->getDaftarDosenGrouped('Teknik Informatika');
        $this->assertCount(1, $grouped['sejurusan']);
        $this->assertCount(2, $grouped['lainnya']);
        $this->assertEquals('Dosen Informatika Satu', $grouped['sejurusan'][0]['nama_lengkap']);

        // 3. Toleransi fuzzy matching ("Informatika" cocok dengan "Teknik Informatika")
        $groupedFuzzy = $service->getDaftarDosenGrouped('Informatika');
        $this->assertCount(1, $groupedFuzzy['sejurusan']);
        $this->assertEquals('Dosen Informatika Satu', $groupedFuzzy['sejurusan'][0]['nama_lengkap']);
    }

    public function test_dashboard_mahasiswa_renders_prodi_grouped_dosen_options(): void
    {
        $mockToken = 'dash1234567890123456789012345678';

        Http::fake([
            $this->baseUrl . '/authorize' => Http::response([
                'success' => true,
                'data'    => [
                    'info' => [
                        'urls' => [
                            'data' => $this->baseUrl . '/data/' . $mockToken,
                        ],
                    ],
                ],
            ], 200),
            $this->baseUrl . '/data/' . $mockToken => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'id'            => 201,
                        'nama_penduduk' => 'Dosen Informatika Penguji',
                        'nidn'          => '0799999901',
                        'prodi'         => 'Teknik Informatika',
                    ],
                ],
            ], 200),
            'http://localhost:8002/*' => Http::response(['data' => []], 200),
            'http://localhost:8003/*' => Http::response(['data' => []], 200),
            'http://localhost:8004/*' => Http::response(['data' => []], 200),
        ]);

        $response = $this->withSession([
            'token' => 'dummy_token',
            'user'  => [
                'id'           => 1,
                'nama_lengkap' => 'Mahasiswa Test',
                'nim_nidn'     => '2024001001',
                'role'         => 'mahasiswa',
                'prodi'        => 'Teknik Informatika',
            ],
        ])->get(route('web.mahasiswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('⭐ Dosen Jurusan Anda (Teknik Informatika)');
        $response->assertSee('Hanya Sejurusan');
        $response->assertSee('Dosen Informatika Penguji');
        $response->assertSee('NIP / NIDN Dosen');
        $response->assertSee('display_nip_dosen');
    }
}
