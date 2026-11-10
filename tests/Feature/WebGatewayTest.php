<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebGatewayTest extends TestCase
{
    use RefreshDatabase;
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Portal Microservices Kemahasiswaan');
        $response->assertSee('NIM / NIDN');
    }

    public function test_protected_dashboard_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/mahasiswa/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_process_login_authenticates_via_auth_service_and_redirects_to_mahasiswa_dashboard(): void
    {
        Http::fake([
            'http://localhost:8001/api/login' => Http::response([
                'status'  => 'success',
                'message' => 'Login berhasil.',
                'data'    => [
                    'token' => 'mock-sanctum-token-12345',
                    'user'  => [
                        'id'           => 1,
                        'nim_nidn'     => '2024001001',
                        'nama_lengkap' => 'Ahmad Fauzi',
                        'role'         => 'mahasiswa',
                        'prodi'        => 'Teknik Informatika',
                        'fakultas'     => 'Fakultas Ilmu Komputer',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->post('/login', [
            'nim_nidn' => '2024001001',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/mahasiswa/dashboard');
        $this->assertEquals('mock-sanctum-token-12345', session('token'));
        $this->assertEquals('Ahmad Fauzi', session('user.nama_lengkap'));
    }

    public function test_process_login_handles_invalid_credentials_gracefully(): void
    {
        Http::fake([
            'http://localhost:8001/api/login' => Http::response([
                'status'  => 'error',
                'message' => 'Kombinasi NIM/NIDN dan password salah.',
            ], 401),
        ]);

        $response = $this->from('/login')->post('/login', [
            'nim_nidn' => '2024001001',
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['nim_nidn']);
    }

    public function test_mahasiswa_dashboard_fetches_kegiatan_and_pendaftaran(): void
    {
        Http::fake([
            'http://localhost:8002/api/kegiatan/published' => Http::response([
                'status' => 'success',
                'data'   => [
                    [
                        'id'                => 10,
                        'judul_kegiatan'    => 'Lomba Karya Ilmiah Nasional',
                        'deskripsi'         => 'Lomba karya ilmiah tingkat nasional mahasiswa.',
                        'batas_pendaftaran' => '2026-12-31',
                        'kuota_maksimal'    => 50,
                        'kuota_terisi'      => 12,
                        'status_publikasi'  => 'Published',
                    ],
                ],
            ], 200),
            'http://localhost:8003/api/pendaftaran/mahasiswa/1' => Http::response([
                'status' => 'success',
                'data'   => [
                    [
                        'id'                       => 101,
                        'kegiatan_id'              => 10,
                        'mahasiswa_id'             => 1,
                        'dosen_wali_id'            => 4,
                        'status_rekomendasi_dosen' => 'Direkomendasikan',
                        'status_pendaftaran'       => 'Diterima',
                        'created_at'               => '2026-11-07 10:00:00',
                    ],
                ],
            ], 200),
            'http://localhost:8004/api/prestasi/mahasiswa/1' => Http::response([
                'status' => 'success',
                'data'   => [],
            ], 200),
        ]);

        $response = $this->withSession([
            'token' => 'mock-token-xyz',
            'user'  => [
                'id'           => 1,
                'nim_nidn'     => '2024001001',
                'nama_lengkap' => 'Ahmad Fauzi',
                'role'         => 'mahasiswa',
                'prodi'        => 'Teknik Informatika',
                'fakultas'     => 'Fakultas Ilmu Komputer',
            ],
        ])->get('/mahasiswa/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('Lomba Karya Ilmiah Nasional');
        $response->assertSee('Kegiatan #10');
        $response->assertSee('Direkomendasikan');
        $response->assertSee('Diterima');
    }

    public function test_process_login_falls_back_gracefully_when_auth_service_is_offline(): void
    {
        $this->seed(UserSeeder::class);

        // Simulasi Port 8001 offline / connection refused
        Http::fake([
            'http://localhost:8001/api/login' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('cURL error 7: Failed to connect to localhost port 8001');
            },
        ]);

        $response = $this->post('/login', [
            'nim_nidn' => '2024001001',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/mahasiswa/dashboard');
        $this->assertNotEmpty(session('token'));
        $this->assertEquals('Ahmad Fauzi', session('user.nama_lengkap'));
        $this->assertTrue(session('is_fallback'));
    }

    public function test_admin_dashboard_renders_with_fallback(): void
    {
        $response = $this->withSession([
            'token' => 'sample-token',
            'user'  => [
                'id'           => 2,
                'nim_nidn'     => 'ADM001',
                'nama_lengkap' => 'Bambang Sudarsono, S.Kom',
                'role'         => 'admin',
                'prodi'        => 'Bagian Kemahasiswaan',
                'fakultas'     => 'Rektorat',
            ],
        ])->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Portal Kemahasiswaan');
        $response->assertSee('Bambang Sudarsono, S.Kom');
    }

    public function test_logout_clears_session_and_redirects_to_login(): void
    {
        Http::fake([
            'http://localhost:8001/api/logout' => Http::response([
                'status'  => 'success',
                'message' => 'Token berhasil dicabut.',
            ], 200),
        ]);

        $response = $this->withSession([
            'token' => 'active-token',
            'user'  => ['id' => 1, 'role' => 'mahasiswa'],
        ])->post('/logout');

        $response->assertRedirect('/login');
        $this->assertNull(session('token'));
        $this->assertNull(session('user'));
    }

    public function test_mahasiswa_can_ajukan_prestasi_via_web_with_campus_dosen_nidn(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->create('sertifikat.pdf', 250, 'application/pdf');

        $response = $this->withSession([
            'token' => 'active-token',
            'user'  => [
                'id'           => 1,
                'nim_nidn'     => '2024001001',
                'nama_lengkap' => 'Mahasiswa Test',
                'role'         => 'mahasiswa',
                'prodi'        => 'Teknik Informatika',
            ],
        ])->post('/mahasiswa/prestasi', [
            'judul_prestasi'      => 'Juara 1 Hackathon Nasional',
            'tingkat'             => 'Nasional',
            'dosen_pembimbing_id' => '0728069505', // NIDN string from campus API
            'file_sertifikat'     => $file,
        ]);

        $response->assertSessionHas('success', 'Klaim sertifikat prestasi berhasil diajukan untuk SKPI!');
        $this->assertDatabaseHas('prestasis', [
            'judul_prestasi' => 'Juara 1 Hackathon Nasional',
            'tingkat'        => 'Nasional',
        ]);
    }

    public function test_ormawa_cannot_verify_pendaftaran_or_skpi(): void
    {
        // 1. Ormawa mencoba verifikasi pendaftaran
        $respPendaftaran = $this->withSession([
            'token' => 'active-token',
            'user'  => [
                'id'           => 3,
                'nim_nidn'     => 'ORM001',
                'nama_lengkap' => 'Pengurus BEM',
                'role'         => 'ormawa',
            ],
        ])->put('/admin/pendaftaran/1/verifikasi', [
            'status_pendaftaran' => 'Diterima',
        ]);

        $respPendaftaran->assertSessionHas('error', 'Akses ditolak. Hanya Admin Kemahasiswaan yang berhak melakukan verifikasi pendaftaran.');

        // 2. Ormawa mencoba validasi prestasi SKPI
        $respPrestasi = $this->withSession([
            'token' => 'active-token',
            'user'  => [
                'id'           => 3,
                'nim_nidn'     => 'ORM001',
                'nama_lengkap' => 'Pengurus BEM',
                'role'         => 'ormawa',
            ],
        ])->put('/admin/prestasi/1/validasi', [
            'status_validasi_akhir' => 'Disetujui',
        ]);

        $respPrestasi->assertSessionHas('error', 'Akses ditolak. Hanya Admin Kemahasiswaan yang berhak memvalidasi klaim prestasi SKPI.');
    }
}

