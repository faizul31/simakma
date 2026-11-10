<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CampusApiService
{
    protected string $baseUrl;
    protected string $devId;
    protected string $xToken;
    protected string $appId;
    protected string $userAgent;

    public function __construct()
    {
        $rawUrl = config('services.campus.base_url', 'https://sso.unuja.ac.id/portal/data');

        // Bersihkan jika user mem-paste URL dokumentasi Swagger / hash
        if (str_contains($rawUrl, '#')) {
            $rawUrl = explode('#', $rawUrl)[0];
        }
        if (str_contains($rawUrl, '/api/docs')) {
            $rawUrl = str_replace('/api/docs', '/portal/data', $rawUrl);
        }

        $this->baseUrl   = rtrim($rawUrl, '/');
        $this->devId     = (string) config('services.campus.dev_id', 'bS6AM42sjbnw1bLV');
        $this->xToken    = (string) config('services.campus.token', 'xnvpZgImLBF5XzcG');
        $this->appId     = (string) config('services.campus.app_id', '1ldd76isb6U2OnZm');
        $this->userAgent = (string) config('services.campus.user_agent', 'PortalKemahasiswaan/1.0');
    }

    /**
     * Memeriksa apakah kredensial API Kampus sudah diatur.
     */
    public function isConfigured(): bool
    {
        return !empty($this->devId)
            && $this->devId !== 'xxxxxxxxx'
            && !empty($this->xToken)
            && $this->xToken !== 'xxxxxxxxxxx'
            && !empty($this->baseUrl);
    }

    /**
     * Cache Key untuk Access Token 32 Karakter.
     */
    protected function getCacheKey(): string
    {
        return 'campus_api_token_' . md5($this->devId . '_' . $this->xToken);
    }

    /**
     * 1. Authorize Data Access.
     * Mengambil Access Token (32 karakter random) dari endpoint /authorize.
     * Token disimpan di Cache selama 5.5 jam (berlaku 6 jam di server kampus).
     */
    public function getAccessToken(bool $forceRefresh = false): ?string
    {
        $cacheKey = $this->getCacheKey();

        if ($forceRefresh) {
            Cache::forget($cacheKey);
            Cache::forget('campus_api_url_data');
        }

        return Cache::remember($cacheKey, now()->addHours(5)->addMinutes(30), function () {
            return $this->requestAuthorizeToken();
        });
    }

    /**
     * Request langsung ke endpoint /authorize.
     */
    public function requestAuthorizeToken(): ?string
    {
        // Pastikan endpoint /authorize sesuai struktur portal/data
        $url = str_ends_with($this->baseUrl, '/authorize')
            ? $this->baseUrl
            : "{$this->baseUrl}/authorize";

        $headers = [
            'X-Dev-Id'     => $this->devId,
            'X-Token'      => $this->xToken,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent'   => $this->userAgent,
        ];

        $payload = [
            'dev_id'  => $this->devId,
            'X-Token' => $this->xToken,
        ];

        try {
            $response = Http::timeout(6)
                ->connectTimeout(3)
                ->withHeaders($headers)
                ->post($url, $payload);

            if ($response->successful()) {
                $json = $response->json();

                // 1. Ekstrak URL data dari respon
                $dataUrl = $json['data']['info']['urls']['data']
                    ?? $json['data']['urls']['data']
                    ?? $json['url_data']
                    ?? null;
                if ($dataUrl) {
                    Cache::put('campus_api_url_data', $dataUrl, now()->addHours(6));
                    
                    // Token 32 karakter berada di ujung URL data
                    $tokenFromUrl = basename($dataUrl);
                    if (strlen($tokenFromUrl) >= 30) {
                        return $tokenFromUrl;
                    }
                }

                // 2. Atau ekstrak langsung jika ada di atribut token
                $token = $json['token']
                    ?? $json['access_token']
                    ?? $json['data']['token']
                    ?? null;

                if ($token && strlen($token) >= 30) {
                    return $token;
                }
            }

            Log::warning('CampusApiService authorize gagal', [
                'url'      => $url,
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('CampusApiService authorize exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 2. Get Reference Data.
     * Mengambil data referensi dari server kampus dengan Access Token (32 karakter).
     *
     * @param string $filter Nama filter (contoh: 'dosen', 'mahasiswa', 'prodi', 'fakultas')
     * @param array  $options Opsi tambahan (search, pagination, dll)
     */
    public function getReferenceData(string $filter, array $options = []): array
    {
        $token = $this->getAccessToken();

        // Jika gagal mendapatkan token atau belum dikonfigurasi, kembalikan fallback lokal
        if (!$token) {
            return $this->getLocalFallbackData($filter, $options);
        }

        // Tentukan URL data
        $cachedUrlData = Cache::get('campus_api_url_data');
        if (!empty($cachedUrlData)) {
            $url = $cachedUrlData;
        } else {
            $url = "{$this->baseUrl}/data/{$token}";
        }

        $headers = [
            'X-Token'      => $this->xToken,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent'   => $this->userAgent,
        ];

        // Format nama filter ke huruf kecil sesuai dokumentasi API UNUJA
        $filterLower = strtolower($filter);

        $payload = array_merge([
            'filter'     => $filterLower,
            'pagination' => $options['pagination'] ?? 'off',
        ], $options);

        // Jangan kirim options khusus internal
        unset($payload['search_field']);

        try {
            $response = Http::timeout(6)
                ->connectTimeout(3)
                ->withHeaders($headers)
                ->post($url, $payload);

            if ($response->successful()) {
                $json = $response->json();
                $data = $json['data'] ?? [];

                // Jika data berbentuk pagination laravel (current_page, data: [...])
                if (isset($data['data']) && is_array($data['data'])) {
                    return $data['data'];
                }

                if (is_array($data)) {
                    return $data;
                }

                return [];
            }

            // Jika token kadaluarsa (misal 401 Unauthorized), reset token dan coba 1 kali lagi
            if ($response->status() === 401) {
                $freshToken = $this->getAccessToken(true);
                $freshDataUrl = Cache::get('campus_api_url_data') ?? "{$this->baseUrl}/data/{$freshToken}";
                if ($freshToken) {
                    $retryResp = Http::timeout(10)->withHeaders($headers)->post($freshDataUrl, $payload);
                    if ($retryResp->successful()) {
                        $json = $retryResp->json();
                        $data = $json['data'] ?? [];
                        if (isset($data['data']) && is_array($data['data'])) {
                            return $data['data'];
                        }
                        return is_array($data) ? $data : [];
                    }
                }
            }

            Log::warning("CampusApiService getReferenceData [{$filter}] gagal", [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);

            return $this->getLocalFallbackData($filter, $options);
        } catch (\Throwable $e) {
            Log::error("CampusApiService getReferenceData [{$filter}] exception: " . $e->getMessage());
            return $this->getLocalFallbackData($filter, $options);
        }
    }

    /**
     * Ambil Daftar Dosen untuk Dosen Wali & Dosen Pembimbing.
     * Mendukung pencarian dan pemfilteran berdasarkan Program Studi.
     */
    public function getDaftarDosen(?string $search = null, ?string $prodi = null): array
    {
        $params = ['pagination' => 'off'];
        if (!empty($search)) {
            $params['search'] = substr($search, 0, 100);
        }

        $results = $this->getReferenceData('dosen', $params);

        if (empty($results)) {
            $results = $this->getLocalFallbackDosen();
        } else {
            // Format data dosen resmi dari API UNUJA
            $formatted = [];
            foreach ($results as $item) {
                $id   = $item['nidn'] ?? $item['id_penduduk'] ?? $item['id'] ?? null;
                
                // Susun nama lengkap beserta gelar depan dan belakang
                $gelarDepan = trim($item['gelar_depan'] ?? '');
                $nama = trim($item['nama_penduduk'] ?? $item['nama_dosen'] ?? $item['nama_lengkap'] ?? $item['nama'] ?? 'Dosen');
                $gelarBelakang = trim($item['gelar_belakang'] ?? '');

                $namaLengkap = $nama;
                if (!empty($gelarDepan)) {
                    $namaLengkap = $gelarDepan . ' ' . $namaLengkap;
                }
                if (!empty($gelarBelakang)) {
                    $namaLengkap = $namaLengkap . ', ' . $gelarBelakang;
                }

                $nidn = $item['nidn'] ?? $item['nip'] ?? '-';
                $prodiNama = $item['prodi'] ?? $item['nama_prodi'] ?? 'Teknik';

                $formatted[] = [
                    'id'           => $id,
                    'nama_lengkap' => $namaLengkap,
                    'nim_nidn'     => $nidn,
                    'prodi'        => $prodiNama,
                ];
            }
            $results = $formatted;
        }

        // Filter berdasarkan program studi jika diberikan
        if (!empty($prodi)) {
            $results = array_values(array_filter($results, function ($d) use ($prodi) {
                return $this->isProdiMatch($prodi, $d['prodi'] ?? '');
            }));
        }

        return $results;
    }

    /**
     * Ambil Daftar Dosen yang Dikelompokkan:
     * - 'sejurusan': Dosen yang jurusannya cocok dengan mahasiswa
     * - 'lainnya'  : Dosen lintas jurusan
     */
    public function getDaftarDosenGrouped(?string $studentProdi = null, ?string $search = null): array
    {
        $allDosen = $this->getDaftarDosen($search);

        if (empty($studentProdi)) {
            return [
                'prodi_mahasiswa' => null,
                'sejurusan'       => $allDosen,
                'lainnya'         => [],
                'total'           => count($allDosen),
            ];
        }

        $sejurusan = [];
        $lainnya = [];

        foreach ($allDosen as $dosen) {
            if ($this->isProdiMatch($studentProdi, $dosen['prodi'] ?? '')) {
                $sejurusan[] = $dosen;
            } else {
                $lainnya[] = $dosen;
            }
        }

        // Jika tidak ada dosen sejurusan yang cocok sama sekali, jadikan semua dosen sebagai sejurusan agar tidak kosong
        if (empty($sejurusan)) {
            $sejurusan = $allDosen;
            $lainnya   = [];
        }

        return [
            'prodi_mahasiswa' => $studentProdi,
            'sejurusan'       => $sejurusan,
            'lainnya'         => $lainnya,
            'total'           => count($allDosen),
        ];
    }

    /**
     * Helper kecocokan program studi (toleran terhadap perbedaan penulisan seperti 'Informatika' dan 'Teknik Informatika')
     */
    public function isProdiMatch(?string $studentProdi, ?string $dosenProdi): bool
    {
        if (empty($studentProdi) || empty($dosenProdi)) {
            return false;
        }

        $cleanStudent = strtolower(trim(preg_replace('/^(s1|d3|d4|prodi|program\s+studi)\s+/i', '', $studentProdi)));
        $cleanDosen   = strtolower(trim(preg_replace('/^(s1|d3|d4|prodi|program\s+studi)\s+/i', '', $dosenProdi)));

        if ($cleanStudent === $cleanDosen) {
            return true;
        }

        if (str_contains($cleanDosen, $cleanStudent) || str_contains($cleanStudent, $cleanDosen)) {
            return true;
        }

        // Cek kata kunci utama yang bermakna (hilangkan kata umum seperti 'teknik', 'dan')
        $stripCommon = fn($str) => trim(str_replace(['teknik', 'dan', '&', 'studi'], '', $str));
        $keyStudent = $stripCommon($cleanStudent);
        $keyDosen   = $stripCommon($cleanDosen);

        if (!empty($keyStudent) && !empty($keyDosen)) {
            if (str_contains($cleanDosen, $keyStudent) || str_contains($cleanStudent, $keyDosen)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cari Data Mahasiswa berdasarkan NIM dari API Kampus.
     */
    public function getMahasiswaByNim(string $nim): ?array
    {
        $results = $this->getReferenceData('mahasiswa', [
            'search'     => $nim,
            'pagination' => 'on',
        ]);

        if (empty($results)) {
            return null;
        }

        foreach ($results as $item) {
            $itemNim = (string) ($item['nim'] ?? $item['nim_nidn'] ?? '');
            if ($itemNim === (string) $nim) {
                return [
                    'id'           => $item['nim'],
                    'nama_lengkap' => $item['nama'] ?? $item['nama_mahasiswa'] ?? 'Mahasiswa',
                    'nim_nidn'     => $itemNim,
                    'prodi'        => $item['prodi'] ?? 'Teknik',
                    'fakultas'     => $item['fakultas'] ?? 'Fakultas Teknik',
                    'dosen_wali'   => $item['wali'] ?? null,
                    'nidn_wali'    => $item['nidn'] ?? null,
                    'ipk'          => $item['ipk'] ?? null,
                    'semester'     => $item['semester'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Fallback data lokal jika API Kampus belum terhubung/offline.
     */
    protected function getLocalFallbackData(string $filter, array $options = []): array
    {
        if (strtolower($filter) === 'dosen') {
            return $this->getLocalFallbackDosen();
        }

        return [];
    }

    /**
     * Data Dosen Default untuk fallback.
     */
    public function getLocalFallbackDosen(): array
    {
        return [
            [
                'id'           => 4,
                'nama_lengkap' => 'Dr. Budi Santoso, M.Kom',
                'nim_nidn'     => '0412058501',
                'prodi'        => 'Teknik Informatika',
            ],
            [
                'id'           => 5,
                'nama_lengkap' => 'Siti Nurhaliza, M.T.',
                'nim_nidn'     => '0415088802',
                'prodi'        => 'Sistem Informasi',
            ],
            [
                'id'           => 6,
                'nama_lengkap' => 'Prof. Dr. Hendra Wijaya, M.Sc',
                'nim_nidn'     => '0403027501',
                'prodi'        => 'Teknologi Informasi',
            ],
        ];
    }

    /**
     * Hapus Token Cache (untuk tes ulang).
     */
    public function clearTokenCache(): void
    {
        Cache::forget($this->getCacheKey());
        Cache::forget('campus_api_url_data');
    }
}
