<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        $users = [
            [
                'nim_nidn' => '2024001001',
                'nama_lengkap' => 'Ahmad Fauzi',
                'role' => User::ROLE_MAHASISWA,
                'prodi' => 'Teknik Informatika',
                'fakultas' => 'Ilmu Komputer',
                'password' => $defaultPassword,
            ],
            [
                'nim_nidn' => 'ADM001',
                'nama_lengkap' => 'Siti Rahmawati',
                'role' => User::ROLE_ADMIN,
                'prodi' => 'Biro Administrasi Akademik',
                'fakultas' => 'Rektorat',
                'password' => $defaultPassword,
            ],
            [
                'nim_nidn' => 'ORM202401',
                'nama_lengkap' => 'BEM Fakultas Ilmu Komputer',
                'role' => User::ROLE_ORMAWA,
                'prodi' => 'Teknik Informatika',
                'fakultas' => 'Ilmu Komputer',
                'password' => $defaultPassword,
            ],
            [
                'nim_nidn' => '0412058501',
                'nama_lengkap' => 'Dr. Budi Santoso, M.Kom.',
                'role' => User::ROLE_DOSEN,
                'prodi' => 'Teknik Informatika',
                'fakultas' => 'Ilmu Komputer',
                'password' => $defaultPassword,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['nim_nidn' => $userData['nim_nidn']],
                $userData
            );
        }
    }
}

