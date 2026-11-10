<?php

namespace Database\Seeders;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Master Kategori Kegiatan
        $kategoriLomba = KategoriKegiatan::firstOrCreate(['nama_kategori' => 'Lomba & Kompetisi']);
        $kategoriBeasiswa = KategoriKegiatan::firstOrCreate(['nama_kategori' => 'Beasiswa']);
        $kategoriSeminar = KategoriKegiatan::firstOrCreate(['nama_kategori' => 'Seminar & Konferensi']);
        $kategoriWorkshop = KategoriKegiatan::firstOrCreate(['nama_kategori' => 'Workshop & Pelatihan']);
        $kategoriOrmawa = KategoriKegiatan::firstOrCreate(['nama_kategori' => 'Organisasi & Kepemimpinan']);

        // 2. Master Data Kegiatan
        $kegiatans = [
            [
                'pembuat_id'        => 3, // Ormawa
                'kategori_id'       => $kategoriLomba->id,
                'judul_kegiatan'    => 'Gemastik 2026 - Kompetisi Pemrograman Mahasiswa Nasional',
                'deskripsi'         => 'Pagelaran mahasiswa nasional bidang TIK dengan kategori pemrograman, data mining, dan keamanan siber.',
                'batas_pendaftaran' => now()->addDays(45)->format('Y-m-d H:i:s'),
                'kuota_maksimal'    => 100,
                'kuota_terisi'      => 0,
                'status_publikasi'  => 'Published',
            ],
            [
                'pembuat_id'        => 2, // Admin
                'kategori_id'       => $kategoriBeasiswa->id,
                'judul_kegiatan'    => 'Beasiswa Prestasi Unggulan Akademik 2026',
                'deskripsi'         => 'Bantuan biaya pendidikan semester genap dan tunjangan riset bagi mahasiswa berprestasi dengan IPK minimal 3.50.',
                'batas_pendaftaran' => now()->addDays(25)->format('Y-m-d H:i:s'),
                'kuota_maksimal'    => 50,
                'kuota_terisi'      => 0,
                'status_publikasi'  => 'Published',
            ],
            [
                'pembuat_id'        => 3, // Ormawa
                'kategori_id'       => $kategoriSeminar->id,
                'judul_kegiatan'    => 'Seminar Nasional AI & Microservices Architecture',
                'deskripsi'         => 'Seminar interaktif bersama praktisi industri teknologi mengenai tren implementasi microservices dan Generative AI.',
                'batas_pendaftaran' => now()->addDays(14)->format('Y-m-d H:i:s'),
                'kuota_maksimal'    => 200,
                'kuota_terisi'      => 0,
                'status_publikasi'  => 'Published',
            ],
            [
                'pembuat_id'        => 3, // Ormawa
                'kategori_id'       => $kategoriWorkshop->id,
                'judul_kegiatan'    => 'Workshop Hands-On Cloud Native & Kubernetes',
                'deskripsi'         => 'Pelatihan intensif instalasi cluster container, CI/CD pipeline, dan deployment aplikasi enterprise.',
                'batas_pendaftaran' => now()->addDays(20)->format('Y-m-d H:i:s'),
                'kuota_maksimal'    => 40,
                'kuota_terisi'      => 0,
                'status_publikasi'  => 'Published',
            ],
            [
                'pembuat_id'        => 3, // Ormawa
                'kategori_id'       => $kategoriOrmawa->id,
                'judul_kegiatan'    => 'Latihan Dasar Kepemimpinan Mahasiswa (LDKM) 2026',
                'deskripsi'         => 'Program pembekalan manajerial dan kepemimpinan bagi calon pengurus lembaga kemahasiswaan kampus.',
                'batas_pendaftaran' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'kuota_maksimal'    => 60,
                'kuota_terisi'      => 0,
                'status_publikasi'  => 'Draft',
            ],
        ];

        foreach ($kegiatans as $k) {
            Kegiatan::updateOrCreate(
                ['judul_kegiatan' => $k['judul_kegiatan']],
                $k
            );
        }
    }
}

