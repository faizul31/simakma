<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test UserSeeder creates the 4 required accounts with correct roles and data.
     */
    public function test_user_seeder_creates_four_dummy_accounts(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 4);

        $this->assertDatabaseHas('users', [
            'nim_nidn' => '2024001001',
            'role'     => User::ROLE_MAHASISWA,
            'prodi'    => 'Teknik Informatika',
        ]);

        $this->assertDatabaseHas('users', [
            'nim_nidn' => 'ADM001',
            'role'     => User::ROLE_ADMIN,
        ]);

        $this->assertDatabaseHas('users', [
            'nim_nidn' => 'ORM202401',
            'role'     => User::ROLE_ORMAWA,
        ]);

        $this->assertDatabaseHas('users', [
            'nim_nidn' => '0412058501',
            'role'     => User::ROLE_DOSEN,
        ]);
    }

    /**
     * Test login validation fails when parameters are missing.
     */
    public function test_login_validation_fails_with_empty_payload(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nim_nidn', 'password']);
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        $this->seed(UserSeeder::class);

        $response = $this->postJson('/api/login', [
            'nim_nidn' => '2024001001',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Kombinasi NIM/NIDN dan password tidak cocok.',
            ]);
    }

    /**
     * Test login succeeds for seeded user and returns Sanctum token + user data.
     */
    public function test_login_succeeds_and_returns_token_and_user_info(): void
    {
        $this->seed(UserSeeder::class);

        $response = $this->postJson('/api/login', [
            'nim_nidn' => '2024001001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token_type',
                    'access_token',
                    'user' => [
                        'id',
                        'nim_nidn',
                        'nama_lengkap',
                        'role',
                        'prodi',
                        'fakultas',
                    ],
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'token_type' => 'Bearer',
                    'user'       => [
                        'nim_nidn' => '2024001001',
                        'role'     => 'mahasiswa',
                    ],
                ],
            ]);
    }

    /**
     * Test /api/me endpoint without token returns 401 unauthorized.
     */
    public function test_me_endpoint_unauthorized_without_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    /**
     * Test /api/me endpoint returns authenticated user data when bearer token is provided.
     */
    public function test_me_endpoint_returns_user_data_with_valid_token(): void
    {
        $this->seed(UserSeeder::class);

        // Perform login
        $loginResponse = $this->postJson('/api/login', [
            'nim_nidn' => '0412058501',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.access_token');

        // Request /api/me with token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'user' => [
                        'nim_nidn'     => '0412058501',
                        'nama_lengkap' => 'Dr. Budi Santoso, M.Kom.',
                        'role'         => 'dosen',
                    ],
                ],
            ]);
    }

    /**
     * Test /api/logout revokes the token.
     */
    public function test_logout_revokes_token(): void
    {
        $this->seed(UserSeeder::class);

        $loginResponse = $this->postJson('/api/login', [
            'nim_nidn' => 'ADM001',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.access_token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Token berhasil dicabut. Logout berhasil.',
            ]);

        // Reset cached auth guard in testing memory
        $this->app['auth']->forgetGuards();

        // Attempt to access /api/me with the revoked token should now fail
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $meResponse->assertStatus(401);
    }
}

