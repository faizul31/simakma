<?php

namespace Tests\Feature;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Pendaftaran;
use App\Models\Prestasi;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(UserSeeder::class);

        // Instan-kan fallback tanpa menunggu cURL timeout ke localhost:8001-8004
        Http::fake([
            'http://localhost:*' => function () {
                throw new ConnectionException('Service offline - using in-process controller fallback');
            },
        ]);
    }

    public function test_complete_end_to_end_system_workflow(): void
    {
        // 1. SETUP USERS
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        $dosen     = User::where('role', 'dosen')->first();
        $admin     = User::where('role', 'admin')->first();

        $this->assertNotNull($mahasiswa, 'User mahasiswa harus ada');
        $this->assertNotNull($dosen, 'User dosen harus ada');
        $this->assertNotNull($admin, 'User admin harus ada');

        // =========================================================================
        // STEP 1: ADMIN MENAMBAHKAN KATEGORI & MASTER KEGIATAN BARU
        // =========================================================================
        $adminSession = [
            'token' => 'admin-sanctum-token',
            'user'  => $admin->toArray(),
        ];

        // 1.a Tambah Kategori
        $respKat = $this->withSession($adminSession)->post('/admin/kategori', [
            'nama_kategori' => 'Kompetisi Nasional Mahasiswa',
        ]);
        $respKat->assertRedirect();
        $respKat->assertSessionHas('success');

        $kategori = KategoriKegiatan::where('nama_kategori', 'Kompetisi Nasional Mahasiswa')->first();
        $this->assertNotNull($kategori);

        // 1.b Tambah Kegiatan (Status: Draft)
        $respKeg = $this->withSession($adminSession)->post('/admin/kegiatan', [
            'judul_kegiatan'    => 'Hackathon Inovasi Kampus 2026',
            'kategori_id'       => $kategori->id,
            'deskripsi'         => 'Kompetisi inovasi teknologi dan pemrograman 48 jam.',
            'batas_pendaftaran' => now()->addDays(14)->format('Y-m-d\TH:i'),
            'kuota_maksimal'    => 20,
            'status_publikasi'  => 'Draft',
        ]);
        $respKeg->assertRedirect();
        $respKeg->assertSessionHas('success');

        $kegiatan = Kegiatan::where('judul_kegiatan', 'Hackathon Inovasi Kampus 2026')->first();
        $this->assertNotNull($kegiatan);
        $this->assertEquals('Draft', $kegiatan->status_publikasi);

        // =========================================================================
        // STEP 2: MAHASISWA TIDAK DAPAT MELIHAT KEGIATAN DRAFT DI KATALOG
        // =========================================================================
        $mhsSession = [
            'token' => 'mahasiswa-sanctum-token',
            'user'  => $mahasiswa->toArray(),
        ];

        $respMhsKatalog1 = $this->withSession($mhsSession)->get('/mahasiswa/dashboard');
        $respMhsKatalog1->assertStatus(200);
        $respMhsKatalog1->assertDontSee('Hackathon Inovasi Kampus 2026');

        // =========================================================================
        // STEP 3: ADMIN MENERBITKAN (PUBLISH) KEGIATAN
        // =========================================================================
        $respToggle = $this->withSession($adminSession)->post("/admin/kegiatan/{$kegiatan->id}/toggle", [
            'status_publikasi' => 'Draft',
        ]);
        $respToggle->assertRedirect();
        $respToggle->assertSessionHas('success');

        $kegiatan->refresh();
        $this->assertEquals('Published', $kegiatan->status_publikasi);

        // Sekarang kegiatan harus muncul di dashboard mahasiswa
        $respMhsKatalog2 = $this->withSession($mhsSession)->get('/mahasiswa/dashboard');
        $respMhsKatalog2->assertStatus(200);
        $respMhsKatalog2->assertSee('Hackathon Inovasi Kampus 2026');

        // =========================================================================
        // STEP 4: MAHASISWA MENDAFTAR KEGIATAN DENGAN BERKAS PERSYARATAN
        // =========================================================================
        $fileSyarat = UploadedFile::fake()->create('ktm_dan_transkrip.pdf', 500, 'application/pdf');

        $respDaftar = $this->withSession($mhsSession)->post('/mahasiswa/daftar', [
            'kegiatan_id'   => $kegiatan->id,
            'dosen_wali_id' => $dosen->id,
            'file_syarat'   => $fileSyarat,
        ]);
        $respDaftar->assertRedirect();
        $respDaftar->assertSessionHas('success');

        $pendaftaran = Pendaftaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('kegiatan_id', $kegiatan->id)
            ->first();
        $this->assertNotNull($pendaftaran);
        $this->assertEquals('Menunggu', $pendaftaran->status_rekomendasi_dosen);
        $this->assertEquals('Menunggu', $pendaftaran->status_pendaftaran);
        $this->assertNotNull($pendaftaran->file_syarat);

        // Kuota kegiatan bertambah (+1)
        $kegiatan->refresh();
        $this->assertEquals(1, $kegiatan->kuota_terisi);

        // =========================================================================
        // STEP 5: MAHASISWA MENGAJUKAN KLAIM PRESTASI SKPI
        // =========================================================================
        $fileSertifikat = UploadedFile::fake()->create('sertifikat_juara_1.pdf', 600, 'application/pdf');

        $respKlaim = $this->withSession($mhsSession)->post('/mahasiswa/prestasi', [
            'judul_prestasi'      => 'Juara 1 Lomba Web Design Regional 2026',
            'tingkat'             => 'Provinsi',
            'dosen_pembimbing_id' => $dosen->id,
            'file_sertifikat'     => $fileSertifikat,
        ]);
        $respKlaim->assertRedirect();
        $respKlaim->assertSessionHas('success');

        $prestasi = Prestasi::where('mahasiswa_id', $mahasiswa->id)
            ->where('judul_prestasi', 'Juara 1 Lomba Web Design Regional 2026')
            ->first();
        $this->assertNotNull($prestasi);
        $this->assertEquals('Menunggu', $prestasi->status_validasi_dosen);
        $this->assertEquals('Menunggu', $prestasi->status_validasi_akhir);

        // =========================================================================
        // STEP 6: DOSEN WALI MENINJAU & MEMBERIKAN REKOMENDASI
        // =========================================================================
        $dosenSession = [
            'token' => 'dosen-sanctum-token',
            'user'  => $dosen->toArray(),
        ];

        // 6.a Dashboard Dosen menampilkan permohonan pendaftaran & sertifikat
        $respDosenDash = $this->withSession($dosenSession)->get('/dosen/dashboard');
        $respDosenDash->assertStatus(200);
        $respDosenDash->assertSee($mahasiswa->nama_lengkap);
        $respDosenDash->assertSee('Hackathon Inovasi Kampus 2026');
        $respDosenDash->assertSee('Juara 1 Lomba Web Design Regional 2026');

        // 6.b Dosen menyetujui rekomendasi pendaftaran
        $respRek = $this->withSession($dosenSession)->put("/dosen/pendaftaran/{$pendaftaran->id}/rekomendasi", [
            'status_rekomendasi_dosen' => 'Direkomendasikan',
        ]);
        $respRek->assertRedirect();
        $respRek->assertSessionHas('success');

        $pendaftaran->refresh();
        $this->assertEquals('Direkomendasikan', $pendaftaran->status_rekomendasi_dosen);

        // 6.c Dosen menyetujui validasi sertifikat prestasi lomba
        $respValDosen = $this->withSession($dosenSession)->put("/dosen/prestasi/{$prestasi->id}/validasi", [
            'status_validasi_dosen' => 'Disetujui',
            'catatan'               => 'Sertifikat telah diverifikasi keabsahannya.',
        ]);
        $respValDosen->assertRedirect();
        $respValDosen->assertSessionHas('success');

        $prestasi->refresh();
        $this->assertEquals('Disetujui', $prestasi->status_validasi_dosen);

        // =========================================================================
        // STEP 7: ADMIN KEMAHASISWAAN MEMVERIFIKASI AKHIR
        // =========================================================================
        // 7.a Admin memverifikasi akhir pendaftaran mahasiswa (Diterima)
        $respVerifAdmin = $this->withSession($adminSession)->put("/admin/pendaftaran/{$pendaftaran->id}/verifikasi", [
            'status_pendaftaran'  => 'Diterima',
            'catatan_verifikator' => 'Berkas lengkap dan rekomendasi dosen telah sah.',
        ]);
        $respVerifAdmin->assertRedirect();
        $respVerifAdmin->assertSessionHas('success');

        $pendaftaran->refresh();
        $this->assertEquals('Diterima', $pendaftaran->status_pendaftaran);

        // 7.b Admin memvalidasi final sertifikat SKPI (Disetujui)
        $respValFinal = $this->withSession($adminSession)->put("/admin/prestasi/{$prestasi->id}/validasi", [
            'status_validasi_akhir' => 'Disetujui',
            'catatan'               => 'Poin SKPI ditambahkan ke database kelulusan.',
        ]);
        $respValFinal->assertRedirect();
        $respValFinal->assertSessionHas('success');

        $prestasi->refresh();
        $this->assertEquals('Disetujui', $prestasi->status_validasi_akhir);

        // =========================================================================
        // STEP 8: MAHASISWA MELIHAT STATUS TERAKHIR DI DASHBOARD
        // =========================================================================
        $respMhsFinal = $this->withSession($mhsSession)->get('/mahasiswa/dashboard');
        $respMhsFinal->assertStatus(200);
        $respMhsFinal->assertSee('Diterima');
        $respMhsFinal->assertSee('Direkomendasikan');
        $respMhsFinal->assertSee('Disetujui');
    }

    public function test_business_rule_admin_cannot_accept_registration_without_dosen_recommendation(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        $dosen     = User::where('role', 'dosen')->first();
        $admin     = User::where('role', 'admin')->first();

        $kategori = KategoriKegiatan::create(['nama_kategori' => 'Akademik']);
        $kegiatan = Kegiatan::create([
            'pembuat_id'        => $admin->id,
            'kategori_id'       => $kategori->id,
            'judul_kegiatan'    => 'Seminar Nasional AI 2026',
            'deskripsi'         => 'Seminar kecerdasan buatan.',
            'batas_pendaftaran' => now()->addDays(5),
            'kuota_maksimal'    => 100,
            'kuota_terisi'      => 0,
            'status_publikasi'  => 'Published',
        ]);

        $pendaftaran = Pendaftaran::create([
            'kegiatan_id'              => $kegiatan->id,
            'mahasiswa_id'             => $mahasiswa->id,
            'dosen_wali_id'            => $dosen->id,
            'status_rekomendasi_dosen' => 'Menunggu', // BELUM DIREKOMENDASIKAN
            'status_pendaftaran'       => 'Menunggu',
        ]);

        $adminSession = [
            'token' => 'admin-token',
            'user'  => $admin->toArray(),
        ];

        // Admin mencoba memverifikasi pendaftaran menjadi 'Diterima' saat rekomendasi masih 'Menunggu'
        $resp = $this->withSession($adminSession)->put("/admin/pendaftaran/{$pendaftaran->id}/verifikasi", [
            'status_pendaftaran' => 'Diterima',
        ]);

        $resp->assertRedirect();
        $resp->assertSessionHas('error'); // Ditolak oleh rule bisnis

        $pendaftaran->refresh();
        $this->assertEquals('Menunggu', $pendaftaran->status_pendaftaran, 'Status pendaftaran tidak boleh berubah jadi Diterima jika dosen belum merekomendasikan');
    }

    public function test_business_rule_admin_cannot_approve_prestasi_without_dosen_validation(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        $dosen     = User::where('role', 'dosen')->first();
        $admin     = User::where('role', 'admin')->first();

        $prestasi = Prestasi::create([
            'mahasiswa_id'          => $mahasiswa->id,
            'dosen_pembimbing_id'   => $dosen->id,
            'judul_prestasi'        => 'Lomba Robotika Tingkat Nasional',
            'tingkat'               => 'Nasional',
            'file_sertifikat'       => 'sertifikat/dummy.pdf',
            'status_validasi_dosen' => 'Menunggu', // BELUM DISETUJUI DOSEN
            'status_validasi_akhir' => 'Menunggu',
        ]);

        $adminSession = [
            'token' => 'admin-token',
            'user'  => $admin->toArray(),
        ];

        // Admin mencoba menyetujui final saat dosen pembimbing belum menyetujui
        $resp = $this->withSession($adminSession)->put("/admin/prestasi/{$prestasi->id}/validasi", [
            'status_validasi_akhir' => 'Disetujui',
        ]);

        $resp->assertRedirect();
        $resp->assertSessionHas('error'); // Ditolak oleh validasi rule bisnis

        $prestasi->refresh();
        $this->assertEquals('Menunggu', $prestasi->status_validasi_akhir, 'Status akhir tidak boleh berubah jadi Disetujui jika dosen belum menyetujui');
    }
}
