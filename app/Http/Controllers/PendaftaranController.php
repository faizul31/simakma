<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PendaftaranController extends Controller
{
    /**
     * URL dasar Kegiatan Service (Port 8002) untuk komunikasi inter-service.
     */
    protected function getKegiatanServiceUrl(): string
    {
        return env('KEGIATAN_SERVICE_URL', 'http://localhost:8002');
    }

    /**
     * A. [Mahasiswa] Pendaftaran Kegiatan Baru.
     * Endpoint: POST /api/daftar
     *
     * Alur Komunikasi Antar-Service:
     * 1. Validasi input request.
     * 2. Cek apakah mahasiswa sudah mendaftar kegiatan yang sama.
     * 3. GET Request ke Kegiatan Service: http://localhost:8002/api/kegiatan/{kegiatan_id}/cek-kuota.
     *    - Jika API merespons tidak tersedia (400 atau false):
     *      Kembalikan: "Pendaftaran gagal, kuota penuh atau batas waktu habis" (400).
     * 4. Jika tersedia (200):
     *    - Upload file_syarat (jika dilampirkan) ke storage.
     *    - Simpan data pendaftaran ke tabel pendaftarans di db_pendaftaran.
     * 5. POST Request ke Kegiatan Service: http://localhost:8002/api/kegiatan/{kegiatan_id}/tambah-kuota-terisi
     *    untuk menambah kuota_terisi (+1).
     */
    public function daftar(Request $request): JsonResponse
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'kegiatan_id'    => ['required', 'integer'],
            'mahasiswa_id'   => ['required', 'integer'],
            'dosen_wali_id'  => ['nullable'],
            'file_syarat'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
            'files_syarat'   => ['nullable', 'array'],
            'files_syarat.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'syarat_labels'  => ['nullable', 'array'],
        ], [
            'kegiatan_id.required'  => 'ID Kegiatan wajib diisi.',
            'mahasiswa_id.required' => 'ID Mahasiswa wajib diisi.',
            'file_syarat.file'      => 'File syarat harus berupa dokumen atau gambar.',
            'file_syarat.mimes'     => 'Format file syarat harus berupa pdf, jpg, jpeg, atau png.',
            'file_syarat.max'       => 'Ukuran file syarat maksimal 5MB.',
            'files_syarat.*.file'   => 'Berkas syarat harus berupa dokumen atau gambar.',
            'files_syarat.*.mimes'  => 'Format berkas syarat harus berupa pdf, jpg, jpeg, atau png.',
            'files_syarat.*.max'    => 'Ukuran berkas syarat maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $kegiatanId = (int) $request->input('kegiatan_id');
        $mahasiswaId = (int) $request->input('mahasiswa_id');

        // 2. Cek apakah mahasiswa sudah pernah mendaftar kegiatan ini
        $sudahMendaftar = Pendaftaran::where('mahasiswa_id', $mahasiswaId)
            ->where('kegiatan_id', $kegiatanId)
            ->exists();

        if ($sudahMendaftar) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mahasiswa sudah terdaftar pada kegiatan ini sebelumnya.',
            ], 409);
        }

        // 3. Inter-Service Communication: GET Request cek kuota ke Kegiatan Service (Port 8002)
        $kegiatanUrl = $this->getKegiatanServiceUrl();
        $tersedia = false;

        try {
            $cekKuotaResponse = Http::timeout(2)->get("{$kegiatanUrl}/api/kegiatan/{$kegiatanId}/cek-kuota");
            $tersedia = ($cekKuotaResponse->status() === 200 && $cekKuotaResponse->json('tersedia'));
        } catch (\Throwable $e) {
            // Smart Fallback In-Process jika Kegiatan Service (Port 8002) belum dinyalakan terpisah
            $localResp = app(KegiatanController::class)->cekKuota($kegiatanId);
            $localData = $localResp->getData(true);
            $tersedia = ($localResp->getStatusCode() === 200 && !empty($localData['tersedia']));
        }

        // Jika respons API mengatakan tidak tersedia
        if (! $tersedia) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pendaftaran gagal, kuota penuh atau batas waktu habis',
            ], 400);
        }

        // 4. Jika kuota tersedia (200), upload berkas syarat
        $filePath = null;

        if ($request->hasFile('files_syarat')) {
            $files = $request->file('files_syarat');
            $labels = $request->input('syarat_labels', []);
            $uploadedMap = [];

            foreach ($files as $idx => $f) {
                if ($f && $f->isValid()) {
                    $stored = $f->store('syarat_pendaftaran', 'public');
                    $label = !empty($labels[$idx]) ? trim($labels[$idx]) : ('Dokumen Syarat ' . ($idx + 1));
                    $uploadedMap[$label] = $stored;
                }
            }

            if (!empty($uploadedMap)) {
                $filePath = json_encode($uploadedMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        } elseif ($request->hasFile('file_syarat')) {
            $filePath = $request->file('file_syarat')->store('syarat_pendaftaran', 'public');
        }

        // Simpan pendaftaran ke database db_pendaftaran
        $pendaftaran = Pendaftaran::create([
            'kegiatan_id'              => $kegiatanId,
            'mahasiswa_id'             => $mahasiswaId,
            'dosen_wali_id'            => ($request->filled('dosen_wali_id') && is_numeric($request->input('dosen_wali_id'))) ? (int) $request->input('dosen_wali_id') : null,
            'file_syarat'              => $filePath,
            'status_rekomendasi_dosen' => Pendaftaran::REKOMENDASI_MENUNGGU,
            'status_pendaftaran'       => Pendaftaran::STATUS_MENUNGGU,
            'catatan_verifikator'      => null,
        ]);

        // 5. Inter-Service Communication: POST Request untuk memotong kuota acara (+1 kuota_terisi)
        try {
            Http::timeout(2)->post("{$kegiatanUrl}/api/kegiatan/{$kegiatanId}/tambah-kuota-terisi");
        } catch (\Throwable $e) {
            // Smart Fallback In-Process jika Kegiatan Service (Port 8002) belum dinyalakan terpisah
            app(KegiatanController::class)->tambahKuotaTerisi($kegiatanId);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran berhasil dilakukan.',
            'data'    => $pendaftaran,
        ], 201);
    }

    /**
     * B. [Khusus Dosen] Mengubah status rekomendasi dosen wali.
     * Endpoint: PUT /api/pendaftaran/{id}/rekomendasi
     * Nilai: 'Direkomendasikan' atau 'Ditolak'
     */
    public function rekomendasi(Request $request, int $id): JsonResponse
    {
        $pendaftaran = Pendaftaran::find($id);

        if (! $pendaftaran) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data pendaftaran dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_rekomendasi_dosen' => ['required', 'in:Direkomendasikan,Ditolak'],
        ], [
            'status_rekomendasi_dosen.required' => 'Status rekomendasi dosen wajib dipilih.',
            'status_rekomendasi_dosen.in'       => 'Status rekomendasi harus bernilai Direkomendasikan atau Ditolak.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pendaftaran->update([
            'status_rekomendasi_dosen' => $request->input('status_rekomendasi_dosen'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Rekomendasi dosen berhasil diperbarui.',
            'data'    => $pendaftaran,
        ], 200);
    }

    /**
     * C. [Khusus Admin] Verifikasi Akhir Pendaftaran.
     * Endpoint: PUT /api/pendaftaran/{id}/verifikasi-akhir
     *
     * Validasi Penting:
     * Admin HANYA BISA memilih status 'Diterima' jika status_rekomendasi_dosen
     * sudah bernilai 'Direkomendasikan' (kecuali kegiatan yang tidak memiliki dosen wali / dosen_wali_id = null).
     */
    public function verifikasiAkhir(Request $request, int $id): JsonResponse
    {
        $pendaftaran = Pendaftaran::find($id);

        if (! $pendaftaran) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data pendaftaran dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_pendaftaran'  => ['required', 'in:Menunggu,Lolos Berkas,Diterima,Revisi,Ditolak'],
            'catatan_verifikator' => ['nullable', 'string'],
        ], [
            'status_pendaftaran.required' => 'Status pendaftaran wajib dipilih.',
            'status_pendaftaran.in'       => 'Status pendaftaran harus bernilai: Menunggu, Lolos Berkas, Diterima, Revisi, atau Ditolak.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $statusBaru = $request->input('status_pendaftaran');

        // Validasi Bisnis: Jika status diubah menjadi 'Diterima'
        if ($statusBaru === Pendaftaran::STATUS_DITERIMA) {
            if (! $pendaftaran->bolehDiterima()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pendaftaran belum bisa diterima karena belum direkomendasikan oleh dosen wali.',
                ], 422);
            }
        }

        $pendaftaran->update([
            'status_pendaftaran'  => $statusBaru,
            'catatan_verifikator' => $request->input('catatan_verifikator'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Verifikasi akhir pendaftaran berhasil disimpan.',
            'data'    => $pendaftaran,
        ], 200);
    }

    /**
     * D. [Mahasiswa] Mengembalikan riwayat pendaftaran seorang mahasiswa.
     * Endpoint: GET /api/pendaftaran/mahasiswa/{mahasiswa_id}
     */
    public function riwayatMahasiswa(int $mahasiswa_id): JsonResponse
    {
        $riwayat = Pendaftaran::where('mahasiswa_id', $mahasiswa_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status'       => 'success',
            'message'      => 'Riwayat pendaftaran mahasiswa berhasil diambil.',
            'mahasiswa_id' => $mahasiswa_id,
            'total'        => $riwayat->count(),
            'data'         => $riwayat,
        ], 200);
    }

    /**
     * E. [Dosen / Admin] Menampilkan daftar seluruh pendaftaran mahasiswa.
     * Endpoint: GET /api/pendaftaran
     * Parameter filter opsional: dosen_wali_id, status_rekomendasi_dosen, status_pendaftaran.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pendaftaran::query();

        if ($request->filled('dosen_wali_id')) {
            $query->where('dosen_wali_id', (int) $request->input('dosen_wali_id'));
        }

        if ($request->filled('status_rekomendasi_dosen')) {
            $query->where('status_rekomendasi_dosen', $request->input('status_rekomendasi_dosen'));
        }

        if ($request->filled('status_pendaftaran')) {
            $query->where('status_pendaftaran', $request->input('status_pendaftaran'));
        }

        $pendaftarans = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar data pendaftaran berhasil diambil.',
            'total'   => $pendaftarans->count(),
            'data'    => $pendaftarans,
        ], 200);
    }
}
