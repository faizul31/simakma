<?php

namespace Database\Seeders;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = KategoriKegiatan::first();

        if (! $kategori) {
            $kategori = KategoriKegiatan::create(['nama_kategori' => 'Seminar Teknologi']);
        }

        Kegiatan::updateOrCreate(
            ['judul_kegiatan' => 'Seminar Nasional AI & Microservices 2026'],
            [
                'pembuat_id'        => 3, // BEM Fasilkom (Ormawa)
                'kategori_id'       => $kategori->id,
                'deskripsi'         => 'Seminar nasional implementasi microservices pada ekosistem kampus modern.',
                'batas_pendaftaran' => now()->addDays(14),
                'kuota_maksimal'    => 100,
                'kuota_terisi'      => 0,
                'status_publikasi'  => Kegiatan::STATUS_PUBLISHED,
            ]
        );

        Kegiatan::updateOrCreate(
            ['judul_kegiatan' => 'Workshop DevOps & Kubernetes'],
            [
                'pembuat_id'        => 2, // Admin
                'kategori_id'       => $kategori->id,
                'deskripsi'         => 'Workshop hands-on container orchestration.',
                'batas_pendaftaran' => now()->addDays(7),
                'kuota_maksimal'    => 50,
                'kuota_terisi'      => 0,
                'status_publikasi'  => Kegiatan::STATUS_PUBLISHED,
            ]
        );

        Kegiatan::updateOrCreate(
            ['judul_kegiatan' => 'Lomba UI/UX Design Kampus (Draft)'],
            [
                'pembuat_id'        => 3,
                'kategori_id'       => $kategori->id,
                'deskripsi'         => 'Kompetisi desain aplikasi mobile mahasiswa.',
                'batas_pendaftaran' => now()->addDays(30),
                'kuota_maksimal'    => 30,
                'kuota_terisi'      => 0,
                'status_publikasi'  => Kegiatan::STATUS_DRAFT,
            ]
        );
    }
}

