<?php

namespace Tests\Feature;

use App\Models\Prestasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrestasiApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Mahasiswa can submit achievement claim and upload certificate file.
     */
    public function test_mahasiswa_can_submit_prestasi_claim(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('sertifikat_juara.pdf', 300, 'application/pdf');

        $payload = [
            'mahasiswa_id'        => 1,
            'dosen_pembimbing_id' => 4,
            'judul_prestasi'      => 'Juara 1 Hackathon Nasional 2026',
            'tingkat'             => 'Nasional',
            'file_sertifikat'     => $file,
        ];

        $response = $this->postJson('/api/prestasi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Pengajuan klaim prestasi lomba berhasil diunggah.',
                'data'    => [
                    'mahasiswa_id'          => 1,
                    'dosen_pembimbing_id'   => 4,
                    'judul_prestasi'        => 'Juara 1 Hackathon Nasional 2026',
                    'tingkat'               => 'Nasional',
                    'status_validasi_dosen' => 'Menunggu',
                    'status_validasi_akhir' => 'Menunggu',
                ],
            ]);

        $filePath = $response->json('data.file_sertifikat');
        Storage::disk('public')->assertExists($filePath);

        $this->assertDatabaseHas('prestasis', [
            'judul_prestasi' => 'Juara 1 Hackathon Nasional 2026',
            'tingkat'        => 'Nasional',
        ]);
    }

    /**
     * Test Dosen can approve achievement claim.
     */
    public function test_dosen_can_approve_prestasi(): void
    {
        $prestasi = Prestasi::create([
            'mahasiswa_id'          => 1,
            'dosen_pembimbing_id'   => 4,
            'judul_prestasi'        => 'Juara 2 Data Science Competition',
            'tingkat'               => 'Nasional',
            'file_sertifikat'       => 'sertifikat/dummy.pdf',
            'status_validasi_dosen' => 'Menunggu',
            'status_validasi_akhir' => 'Menunggu',
        ]);

        $response = $this->putJson("/api/prestasi/{$prestasi->id}/validasi-dosen", [
            'status_validasi_dosen' => 'Disetujui',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Validasi prestasi oleh dosen pembimbing berhasil disimpan.',
                'data'    => [
                    'status_validasi_dosen' => 'Disetujui',
                ],
            ]);

        $this->assertEquals('Disetujui', $prestasi->fresh()->status_validasi_dosen);
    }

    /**
     * Test Dosen MUST provide catatan when rejecting achievement claim.
     */
    public function test_dosen_must_provide_catatan_when_rejecting(): void
    {
        $prestasi = Prestasi::create([
            'mahasiswa_id'          => 1,
            'dosen_pembimbing_id'   => 4,
            'judul_prestasi'        => 'Peserta Webinar Biasa',
            'tingkat'               => 'Lokal',
            'file_sertifikat'       => 'sertifikat/dummy.pdf',
            'status_validasi_dosen' => 'Menunggu',
            'status_validasi_akhir' => 'Menunggu',
        ]);

        // Gagal jika catatan kosong saat Ditolak
        $responseFail = $this->putJson("/api/prestasi/{$prestasi->id}/validasi-dosen", [
            'status_validasi_dosen' => 'Ditolak',
        ]);
        $responseFail->assertStatus(422)
            ->assertJsonValidationErrors(['catatan']);

        // Berhasil jika catatan diisi
        $responseSuccess = $this->putJson("/api/prestasi/{$prestasi->id}/validasi-dosen", [
            'status_validasi_dosen' => 'Ditolak',
            'catatan'               => 'Bukan sertifikat kejuaraan lomba yang memenuhi kriteria SKPI.',
        ]);
        $responseSuccess->assertStatus(200);

        $this->assertEquals('Ditolak', $prestasi->fresh()->status_validasi_dosen);
        $this->assertEquals('Bukan sertifikat kejuaraan lomba yang memenuhi kriteria SKPI.', $prestasi->fresh()->catatan);
    }

    /**
     * Test Admin CANNOT approve achievement if Dosen has not approved yet.
     */
    public function test_admin_cannot_approve_if_dosen_has_not_approved(): void
    {
        $prestasi = Prestasi::create([
            'mahasiswa_id'          => 1,
            'dosen_pembimbing_id'   => 4, // Ada dosen pembimbing
            'judul_prestasi'        => 'Juara 3 CTF Cyber Security',
            'tingkat'               => 'Provinsi',
            'file_sertifikat'       => 'sertifikat/dummy.pdf',
            'status_validasi_dosen' => 'Menunggu', // Belum disetujui dosen
            'status_validasi_akhir' => 'Menunggu',
        ]);

        $response = $this->putJson("/api/prestasi/{$prestasi->id}/validasi-admin", [
            'status_validasi_akhir' => 'Disetujui',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Admin tidak dapat menyetujui prestasi ini karena belum divalidasi dan disetujui oleh dosen pembimbing.',
            ]);

        $this->assertEquals('Menunggu', $prestasi->fresh()->status_validasi_akhir);
    }

    /**
     * Test Admin CAN approve achievement once Dosen has approved.
     */
    public function test_admin_can_approve_after_dosen_approved(): void
    {
        $prestasi = Prestasi::create([
            'mahasiswa_id'          => 1,
            'dosen_pembimbing_id'   => 4,
            'judul_prestasi'        => 'Juara 1 Web Design Nasional',
            'tingkat'               => 'Nasional',
            'file_sertifikat'       => 'sertifikat/dummy.pdf',
            'status_validasi_dosen' => 'Disetujui', // Sudah disetujui dosen
            'status_validasi_akhir' => 'Menunggu',
        ]);

        $response = $this->putJson("/api/prestasi/{$prestasi->id}/validasi-admin", [
            'status_validasi_akhir' => 'Disetujui',
            'catatan'               => 'Poin SKPI sebesar 25 poin telah ditambahkan ke akun mahasiswa.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Validasi akhir kemahasiswaan berhasil disimpan.',
                'data'    => [
                    'status_validasi_akhir' => 'Disetujui',
                ],
            ]);

        $this->assertEquals('Disetujui', $prestasi->fresh()->status_validasi_akhir);
    }

    /**
     * Test Admin CAN approve achievement if no Dosen Pembimbing was listed.
     */
    public function test_admin_can_approve_if_no_dosen_pembimbing_listed(): void
    {
        $prestasi = Prestasi::create([
            'mahasiswa_id'          => 1,
            'dosen_pembimbing_id'   => null, // Tanpa dosen pembimbing
            'judul_prestasi'        => 'Juara Favorit Lomba Esai Mandiri',
            'tingkat'               => 'Lokal',
            'file_sertifikat'       => 'sertifikat/dummy.pdf',
            'status_validasi_dosen' => 'Menunggu',
            'status_validasi_akhir' => 'Menunggu',
        ]);

        $response = $this->putJson("/api/prestasi/{$prestasi->id}/validasi-admin", [
            'status_validasi_akhir' => 'Disetujui',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'status_validasi_akhir' => 'Disetujui',
                ],
            ]);

        $this->assertEquals('Disetujui', $prestasi->fresh()->status_validasi_akhir);
    }

    /**
     * Test student can view their achievement portfolio via GET /api/prestasi/mahasiswa/{mahasiswa_id}.
     */
    public function test_can_view_student_prestasi_portfolio(): void
    {
        Prestasi::create([
            'mahasiswa_id'          => 1,
            'judul_prestasi'        => 'Prestasi 1',
            'tingkat'               => 'Nasional',
            'file_sertifikat'       => 'sertifikat/1.pdf',
            'status_validasi_dosen' => 'Disetujui',
            'status_validasi_akhir' => 'Disetujui',
        ]);

        Prestasi::create([
            'mahasiswa_id'          => 1,
            'judul_prestasi'        => 'Prestasi 2',
            'tingkat'               => 'Internasional',
            'file_sertifikat'       => 'sertifikat/2.pdf',
            'status_validasi_dosen' => 'Menunggu',
            'status_validasi_akhir' => 'Menunggu',
        ]);

        $response = $this->getJson('/api/prestasi/mahasiswa/1');

        $response->assertStatus(200)
            ->assertJson([
                'status'       => 'success',
                'mahasiswa_id' => 1,
                'total'        => 2,
            ]);
    }

    /**
     * Test Mahasiswa can submit claim even if dosen_pembimbing_id is passed as NIDN string (with leading 0).
     */
    public function test_mahasiswa_can_submit_prestasi_claim_with_nidn_string(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('sertifikat.pdf', 200, 'application/pdf');

        $payload = [
            'mahasiswa_id'        => 1,
            'dosen_pembimbing_id' => '0728069505', // Ahmad Halimi's NIDN with leading 0
            'judul_prestasi'      => 'Juara 1 Lomba Web Design',
            'tingkat'             => 'Nasional',
            'file_sertifikat'     => $file,
        ];

        $response = $this->postJson('/api/prestasi', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Pengajuan klaim prestasi lomba berhasil diunggah.',
            ]);

        $this->assertDatabaseHas('prestasis', [
            'judul_prestasi'      => 'Juara 1 Lomba Web Design',
            'dosen_pembimbing_id' => 728069505,
        ]);
    }
}

