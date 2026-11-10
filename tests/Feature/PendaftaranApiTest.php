<?php

namespace Tests\Feature;

use App\Models\Pendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendaftaranApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test POST /api/daftar succeeds when Kegiatan Service indicates quota is available.
     */
    public function test_daftar_succeeds_when_kegiatan_service_quota_available(): void
    {
        Storage::fake('public');

        Http::fake([
            'http://localhost:8002/api/kegiatan/1/cek-kuota' => Http::response([
                'tersedia' => true,
                'kegiatan' => [
                    'id'             => 1,
                    'judul_kegiatan' => 'Seminar Nasional Microservices 2026',
                    'kuota_maksimal' => 100,
                    'kuota_terisi'   => 10,
                ],
            ], 200),
            'http://localhost:8002/api/kegiatan/1/tambah-kuota-terisi' => Http::response([
                'status'  => 'success',
                'message' => 'Kuota terisi berhasil ditambah.',
            ], 200),
        ]);

        $file = UploadedFile::fake()->create('syarat.pdf', 200, 'application/pdf');

        $payload = [
            'kegiatan_id'   => 1,
            'mahasiswa_id'  => 10,
            'dosen_wali_id' => 4,
            'file_syarat'   => $file,
        ];

        $response = $this->postJson('/api/daftar', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Pendaftaran berhasil dilakukan.',
                'data'    => [
                    'kegiatan_id'              => 1,
                    'mahasiswa_id'             => 10,
                    'dosen_wali_id'            => 4,
                    'status_rekomendasi_dosen' => 'Menunggu',
                    'status_pendaftaran'       => 'Menunggu',
                ],
            ]);

        $this->assertDatabaseHas('pendaftarans', [
            'kegiatan_id'  => 1,
            'mahasiswa_id' => 10,
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8002/api/kegiatan/1/cek-kuota');
        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8002/api/kegiatan/1/tambah-kuota-terisi');
    }

    /**
     * Test POST /api/daftar rejected when Kegiatan Service indicates quota is full / expired.
     */
    public function test_daftar_fails_when_quota_full_or_closed(): void
    {
        Http::fake([
            'http://localhost:8002/api/kegiatan/2/cek-kuota' => Http::response([
                'tersedia' => false,
                'pesan'    => 'Kuota penuh atau pendaftaran ditutup',
            ], 400),
        ]);

        $payload = [
            'kegiatan_id'  => 2,
            'mahasiswa_id' => 10,
        ];

        $response = $this->postJson('/api/daftar', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Pendaftaran gagal, kuota penuh atau batas waktu habis',
            ]);

        $this->assertDatabaseMissing('pendaftarans', [
            'kegiatan_id'  => 2,
            'mahasiswa_id' => 10,
        ]);
    }

    /**
     * Test Dosen can update recommendation status via PUT /api/pendaftaran/{id}/rekomendasi.
     */
    public function test_dosen_can_update_rekomendasi(): void
    {
        $pendaftaran = Pendaftaran::create([
            'kegiatan_id'              => 1,
            'mahasiswa_id'             => 10,
            'dosen_wali_id'            => 4,
            'status_rekomendasi_dosen' => 'Menunggu',
            'status_pendaftaran'       => 'Menunggu',
        ]);

        $response = $this->putJson("/api/pendaftaran/{$pendaftaran->id}/rekomendasi", [
            'status_rekomendasi_dosen' => 'Direkomendasikan',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Rekomendasi dosen berhasil diperbarui.',
                'data'    => [
                    'status_rekomendasi_dosen' => 'Direkomendasikan',
                ],
            ]);

        $this->assertEquals('Direkomendasikan', $pendaftaran->fresh()->status_rekomendasi_dosen);
    }

    /**
     * Test Admin CANNOT accept registration if Dosen has not recommended yet.
     */
    public function test_admin_cannot_accept_if_not_recommended_by_dosen(): void
    {
        $pendaftaran = Pendaftaran::create([
            'kegiatan_id'              => 1,
            'mahasiswa_id'             => 10,
            'dosen_wali_id'            => 4,
            'status_rekomendasi_dosen' => 'Menunggu', // Belum Direkomendasikan
            'status_pendaftaran'       => 'Menunggu',
        ]);

        $response = $this->putJson("/api/pendaftaran/{$pendaftaran->id}/verifikasi-akhir", [
            'status_pendaftaran'  => 'Diterima',
            'catatan_verifikator' => 'Berkas lengkap tapi dosen belum review.',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Pendaftaran belum bisa diterima karena belum direkomendasikan oleh dosen wali.',
            ]);

        $this->assertEquals('Menunggu', $pendaftaran->fresh()->status_pendaftaran);
    }

    /**
     * Test Admin CAN accept registration once Dosen has recommended.
     */
    public function test_admin_can_accept_after_dosen_recommended(): void
    {
        $pendaftaran = Pendaftaran::create([
            'kegiatan_id'              => 1,
            'mahasiswa_id'             => 10,
            'dosen_wali_id'            => 4,
            'status_rekomendasi_dosen' => 'Direkomendasikan', // Sudah direkomendasikan
            'status_pendaftaran'       => 'Menunggu',
        ]);

        $response = $this->putJson("/api/pendaftaran/{$pendaftaran->id}/verifikasi-akhir", [
            'status_pendaftaran'  => 'Diterima',
            'catatan_verifikator' => 'Dosen menyetujui dan berkas lolos.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Verifikasi akhir pendaftaran berhasil disimpan.',
                'data'    => [
                    'status_pendaftaran' => 'Diterima',
                ],
            ]);

        $this->assertEquals('Diterima', $pendaftaran->fresh()->status_pendaftaran);
    }

    /**
     * Test Admin CAN accept registration if activity does not need dosen wali (dosen_wali_id = null).
     */
    public function test_admin_can_accept_if_no_dosen_wali_required(): void
    {
        $pendaftaran = Pendaftaran::create([
            'kegiatan_id'              => 1,
            'mahasiswa_id'             => 10,
            'dosen_wali_id'            => null, // Tidak butuh dosen wali
            'status_rekomendasi_dosen' => 'Menunggu',
            'status_pendaftaran'       => 'Menunggu',
        ]);

        $response = $this->putJson("/api/pendaftaran/{$pendaftaran->id}/verifikasi-akhir", [
            'status_pendaftaran' => 'Diterima',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'status_pendaftaran' => 'Diterima',
                ],
            ]);

        $this->assertEquals('Diterima', $pendaftaran->fresh()->status_pendaftaran);
    }

    /**
     * Test student can view registration history via GET /api/pendaftaran/mahasiswa/{mahasiswa_id}.
     */
    public function test_can_get_student_registration_history(): void
    {
        Pendaftaran::create([
            'kegiatan_id'              => 1,
            'mahasiswa_id'             => 10,
            'status_rekomendasi_dosen' => 'Menunggu',
            'status_pendaftaran'       => 'Menunggu',
        ]);

        $response = $this->getJson('/api/pendaftaran/mahasiswa/10');

        $response->assertStatus(200)
            ->assertJson([
                'status'       => 'success',
                'mahasiswa_id' => 10,
                'total'        => 1,
            ]);
    }

    /**
     * Test POST /api/daftar supports multiple files based on individual requirements.
     */
    public function test_daftar_supports_multiple_files_based_on_requirements(): void
    {
        Storage::fake('public');

        Http::fake([
            'http://localhost:8002/api/kegiatan/1/cek-kuota' => Http::response([
                'tersedia' => true,
                'kegiatan' => [
                    'id'             => 1,
                    'judul_kegiatan' => 'Beasiswa Prestasi Unggulan Akademik 2026',
                    'kuota_maksimal' => 50,
                    'kuota_terisi'   => 5,
                ],
            ], 200),
            'http://localhost:8002/api/kegiatan/1/tambah-kuota-terisi' => Http::response([
                'status'  => 'success',
                'message' => 'Kuota terisi berhasil ditambah.',
            ], 200),
        ]);

        $fileKtm = UploadedFile::fake()->create('ktm_mhs.pdf', 150, 'application/pdf');
        $fileTranskrip = UploadedFile::fake()->create('transkrip_nilai.pdf', 250, 'application/pdf');

        $payload = [
            'kegiatan_id'   => 1,
            'mahasiswa_id'  => 25,
            'dosen_wali_id' => 4,
            'syarat_labels' => [
                'Kartu Tanda Mahasiswa',
                'Transkrip Nilai',
            ],
            'files_syarat'  => [
                $fileKtm,
                $fileTranskrip,
            ],
        ];

        $response = $this->postJson('/api/daftar', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Pendaftaran berhasil dilakukan.',
            ]);

        $pendaftaran = Pendaftaran::where('mahasiswa_id', 25)->where('kegiatan_id', 1)->first();
        $this->assertNotNull($pendaftaran);

        $daftarBerkas = $pendaftaran->daftar_berkas;
        $this->assertIsArray($daftarBerkas);
        $this->assertArrayHasKey('Kartu Tanda Mahasiswa', $daftarBerkas);
        $this->assertArrayHasKey('Transkrip Nilai', $daftarBerkas);

        Storage::disk('public')->assertExists($daftarBerkas['Kartu Tanda Mahasiswa']);
        Storage::disk('public')->assertExists($daftarBerkas['Transkrip Nilai']);
    }
}
