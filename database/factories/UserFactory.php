<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nim_nidn' => fake()->unique()->numerify('##########'),
            'nama_lengkap' => fake()->name(),
            'role' => fake()->randomElement([
                User::ROLE_MAHASISWA,
                User::ROLE_ADMIN,
                User::ROLE_ORMAWA,
                User::ROLE_DOSEN,
            ]),
            'prodi' => 'Teknik Informatika',
            'fakultas' => 'Ilmu Komputer',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
