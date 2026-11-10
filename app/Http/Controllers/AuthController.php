<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle user login request.
     *
     * Flow arsitektur:
     * 1. Validasi input request (nim_nidn & password).
     * 2. Verifikasi kredensial (saat ini autentikasi lokal, disiapkan modular
     *    agar mudah dialihkan ke API Kampus / SSO / LDAP di masa mendatang).
     * 3. Issue token Laravel Sanctum dengan role-based ability.
     * 4. Return token dan user payload.
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'nim_nidn' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nim_nidn.required' => 'NIM / NIDN wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $nimNidn = $request->input('nim_nidn');
        $password = $request->input('password');

        // 2. Verifikasi kredensial
        // Catatan: Jika migrasi ke API Kampus, ubah implementasi method authenticateViaCredentials()
        $user = $this->authenticateViaCredentials($nimNidn, $password);

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kombinasi NIM/NIDN dan password tidak cocok.',
            ], 401);
        }

        // 3. Generate Personal Access Token via Sanctum
        // Sertakan ability sesuai role untuk otorisasi downstream di Microservices
        $tokenName = 'auth_token_' . $user->nim_nidn;
        $token = $user->createToken($tokenName, ['role:' . $user->role])->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Autentikasi berhasil.',
            'data'    => [
                'token_type'   => 'Bearer',
                'access_token' => $token,
                'user'         => [
                    'id'           => $user->id,
                    'nim_nidn'     => $user->nim_nidn,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role'         => $user->role,
                    'prodi'        => $user->prodi,
                    'fakultas'     => $user->fakultas,
                ],
            ],
        ], 200);
    }

    /**
     * Get current authenticated user profile based on Sanctum Bearer Token.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil pengguna berhasil diambil.',
            'data'    => [
                'user' => [
                    'id'           => $user->id,
                    'nim_nidn'     => $user->nim_nidn,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role'         => $user->role,
                    'prodi'        => $user->prodi,
                    'fakultas'     => $user->fakultas,
                    'created_at'   => $user->created_at,
                    'updated_at'   => $user->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Revoke current token (Logout).
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus token yang sedang aktif digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Token berhasil dicabut. Logout berhasil.',
        ], 200);
    }

    /**
     * Modular Authentication Handler:
     * 1. Cek user di tabel lokal `users` (db_auth). Jika ada, verifikasi hash password.
     * 2. Jika user belum ada di database lokal, lakukan Just-In-Time (JIT) Provisioning:
     *    - Cek ke API Kampus UNUJA (mahasiswa & dosen).
     *    - Jika data terdaftar resmi di kampus, buatkan akun lokal secara otomatis
     *      dengan password yang dimasukkan pengguna saat login pertama kali.
     */
    protected function authenticateViaCredentials(string $nimNidn, string $password): ?User
    {
        // 1. Cari user berdasarkan NIM / NIDN di database lokal
        $user = User::where('nim_nidn', $nimNidn)->first();

        if ($user) {
            // Verifikasi kecocokan hash password
            if (Hash::check($password, $user->password)) {
                return $user;
            }
            return null;
        }

        // 2. Just-In-Time (JIT) Provisioning dari API Kampus
        try {
            $campusService = app(\App\Services\CampusApiService::class);

            // A. Cek apakah NIM terdaftar sebagai Mahasiswa resmi kampus
            $mhsData = $campusService->getMahasiswaByNim($nimNidn);
            if ($mhsData) {
                return User::create([
                    'nama_lengkap' => $mhsData['nama_lengkap'],
                    'nim_nidn'     => (string) $mhsData['nim_nidn'],
                    'role'         => 'mahasiswa',
                    'prodi'        => $mhsData['prodi'] ?? 'Teknik',
                    'fakultas'     => $mhsData['fakultas'] ?? 'Fakultas Teknik',
                    'password'     => Hash::make($password),
                ]);
            }

            // B. Cek apakah NIDN terdaftar sebagai Dosen resmi kampus
            $daftarDosen = $campusService->getDaftarDosen($nimNidn);
            foreach ($daftarDosen as $dsn) {
                if ((string) ($dsn['nim_nidn'] ?? '') === (string) $nimNidn) {
                    return User::create([
                        'nama_lengkap' => $dsn['nama_lengkap'],
                        'nim_nidn'     => (string) $dsn['nim_nidn'],
                        'role'         => 'dosen',
                        'prodi'        => $dsn['prodi'] ?? 'Teknik',
                        'fakultas'     => 'Fakultas Teknik',
                        'password'     => Hash::make($password),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('JIT Campus API Provisioning Error: ' . $e->getMessage());
        }

        return null;
    }
}

