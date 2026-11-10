<?php

namespace Database\Seeders;

use App\Models\KategoriKegiatan;
use Illuminate\Database\Seeder;

class KategoriKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriList = [
            'Seminar Nasional',
            'Workshop & Bootcamp',
            'Kompetisi & Lomba',
            'Sosial & Pengabdian Masyarakat',
            'Organisasi & Kemahasiswaan',
        ];

        foreach ($kategoriList as $nama) {
            KategoriKegiatan::firstOrCreate(['nama_kategori' => $nama]);
        }
    }
}

