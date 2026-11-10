<?php

namespace Tests\Feature;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanApiTest extends TestCase
{
    use RefreshDatabase;

    private KategoriKegiatan $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = KategoriKegiatan::create([
            'nama_kategori' => 'Seminar Teknologi',
        ]);
    }

    /**
     * Test create new kegiatan via POST /api/kegiatan.
     */
    public function test_can_create_kegiatan(): void
    {
        $payload = [
            'pembuat_id'        => 1, // ID dari Auth Service (db_auth)
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Seminar Nasional AI & Cloud 2026',
            'deskripsi'         => 'Seminar mengenai penerapan AI pada arsitektur microservices.',
            'batas_pendaftaran' => now()->addDays(14)->toDateTimeString(),
            'kuota_maksimal'    => 100,
            'status_publikasi'  => 'Published',
        ];

        $response = $this->postJson('/api/kegiatan', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Kegiatan baru berhasil dibuat.',
                'data'    => [
                    'judul_kegiatan'   => 'Seminar Nasional AI & Cloud 2026',
                    'kuota_maksimal'   => 100,
                    'kuota_terisi'     => 0,
                    'status_publikasi' => 'Published',
                ],
            ]);

        $this->assertDatabaseHas('kegiatans', [
            'judul_kegiatan' => 'Seminar Nasional AI & Cloud 2026',
        ]);
    }

    /**
     * Test Mahasiswa can view ONLY published kegiatan via GET /api/kegiatan.
     */
    public function test_mahasiswa_can_view_only_published_kegiatan(): void
    {
        // Draft
        Kegiatan::create([
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Kegiatan Masih Draft',
            'deskripsi'         => 'Deskripsi draft',
            'batas_pendaftaran' => now()->addDays(7),
            'kuota_maksimal'    => 50,
            'kuota_terisi'      => 0,
            'status_publikasi'  => 'Draft',
        ]);

        // Published
        Kegiatan::create([
            'pembuat_id'        => 2,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Workshop Kubernetes',
            'deskripsi'         => 'Deskripsi published',
            'batas_pendaftaran' => now()->addDays(10),
            'kuota_maksimal'    => 30,
            'kuota_terisi'      => 5,
            'status_publikasi'  => 'Published',
        ]);

        $response = $this->getJson('/api/kegiatan');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'total'  => 1,
            ])
            ->assertJsonFragment([
                'judul_kegiatan' => 'Workshop Kubernetes',
            ])
            ->assertJsonMissing([
                'judul_kegiatan' => 'Kegiatan Masih Draft',
            ]);
    }

    /**
     * Test Admin/Ormawa can view ALL kegiatan (including Draft) via GET /api/kegiatan/semua.
     */
    public function test_admin_can_view_all_kegiatan_including_draft(): void
    {
        Kegiatan::create([
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Kegiatan Draft',
            'deskripsi'         => 'Deskripsi draft',
            'batas_pendaftaran' => now()->addDays(7),
            'kuota_maksimal'    => 50,
            'kuota_terisi'      => 0,
            'status_publikasi'  => 'Draft',
        ]);

        Kegiatan::create([
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Kegiatan Published',
            'deskripsi'         => 'Deskripsi published',
            'batas_pendaftaran' => now()->addDays(10),
            'kuota_maksimal'    => 30,
            'kuota_terisi'      => 5,
            'status_publikasi'  => 'Published',
        ]);

        $response = $this->getJson('/api/kegiatan/semua');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'total'  => 2,
            ]);
    }

    /**
     * Test microservices endpoint GET /api/kegiatan/{id}/cek-kuota when quota available & date valid.
     */
    public function test_cek_kuota_returns_200_when_available(): void
    {
        $kegiatan = Kegiatan::create([
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Bootcamp Microservices',
            'deskripsi'         => 'Belajar microservices',
            'batas_pendaftaran' => now()->addDays(5),
            'kuota_maksimal'    => 50,
            'kuota_terisi'      => 20, // 20 < 50 => Tersedia
            'status_publikasi'  => 'Published',
        ]);

        $response = $this->getJson("/api/kegiatan/{$kegiatan->id}/cek-kuota");

        $response->assertStatus(200)
            ->assertJson([
                'tersedia' => true,
                'kegiatan' => [
                    'id'             => $kegiatan->id,
                    'judul_kegiatan' => 'Bootcamp Microservices',
                ],
            ]);
    }

    /**
     * Test microservices endpoint GET /api/kegiatan/{id}/cek-kuota when quota is full.
     */
    public function test_cek_kuota_returns_400_when_quota_is_full(): void
    {
        $kegiatan = Kegiatan::create([
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Lomba UI/UX Design',
            'deskripsi'         => 'Kompetisi desain',
            'batas_pendaftaran' => now()->addDays(3),
            'kuota_maksimal'    => 20,
            'kuota_terisi'      => 20, // Penuh
            'status_publikasi'  => 'Published',
        ]);

        $response = $this->getJson("/api/kegiatan/{$kegiatan->id}/cek-kuota");

        $response->assertStatus(400)
            ->assertJson([
                'tersedia' => false,
                'pesan'    => 'Kuota penuh atau pendaftaran ditutup',
            ]);
    }

    /**
     * Test microservices endpoint GET /api/kegiatan/{id}/cek-kuota when deadline passed.
     */
    public function test_cek_kuota_returns_400_when_deadline_passed(): void
    {
        $kegiatan = Kegiatan::create([
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Webinar Masa Lalu',
            'deskripsi'         => 'Sudah lewat',
            'batas_pendaftaran' => now()->subDay(), // Kemarin
            'kuota_maksimal'    => 50,
            'kuota_terisi'      => 10,
            'status_publikasi'  => 'Published',
        ]);

        $response = $this->getJson("/api/kegiatan/{$kegiatan->id}/cek-kuota");

        $response->assertStatus(400)
            ->assertJson([
                'tersedia' => false,
                'pesan'    => 'Kuota penuh atau pendaftaran ditutup',
            ]);
    }

    /**
     * Test POST /api/kegiatan/{id}/tambah-kuota-terisi increments kuota_terisi by 1.
     */
    public function test_tambah_kuota_terisi_increments_value(): void
    {
        $kegiatan = Kegiatan::create([
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Seminar Bisnis',
            'deskripsi'         => 'Seminar bisnis',
            'batas_pendaftaran' => now()->addDays(5),
            'kuota_maksimal'    => 10,
            'kuota_terisi'      => 2,
            'status_publikasi'  => 'Published',
        ]);

        $response = $this->postJson("/api/kegiatan/{$kegiatan->id}/tambah-kuota-terisi");

        $response->assertStatus(200)
            ->assertJson([
                'status'       => 'success',
                'kuota_terisi' => 3,
                'sisa_kuota'   => 7,
            ]);

        $this->assertEquals(3, $kegiatan->fresh()->kuota_terisi);
    }

    /**
     * Test create new kegiatan with custom persyaratan by admin.
     */
    public function test_can_create_and_update_kegiatan_with_persyaratan(): void
    {
        $payload = [
            'pembuat_id'        => 1,
            'kategori_id'       => $this->kategori->id,
            'judul_kegiatan'    => 'Lomba Karya Tulis Ilmiah 2026',
            'deskripsi'         => 'Lomba karya tulis mahasiswa tingkat nasional.',
            'persyaratan'       => "1. Mahasiswa aktif semester 3-7\n2. Melampirkan KTM & KRS berjalan\n3. Proposal format PDF",
            'batas_pendaftaran' => now()->addDays(20)->toDateTimeString(),
            'kuota_maksimal'    => 50,
            'status_publikasi'  => 'Published',
        ];

        $createResponse = $this->postJson('/api/kegiatan', $payload);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.persyaratan', "1. Mahasiswa aktif semester 3-7\n2. Melampirkan KTM & KRS berjalan\n3. Proposal format PDF");

        $kegiatanId = $createResponse->json('data.id');

        $this->assertDatabaseHas('kegiatans', [
            'id'          => $kegiatanId,
            'persyaratan' => "1. Mahasiswa aktif semester 3-7\n2. Melampirkan KTM & KRS berjalan\n3. Proposal format PDF",
        ]);

        // Test update persyaratan
        $updateResponse = $this->putJson("/api/kegiatan/{$kegiatanId}", [
            'persyaratan' => 'Persyaratan diperbarui: KTM dan surat rekomendasi kaprodi.',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.persyaratan', 'Persyaratan diperbarui: KTM dan surat rekomendasi kaprodi.');

        $this->assertEquals(
            'Persyaratan diperbarui: KTM dan surat rekomendasi kaprodi.',
            Kegiatan::find($kegiatanId)->persyaratan
        );
    }
}
