<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PrestasiController;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebController extends Controller
{
    /**
     * URL Microservices (dapat di-override lewat .env)
     */
    protected function authServiceUrl(): string
    {
        return rtrim(env('AUTH_SERVICE_URL', 'http://localhost:8001'), '/');
    }

    protected function kegiatanServiceUrl(): string
    {
        return rtrim(env('KEGIATAN_SERVICE_URL', 'http://localhost:8002'), '/');
    }

    protected function pendaftaranServiceUrl(): string
    {
        return rtrim(env('PENDAFTARAN_SERVICE_URL', 'http://localhost:8003'), '/');
    }

    protected function prestasiServiceUrl(): string
    {
        return rtrim(env('PRESTASI_SERVICE_URL', 'http://localhost:8004'), '/');
    }

    /**
     * 1. Menampilkan Halaman Login.
     * Jika user sudah memiliki session aktif, redirect langsung ke dashboard masing-masing.
     */
    public function showLogin()
    {
        if (session()->has('token') && session()->has('user')) {
            $user = session('user');
            $role = strtolower($user['role'] ?? '');

            return match ($role) {
                'mahasiswa' => redirect()->route('web.mahasiswa.dashboard'),
                'dosen'     => redirect()->route('web.dosen.dashboard'),
                'admin', 'ormawa' => redirect()->route('web.admin.dashboard'),
                default     => redirect()->route('web.mahasiswa.dashboard'),
            };
        }

        return view('login');
    }

    /**
     * 2. Memproses Login via Auth Service (Port 8001).
     * Endpoint API: POST http://localhost:8001/api/login
     *
     * Catatan Ketahanan (Resilience):
     * - Pertama mencoba menembak Auth Service di Port 8001 via HTTP.
     * - Jika Port 8001 belum dinyalakan terpisah (Connection Refused),
     *   sistem otomatis melakukan in-process fallback ke AuthController lokal
     *   sehingga pengguna tetap bisa login dengan lancar tanpa terhenti error cURL.
     */
    public function processLogin(Request $request)
    {
        $request->validate([
            'nim_nidn' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nim_nidn.required' => 'NIM / NIDN wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $status = null;
        $data = [];
        $isFallback = false;

        try {
            // Coba HTTP request ke Auth Service (Port 8001)
            $response = Http::timeout(2)->post("{$this->authServiceUrl()}/api/login", [
                'nim_nidn' => $request->input('nim_nidn'),
                'password' => $request->input('password'),
            ]);

            $status = $response->status();
            $data = $response->json() ?? [];
        } catch (\Throwable $e) {
            // Smart Fallback jika Port 8001 belum aktif
            $localResp = app(AuthController::class)->login($request);
            $status = $localResp->getStatusCode();
            $data = $localResp->getData(true);
            $isFallback = true;
        }

        if ($status === 200 && ($data['status'] ?? '') === 'success') {
            $payload = $data['data'] ?? [];
            $token = $payload['access_token'] ?? $payload['token'] ?? 'mock-token';
            $user  = $payload['user'] ?? [];

            // Simpan token & user ke session
            session([
                'token'       => $token,
                'user'        => $user,
                'is_fallback' => $isFallback,
            ]);

            $role = strtolower($user['role'] ?? '');
            $nama = $user['nama_lengkap'] ?? 'Pengguna';

            return match ($role) {
                'mahasiswa' => redirect()->route('web.mahasiswa.dashboard')
                    ->with('success', "Selamat datang kembali, {$nama}!"),
                'dosen'     => redirect()->route('web.dosen.dashboard')
                    ->with('success', "Selamat datang Bapak/Ibu Dosen {$nama}!"),
                'admin', 'ormawa' => redirect()->route('web.admin.dashboard')
                    ->with('success', "Selamat datang Admin/Ormawa {$nama}!"),
                default     => redirect()->route('web.mahasiswa.dashboard'),
            };
        }

        $pesanError = $data['message'] ?? 'Kombinasi NIM/NIDN dan password salah.';

        return back()->withInput($request->only('nim_nidn'))
            ->withErrors(['nim_nidn' => $pesanError]);
    }

    /**
     * 3. Logout Pengguna.
     */
    public function logout(Request $request)
    {
        $token = session('token');

        if ($token) {
            try {
                Http::timeout(1)->withToken($token)->post("{$this->authServiceUrl()}/api/logout");
            } catch (\Throwable $e) {
                // Abaikan jika Auth Service offline saat logout
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('web.login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * 4. Dashboard Mahasiswa.
     */
    public function dashboardMahasiswa(Request $request)
    {
        $token = session('token');
        $user  = session('user');
        $mahasiswaId = (int) ($user['id'] ?? 1);

        $kegiatans    = [];
        $pendaftarans = [];
        $prestasis    = [];
        $errorNotices = [];

        // 1. Ambil Data Kegiatan Published (Port 8002)
        try {
            $respKegiatan = Http::timeout(2)->withToken($token)->get("{$this->kegiatanServiceUrl()}/api/kegiatan/published");
            if ($respKegiatan->successful()) {
                $kegiatans = $respKegiatan->json('data') ?? [];
            } else {
                $respFallback = Http::timeout(2)->withToken($token)->get("{$this->kegiatanServiceUrl()}/api/kegiatan");
                if ($respFallback->successful()) {
                    $kegiatans = $respFallback->json('data') ?? [];
                }
            }
        } catch (\Throwable $e) {
            // In-process fallback
            $localResp = app(KegiatanController::class)->index(new Request());
            $kegiatans = $localResp->getData(true)['data'] ?? [];
        }

        // 2. Ambil Riwayat Pendaftaran Mahasiswa (Port 8003)
        try {
            $respPendaftaran = Http::timeout(2)->withToken($token)
                ->get("{$this->pendaftaranServiceUrl()}/api/pendaftaran/mahasiswa/{$mahasiswaId}");
            if ($respPendaftaran->successful()) {
                $pendaftarans = $respPendaftaran->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            // In-process fallback
            $localResp = app(PendaftaranController::class)->riwayatMahasiswa($mahasiswaId);
            $pendaftarans = $localResp->getData(true)['data'] ?? [];
        }

        // 3. Ambil Portofolio Prestasi Mahasiswa (Port 8004)
        try {
            $respPrestasi = Http::timeout(2)->withToken($token)
                ->get("{$this->prestasiServiceUrl()}/api/prestasi/mahasiswa/{$mahasiswaId}");
            if ($respPrestasi->successful()) {
                $prestasis = $respPrestasi->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            // In-process fallback
            $localResp = app(PrestasiController::class)->riwayatMahasiswa($mahasiswaId);
            $prestasis = $localResp->getData(true)['data'] ?? [];
        }

        // Data Enrichment (Nama Kegiatan & Detail Kategori)
        $pendaftarans = $this->enrichPendaftarans($pendaftarans);

        // Ambil Data Referensi Dosen dari Campus API Service (Pilihan Dosen Wali & Pembimbing)
        // Dikelompokkan berdasarkan kesesuaian Program Studi Mahasiswa
        $campusService = app(\App\Services\CampusApiService::class);
        $studentProdi  = $user['prodi'] ?? null;
        $dosenGrouped  = $campusService->getDaftarDosenGrouped($studentProdi);
        $daftarDosen   = $campusService->getDaftarDosen();

        return view('dashboard-mahasiswa', compact('user', 'kegiatans', 'pendaftarans', 'prestasis', 'errorNotices', 'daftarDosen', 'dosenGrouped'));
    }

    /**
     * 5. Dashboard Dosen.
     */
    public function dashboardDosen(Request $request)
    {
        $token   = session('token');
        $user    = session('user');
        $dosenId = $user['id'] ?? null;

        $pendaftarans = [];
        $prestasis    = [];
        $errorNotices = [];

        // 1. Ambil pendaftaran mahasiswa yang butuh rekomendasi (Port 8003)
        try {
            $respPendaftaran = Http::timeout(2)->withToken($token)
                ->get("{$this->pendaftaranServiceUrl()}/api/pendaftaran", [
                    'status_rekomendasi_dosen' => 'Menunggu',
                ]);

            if ($respPendaftaran->successful()) {
                $pendaftarans = $respPendaftaran->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            $req = new Request(['status_rekomendasi_dosen' => 'Menunggu']);
            $localResp = app(PendaftaranController::class)->index($req);
            $pendaftarans = $localResp->getData(true)['data'] ?? [];
        }

        // 2. Ambil pengajuan sertifikat prestasi yang butuh validasi dosen (Port 8004)
        try {
            $respPrestasi = Http::timeout(2)->withToken($token)
                ->get("{$this->prestasiServiceUrl()}/api/prestasi");

            if ($respPrestasi->successful()) {
                $allPrestasi = $respPrestasi->json('data') ?? [];
                $prestasis = array_values(array_filter($allPrestasi, function ($item) use ($dosenId) {
                    $isMenunggu = ($item['status_validasi_dosen'] ?? '') === 'Menunggu';
                    if ($dosenId && !empty($item['dosen_pembimbing_id'])) {
                        return $isMenunggu && ((int) $item['dosen_pembimbing_id'] === (int) $dosenId);
                    }
                    return $isMenunggu;
                }));
            }
        } catch (\Throwable $e) {
            $localResp = app(PrestasiController::class)->index(new Request());
            $allPrestasi = $localResp->getData(true)['data'] ?? [];
            $prestasis = array_values(array_filter($allPrestasi, function ($item) use ($dosenId) {
                $isMenunggu = ($item['status_validasi_dosen'] ?? '') === 'Menunggu';
                if ($dosenId && !empty($item['dosen_pembimbing_id'])) {
                    return $isMenunggu && ((int) $item['dosen_pembimbing_id'] === (int) $dosenId);
                }
                return $isMenunggu;
            }));
        }

        // Data Enrichment (Nama Mahasiswa, NIM, Prodi, Judul Kegiatan)
        $pendaftarans = $this->enrichPendaftarans($pendaftarans);
        $prestasis    = $this->enrichPrestasis($prestasis);

        return view('dashboard-dosen', compact('user', 'pendaftarans', 'prestasis', 'errorNotices'));
    }

    /**
     * 6. Dashboard Admin Kemahasiswaan & Ormawa.
     */
    public function dashboardAdmin(Request $request)
    {
        $token = session('token');
        $user  = session('user');

        $kegiatans    = [];
        $kategoris    = [];
        $pendaftarans = [];
        $prestasis    = [];
        $errorNotices = [];

        // 1. Ambil Seluruh Master Kegiatan (Port 8002)
        try {
            $respKegiatan = Http::timeout(2)->withToken($token)->get("{$this->kegiatanServiceUrl()}/api/kegiatan/semua");
            if ($respKegiatan->successful()) {
                $kegiatans = $respKegiatan->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            $localResp = app(KegiatanController::class)->semua(new Request());
            $kegiatans = $localResp->getData(true)['data'] ?? [];
        }

        // 2. Ambil Master Kategori Kegiatan (Port 8002)
        try {
            $respKategori = Http::timeout(2)->withToken($token)->get("{$this->kegiatanServiceUrl()}/api/kegiatan/kategori");
            if ($respKategori->successful()) {
                $kategoris = $respKategori->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            $localResp = app(KegiatanController::class)->kategoriIndex();
            $kategoris = $localResp->getData(true)['data'] ?? [];
        }

        // 3. Ambil Seluruh Pendaftaran (Port 8003)
        try {
            $respPendaftaran = Http::timeout(2)->withToken($token)->get("{$this->pendaftaranServiceUrl()}/api/pendaftaran");
            if ($respPendaftaran->successful()) {
                $pendaftarans = $respPendaftaran->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            $localResp = app(PendaftaranController::class)->index(new Request());
            $pendaftarans = $localResp->getData(true)['data'] ?? [];
        }

        // 4. Ambil Seluruh Klaim Prestasi (Port 8004)
        try {
            $respPrestasi = Http::timeout(2)->withToken($token)->get("{$this->prestasiServiceUrl()}/api/prestasi");
            if ($respPrestasi->successful()) {
                $prestasis = $respPrestasi->json('data') ?? [];
            }
        } catch (\Throwable $e) {
            $localResp = app(PrestasiController::class)->index(new Request());
            $prestasis = $localResp->getData(true)['data'] ?? [];
        }

        // Data Enrichment (Nama Mahasiswa, NIM, Prodi, Judul Kegiatan)
        $pendaftarans = $this->enrichPendaftarans($pendaftarans);
        $prestasis    = $this->enrichPrestasis($prestasis);

        return view('dashboard-admin', compact('user', 'kegiatans', 'kategoris', 'pendaftarans', 'prestasis', 'errorNotices'));
    }

    /**
     * Data Enrichment (BFF Pattern):
     * Melengkapi ID referensi pendaftaran dengan nama lengkap mahasiswa, NIM, prodi,
     * serta judul kegiatan dan kategori dari service terkait.
     */
    protected function enrichPendaftarans(array $pendaftarans): array
    {
        if (empty($pendaftarans)) {
            return [];
        }

        $mahasiswaIds = array_unique(array_filter(array_column($pendaftarans, 'mahasiswa_id')));
        $kegiatanIds  = array_unique(array_filter(array_column($pendaftarans, 'kegiatan_id')));

        $usersMap = [];
        if (!empty($mahasiswaIds)) {
            $users = User::whereIn('id', $mahasiswaIds)->get()->keyBy('id');
            foreach ($users as $id => $u) {
                $usersMap[$id] = [
                    'id'           => $u->id,
                    'nama_lengkap' => $u->nama_lengkap,
                    'nim_nidn'     => $u->nim_nidn,
                    'prodi'        => $u->prodi,
                    'fakultas'     => $u->fakultas,
                ];
            }
        }

        $kegiatansMap = [];
        if (!empty($kegiatanIds)) {
            $kegs = Kegiatan::with('kategori')->whereIn('id', $kegiatanIds)->get()->keyBy('id');
            foreach ($kegs as $id => $k) {
                $kegiatansMap[$id] = [
                    'id'             => $k->id,
                    'judul_kegiatan' => $k->judul_kegiatan,
                    'kategori'       => $k->kategori ? ['nama_kategori' => $k->kategori->nama_kategori] : null,
                    'kuota_maksimal' => $k->kuota_maksimal,
                    'kuota_terisi'   => $k->kuota_terisi,
                ];
            }
        }

        foreach ($pendaftarans as &$p) {
            $mId = $p['mahasiswa_id'] ?? null;
            $kId = $p['kegiatan_id'] ?? null;

            $p['mahasiswa'] = $usersMap[$mId] ?? [
                'id'           => $mId,
                'nama_lengkap' => "Mahasiswa #{$mId}",
                'nim_nidn'     => '-',
                'prodi'        => '-',
            ];

            $p['kegiatan'] = $kegiatansMap[$kId] ?? [
                'id'             => $kId,
                'judul_kegiatan' => "Kegiatan #{$kId}",
                'kategori'       => ['nama_kategori' => 'Umum'],
            ];

            // Parsing daftar berkas multi-file JSON maupun format legacy single-file
            $berkas = [];
            $rawFile = $p['file_syarat'] ?? null;
            if (!empty($rawFile)) {
                if (is_array($rawFile)) {
                    $berkas = $rawFile;
                } elseif (is_string($rawFile) && (str_starts_with(trim($rawFile), '{') || str_starts_with(trim($rawFile), '['))) {
                    $decoded = json_decode(trim($rawFile), true);
                    $berkas = is_array($decoded) ? $decoded : ['Dokumen Syarat' => $rawFile];
                } else {
                    $berkas = ['Dokumen Syarat' => $rawFile];
                }
            }
            $p['daftar_berkas'] = $berkas;
        }

        return $pendaftarans;
    }

    /**
     * Data Enrichment (BFF Pattern):
     * Melengkapi ID referensi prestasi dengan data detail mahasiswa dan dosen pembimbing.
     */
    protected function enrichPrestasis(array $prestasis): array
    {
        if (empty($prestasis)) {
            return [];
        }

        $mahasiswaIds = array_unique(array_filter(array_column($prestasis, 'mahasiswa_id')));
        $dosenIds     = array_unique(array_filter(array_column($prestasis, 'dosen_pembimbing_id')));
        $allUserIds   = array_unique(array_merge($mahasiswaIds, $dosenIds));

        $usersMap = [];
        if (!empty($allUserIds)) {
            $users = User::whereIn('id', $allUserIds)->get()->keyBy('id');
            foreach ($users as $id => $u) {
                $usersMap[$id] = [
                    'id'           => $u->id,
                    'nama_lengkap' => $u->nama_lengkap,
                    'nim_nidn'     => $u->nim_nidn,
                    'prodi'        => $u->prodi,
                    'fakultas'     => $u->fakultas,
                ];
            }
        }

        foreach ($prestasis as &$item) {
            $mId = $item['mahasiswa_id'] ?? null;
            $dId = $item['dosen_pembimbing_id'] ?? null;

            $item['mahasiswa'] = $usersMap[$mId] ?? [
                'id'           => $mId,
                'nama_lengkap' => "Mahasiswa #{$mId}",
                'nim_nidn'     => '-',
                'prodi'        => '-',
            ];

            $item['dosen_pembimbing'] = $usersMap[$dId] ?? [
                'id'           => $dId,
                'nama_lengkap' => $dId ? "Dosen #{$dId}" : '-',
                'nim_nidn'     => '-',
            ];
        }

        return $prestasis;
    }

    /**
     * Master Data: Tambah Kegiatan Baru.
     */
    public function tambahKegiatan(Request $request)
    {
        $user  = session('user');
        $token = session('token');

        $request->validate([
            'judul_kegiatan'    => ['required', 'string', 'max:255'],
            'kategori_id'       => ['required', 'integer'],
            'deskripsi'         => ['required', 'string'],
            'persyaratan'       => ['nullable', 'string'],
            'batas_pendaftaran' => ['required', 'date'],
            'kuota_maksimal'    => ['required', 'integer', 'min:1'],
            'status_publikasi'  => ['required', 'in:Draft,Published'],
        ]);

        $payload = [
            'pembuat_id'        => (int) ($user['id'] ?? 2),
            'kategori_id'       => (int) $request->input('kategori_id'),
            'judul_kegiatan'    => $request->input('judul_kegiatan'),
            'deskripsi'         => $request->input('deskripsi'),
            'persyaratan'       => $request->input('persyaratan'),
            'batas_pendaftaran' => $request->input('batas_pendaftaran'),
            'kuota_maksimal'    => (int) $request->input('kuota_maksimal'),
            'status_publikasi'  => $request->input('status_publikasi', 'Published'),
        ];

        try {
            $response = Http::timeout(3)->withToken($token)
                ->post("{$this->kegiatanServiceUrl()}/api/kegiatan", $payload);

            if ($response->successful()) {
                return back()->with('success', 'Master Kegiatan baru berhasil ditambahkan!');
            }
            return back()->with('error', $response->json('message') ?? 'Gagal menambahkan kegiatan.');
        } catch (\Throwable $e) {
            $subReq = Request::create('/api/kegiatan', 'POST', $payload);
            $res = app(KegiatanController::class)->store($subReq);
            if ($res->getStatusCode() === 201) {
                return back()->with('success', 'Master Kegiatan baru berhasil ditambahkan!');
            }
            return back()->with('error', $res->getData(true)['message'] ?? 'Gagal menambahkan kegiatan.');
        }
    }

    /**
     * Master Data: Perbarui Kegiatan.
     */
    public function updateKegiatan(Request $request, int $id)
    {
        $token = session('token');

        $request->validate([
            'judul_kegiatan'    => ['required', 'string', 'max:255'],
            'kategori_id'       => ['required', 'integer'],
            'deskripsi'         => ['required', 'string'],
            'persyaratan'       => ['nullable', 'string'],
            'batas_pendaftaran' => ['required', 'date'],
            'kuota_maksimal'    => ['required', 'integer', 'min:1'],
            'status_publikasi'  => ['required', 'in:Draft,Published'],
        ]);

        $payload = $request->only([
            'judul_kegiatan', 'kategori_id', 'deskripsi', 'persyaratan',
            'batas_pendaftaran', 'kuota_maksimal', 'status_publikasi'
        ]);

        try {
            $response = Http::timeout(3)->withToken($token)
                ->put("{$this->kegiatanServiceUrl()}/api/kegiatan/{$id}", $payload);

            if ($response->successful()) {
                return back()->with('success', 'Master Kegiatan berhasil diperbarui!');
            }
            return back()->with('error', $response->json('message') ?? 'Gagal memperbarui kegiatan.');
        } catch (\Throwable $e) {
            $subReq = Request::create("/api/kegiatan/{$id}", 'PUT', $payload);
            $res = app(KegiatanController::class)->update($subReq, $id);
            if ($res->getStatusCode() === 200) {
                return back()->with('success', 'Master Kegiatan berhasil diperbarui!');
            }
            return back()->with('error', $res->getData(true)['message'] ?? 'Gagal memperbarui kegiatan.');
        }
    }

    /**
     * Master Data: Toggle Status Publikasi (Draft <-> Published).
     */
    public function togglePublikasiKegiatan(Request $request, int $id)
    {
        $token = session('token');
        $current = $request->input('status_publikasi');
        $statusBaru = ($current === 'Published') ? 'Draft' : 'Published';

        try {
            $response = Http::timeout(3)->withToken($token)
                ->put("{$this->kegiatanServiceUrl()}/api/kegiatan/{$id}", ['status_publikasi' => $statusBaru]);

            if ($response->successful()) {
                return back()->with('success', "Status publikasi kegiatan diubah menjadi {$statusBaru}!");
            }
            return back()->with('error', 'Gagal mengubah status publikasi.');
        } catch (\Throwable $e) {
            $subReq = Request::create("/api/kegiatan/{$id}", 'PUT', ['status_publikasi' => $statusBaru]);
            $res = app(KegiatanController::class)->update($subReq, $id);
            if ($res->getStatusCode() === 200) {
                return back()->with('success', "Status publikasi kegiatan diubah menjadi {$statusBaru}!");
            }
            return back()->with('error', 'Gagal mengubah status publikasi.');
        }
    }

    /**
     * Master Data: Hapus Kegiatan.
     */
    public function hapusKegiatan(int $id)
    {
        $token = session('token');

        try {
            $response = Http::timeout(3)->withToken($token)
                ->delete("{$this->kegiatanServiceUrl()}/api/kegiatan/{$id}");

            if ($response->successful()) {
                return back()->with('success', 'Kegiatan berhasil dihapus dari master data!');
            }
            return back()->with('error', $response->json('message') ?? 'Gagal menghapus kegiatan.');
        } catch (\Throwable $e) {
            $res = app(KegiatanController::class)->destroy($id);
            if ($res->getStatusCode() === 200) {
                return back()->with('success', 'Kegiatan berhasil dihapus dari master data!');
            }
            return back()->with('error', $res->getData(true)['message'] ?? 'Gagal menghapus kegiatan.');
        }
    }

    /**
     * Master Data: Tambah Kategori Baru.
     */
    public function tambahKategori(Request $request)
    {
        $token = session('token');
        $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        try {
            $response = Http::timeout(3)->withToken($token)
                ->post("{$this->kegiatanServiceUrl()}/api/kegiatan/kategori", [
                    'nama_kategori' => $request->input('nama_kategori'),
                ]);

            if ($response->successful()) {
                return back()->with('success', 'Kategori baru berhasil ditambahkan ke master data!');
            }
            return back()->with('error', $response->json('message') ?? 'Gagal menambahkan kategori.');
        } catch (\Throwable $e) {
            $subReq = Request::create('/api/kegiatan/kategori', 'POST', [
                'nama_kategori' => $request->input('nama_kategori'),
            ]);
            $res = app(KegiatanController::class)->kategoriStore($subReq);
            if ($res->getStatusCode() === 201) {
                return back()->with('success', 'Kategori baru berhasil ditambahkan ke master data!');
            }
            return back()->with('error', $res->getData(true)['message'] ?? 'Gagal menambahkan kategori.');
        }
    }

    /**
     * Aksi Form Mahasiswa: Mendaftar Kegiatan.
     */
    public function daftarKegiatan(Request $request)
    {
        $user  = session('user');
        $token = session('token');

        $request->validate([
            'kegiatan_id'    => ['required', 'integer'],
            'file_syarat'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'files_syarat'   => ['nullable', 'array'],
            'files_syarat.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'syarat_labels'  => ['nullable', 'array'],
        ]);

        $resolvedDosenWaliId = $this->resolveDosenUserId($request->input('dosen_wali_id'));

        try {
            $httpReq = Http::timeout(10)->withToken($token);

            if ($request->hasFile('files_syarat')) {
                foreach ($request->file('files_syarat') as $idx => $f) {
                    if ($f && $f->isValid()) {
                        $httpReq = $httpReq->attach(
                            "files_syarat[{$idx}]",
                            file_get_contents($f->getRealPath()),
                            $f->getClientOriginalName()
                        );
                    }
                }
            } elseif ($request->hasFile('file_syarat')) {
                $file = $request->file('file_syarat');
                if ($file && $file->isValid()) {
                    $httpReq = $httpReq->attach(
                        'file_syarat',
                        file_get_contents($file->getRealPath()),
                        $file->getClientOriginalName()
                    );
                }
            }

            $postData = [
                'kegiatan_id'   => (int) $request->input('kegiatan_id'),
                'mahasiswa_id'  => (int) ($user['id'] ?? 1),
                'dosen_wali_id' => $resolvedDosenWaliId,
            ];

            if ($request->filled('syarat_labels')) {
                $postData['syarat_labels'] = $request->input('syarat_labels');
            }

            $response = $httpReq->post("{$this->pendaftaranServiceUrl()}/api/daftar", $postData);

            if ($response->successful()) {
                return back()->with('success', 'Pendaftaran kegiatan berhasil dilakukan!');
            }

            $errMsg = $response->json('message') ?? 'Gagal melakukan pendaftaran.';
            if (!empty($response->json('errors'))) {
                $firstErr = collect($response->json('errors'))->flatten()->first();
                if ($firstErr) {
                    $errMsg .= ' (' . $firstErr . ')';
                }
            }
            return back()->with('error', $errMsg);
        } catch (\Throwable $e) {
            // Fallback internal
            $subReqData = [
                'kegiatan_id'   => (int) $request->input('kegiatan_id'),
                'mahasiswa_id'  => (int) ($user['id'] ?? 1),
                'dosen_wali_id' => $resolvedDosenWaliId,
            ];
            if ($request->filled('syarat_labels')) {
                $subReqData['syarat_labels'] = $request->input('syarat_labels');
            }

            $subReq = Request::create('/api/daftar', 'POST', $subReqData, [], $request->allFiles());

            $localResp = app(PendaftaranController::class)->daftar($subReq);
            if ($localResp->getStatusCode() === 201) {
                return back()->with('success', 'Pendaftaran kegiatan berhasil dilakukan!');
            }

            $resData = $localResp->getData(true);
            $errMsg = $resData['message'] ?? 'Gagal melakukan pendaftaran.';
            if (!empty($resData['errors'])) {
                $firstErr = collect($resData['errors'])->flatten()->first();
                if ($firstErr) {
                    $errMsg .= ' (' . $firstErr . ')';
                }
            }
            return back()->with('error', $errMsg);
        }
    }

    /**
     * Aksi Form Mahasiswa: Mengajukan Klaim Prestasi SKPI.
     */
    public function ajukanPrestasi(Request $request)
    {
        $user  = session('user');
        $token = session('token');

        $request->validate([
            'judul_prestasi'  => ['required', 'string', 'max:255'],
            'tingkat'         => ['required', 'in:Lokal,Provinsi,Nasional,Internasional'],
            'file_sertifikat' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $resolvedDosenPembimbingId = $this->resolveDosenUserId($request->input('dosen_pembimbing_id'));

        try {
            $file = $request->file('file_sertifikat');
            $response = Http::timeout(5)->withToken($token)
                ->attach('file_sertifikat', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("{$this->prestasiServiceUrl()}/api/prestasi", [
                    'mahasiswa_id'        => (int) ($user['id'] ?? 1),
                    'dosen_pembimbing_id' => $resolvedDosenPembimbingId,
                    'judul_prestasi'      => $request->input('judul_prestasi'),
                    'tingkat'             => $request->input('tingkat'),
                ]);

            if ($response->successful()) {
                return back()->with('success', 'Klaim sertifikat prestasi berhasil diajukan untuk SKPI!');
            }

            $errMsg = $response->json('message') ?? 'Gagal mengajukan prestasi.';
            if (!empty($response->json('errors'))) {
                $firstErr = collect($response->json('errors'))->flatten()->first();
                if ($firstErr) {
                    $errMsg .= ' (' . $firstErr . ')';
                }
            }
            return back()->with('error', $errMsg);
        } catch (\Throwable $e) {
            $subReq = Request::create('/api/prestasi', 'POST', [
                'mahasiswa_id'        => (int) ($user['id'] ?? 1),
                'dosen_pembimbing_id' => $resolvedDosenPembimbingId,
                'judul_prestasi'      => $request->input('judul_prestasi'),
                'tingkat'             => $request->input('tingkat'),
            ], [], $request->allFiles());

            $localResp = app(PrestasiController::class)->store($subReq);
            if ($localResp->getStatusCode() === 201) {
                return back()->with('success', 'Klaim sertifikat prestasi berhasil diajukan untuk SKPI!');
            }

            $resData = $localResp->getData(true);
            $errMsg = $resData['message'] ?? 'Gagal mengajukan prestasi.';
            if (!empty($resData['errors'])) {
                $firstErr = collect($resData['errors'])->flatten()->first();
                if ($firstErr) {
                    $errMsg .= ' (' . $firstErr . ')';
                }
            }
            return back()->with('error', $errMsg);
        }
    }

    /**
     * Resolusi ID Dosen:
     * Mengonversi NIDN, ID Penduduk kampus, atau User ID ke ID integer resmi di tabel users (db_auth).
     * Jika dosen belum ada di tabel users, otomatis dibuatkan secara Just-In-Time (JIT).
     */
    protected function resolveDosenUserId($nidnOrId): ?int
    {
        if (empty($nidnOrId) || $nidnOrId === '-') {
            return null;
        }

        // 1. Cek jika sudah berupa numeric ID user lokal di tabel users
        if (is_numeric($nidnOrId)) {
            $userById = User::where('id', (int) $nidnOrId)->where('role', 'dosen')->first();
            if ($userById) {
                return (int) $userById->id;
            }
        }

        // 2. Cek apakah ada dosen dengan nim_nidn ini di tabel users
        $userByNidn = User::where('nim_nidn', (string) $nidnOrId)->first();
        if ($userByNidn) {
            return (int) $userByNidn->id;
        }

        // 3. Jika belum ada di users lokal, cari detail dari API kampus & simpan ke tabel users
        try {
            $campusService = app(\App\Services\CampusApiService::class);
            $dosenList = $campusService->getDaftarDosen((string) $nidnOrId);
            foreach ($dosenList as $d) {
                if ((string) ($d['id'] ?? '') === (string) $nidnOrId || (string) ($d['nim_nidn'] ?? '') === (string) $nidnOrId) {
                    $nidnClean = ($d['nim_nidn'] !== '-' && !empty($d['nim_nidn']))
                        ? (string) $d['nim_nidn']
                        : 'DSN_' . substr(md5($d['nama_lengkap']), 0, 8);

                    $newUser = User::updateOrCreate(
                        ['nim_nidn' => $nidnClean],
                        [
                            'nama_lengkap' => $d['nama_lengkap'],
                            'role'         => 'dosen',
                            'prodi'        => $d['prodi'] ?? 'Teknik',
                            'fakultas'     => 'Fakultas Teknik',
                            'password'     => Hash::make('password123'),
                        ]
                    );
                    return (int) $newUser->id;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal auto-resolve dosen user: ' . $e->getMessage());
        }

        // 4. Fallback ke dosen pertama di tabel users atau null
        $fallback = User::where('role', 'dosen')->first();
        return $fallback ? (int) $fallback->id : null;
    }

    /**
     * Aksi Dosen: Berikan Rekomendasi Pendaftaran Mahasiswa.
     */
    public function rekomendasiPendaftaran(Request $request, int $id)
    {
        $request->validate([
            'status_rekomendasi_dosen' => ['required', 'in:Direkomendasikan,Ditolak'],
        ]);

        try {
            $response = Http::timeout(3)->put("{$this->pendaftaranServiceUrl()}/api/pendaftaran/{$id}/rekomendasi", [
                'status_rekomendasi_dosen' => $request->input('status_rekomendasi_dosen'),
            ]);

            if ($response->successful()) {
                return back()->with('success', 'Status rekomendasi mahasiswa berhasil diperbarui.');
            }

            return back()->with('error', $response->json('message') ?? 'Gagal memperbarui status rekomendasi.');
        } catch (\Throwable $e) {
            $localResp = app(PendaftaranController::class)->rekomendasi($request, $id);
            if ($localResp->getStatusCode() === 200) {
                return back()->with('success', 'Status rekomendasi mahasiswa berhasil diperbarui.');
            }
            return back()->with('error', $localResp->getData(true)['message'] ?? 'Gagal memperbarui status rekomendasi.');
        }
    }

    /**
     * Aksi Dosen: Validasi Prestasi Mahasiswa.
     */
    public function validasiPrestasiDosen(Request $request, int $id)
    {
        $request->validate([
            'status_validasi_dosen' => ['required', 'in:Disetujui,Ditolak'],
            'catatan'               => ['nullable', 'string'],
        ]);

        try {
            $response = Http::timeout(3)->put("{$this->prestasiServiceUrl()}/api/prestasi/{$id}/validasi-dosen", [
                'status_validasi_dosen' => $request->input('status_validasi_dosen'),
                'catatan'               => $request->input('catatan'),
            ]);

            if ($response->successful()) {
                return back()->with('success', 'Validasi prestasi dosen berhasil disimpan.');
            }

            return back()->with('error', $response->json('message') ?? 'Gagal memvalidasi prestasi.');
        } catch (\Throwable $e) {
            $localResp = app(PrestasiController::class)->validasiDosen($request, $id);
            if ($localResp->getStatusCode() === 200) {
                return back()->with('success', 'Validasi prestasi dosen berhasil disimpan.');
            }
            return back()->with('error', $localResp->getData(true)['message'] ?? 'Gagal memvalidasi prestasi.');
        }
    }

    /**
     * Aksi Admin: Verifikasi Akhir Pendaftaran Mahasiswa.
     */
    public function verifikasiAkhirPendaftaran(Request $request, int $id)
    {
        $user = session('user');
        if (($user['role'] ?? '') !== 'admin') {
            return back()->with('error', 'Akses ditolak. Hanya Admin Kemahasiswaan yang berhak melakukan verifikasi pendaftaran.');
        }

        $request->validate([
            'status_pendaftaran'  => ['required', 'in:Menunggu,Lolos Berkas,Diterima,Revisi,Ditolak'],
            'catatan_verifikator' => ['nullable', 'string'],
        ]);

        try {
            $response = Http::timeout(3)->withToken(session('token'))
                ->put("{$this->pendaftaranServiceUrl()}/api/pendaftaran/{$id}/verifikasi-akhir", [
                    'status_pendaftaran'  => $request->input('status_pendaftaran'),
                    'catatan_verifikator' => $request->input('catatan_verifikator'),
                ]);

            if ($response->successful()) {
                return back()->with('success', 'Verifikasi akhir pendaftaran berhasil diperbarui.');
            }

            return back()->with('error', $response->json('message') ?? 'Gagal memverifikasi pendaftaran.');
        } catch (\Throwable $e) {
            $localResp = app(PendaftaranController::class)->verifikasiAkhir($request, $id);
            if ($localResp->getStatusCode() === 200) {
                return back()->with('success', 'Verifikasi akhir pendaftaran berhasil diperbarui.');
            }
            return back()->with('error', $localResp->getData(true)['message'] ?? 'Gagal memverifikasi pendaftaran.');
        }
    }

    /**
     * Aksi Admin: Validasi Akhir Prestasi SKPI.
     */
    public function validasiAkhirPrestasi(Request $request, int $id)
    {
        $user = session('user');
        if (($user['role'] ?? '') !== 'admin') {
            return back()->with('error', 'Akses ditolak. Hanya Admin Kemahasiswaan yang berhak memvalidasi klaim prestasi SKPI.');
        }

        $request->validate([
            'status_validasi_akhir' => ['required', 'in:Disetujui,Ditolak'],
            'catatan'               => ['nullable', 'string'],
        ]);

        try {
            $response = Http::timeout(3)->withToken(session('token'))
                ->put("{$this->prestasiServiceUrl()}/api/prestasi/{$id}/validasi-admin", [
                    'status_validasi_akhir' => $request->input('status_validasi_akhir'),
                    'catatan'               => $request->input('catatan'),
                ]);

            if ($response->successful()) {
                return back()->with('success', 'Validasi akhir kemahasiswaan berhasil disimpan.');
            }

            return back()->with('error', $response->json('message') ?? 'Gagal memvalidasi prestasi.');
        } catch (\Throwable $e) {
            $localResp = app(PrestasiController::class)->validasiAdmin($request, $id);
            if ($localResp->getStatusCode() === 200) {
                return back()->with('success', 'Validasi akhir kemahasiswaan berhasil disimpan.');
            }
            return back()->with('error', $localResp->getData(true)['message'] ?? 'Gagal memvalidasi prestasi.');
        }
    }
}
