<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CampusStudentLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_login_with_campus_api_data_via_jit_provisioning(): void
    {
        $mockToken = 'mock_access_token_32_characters_123';
        $baseUrl = rtrim(config('services.campus.base_url', 'https://sso.unuja.ac.id/portal/data'), '/');

        // Mock Campus API responses for student '1921300014'
        Http::fake([
            $baseUrl . '/authorize' => Http::response([
                'success' => true,
                'data'    => [
                    'info' => [
                        'urls' => [
                            'data' => $baseUrl . '/data/' . $mockToken,
                        ],
                    ],
                ],
            ], 200),
            $baseUrl . '/data/' . $mockToken => Http::response([
                'success' => true,
                'data'    => [
                    'data' => [
                        [
                            'nim'      => '1921300014',
                            'nama'     => 'MOH NURUL YAQIN',
                            'fakultas' => 'Teknik',
                            'prodi'    => 'Teknik Elektro',
                            'wali'     => 'FUAD HASAN',
                            'nidn'     => '0706119303',
                        ],
                    ],
                ],
            ], 200),
            'http://localhost:8001/api/login' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Use in-process fallback');
            },
        ]);

        // Pastikan user belum ada di database lokal
        $this->assertDatabaseMissing('users', ['nim_nidn' => '1921300014']);

        // Mahasiswa mencoba login pertama kali dengan NIM kampusnya
        $response = $this->post('/login', [
            'nim_nidn' => '1921300014',
            'password' => 'mypassword123',
        ]);

        // Harusnya berhasil login dan diarahkan ke dashboard mahasiswa
        $response->assertRedirect('/mahasiswa/dashboard');

        // Dan akun otomatis terbuat di database dengan data resmi kampus
        $this->assertDatabaseHas('users', [
            'nim_nidn'     => '1921300014',
            'nama_lengkap' => 'MOH NURUL YAQIN',
            'role'         => 'mahasiswa',
            'prodi'        => 'Teknik Elektro',
            'fakultas'     => 'Teknik',
        ]);

        // Cek isi session
        $this->assertEquals('MOH NURUL YAQIN', session('user.nama_lengkap'));
        $this->assertEquals('Teknik Elektro', session('user.prodi'));
    }
}

