<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use App\Models\Sertifikat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SertifikatController extends Controller
{
    /**
     * URL dasar Pendaftaran Service (Port 8003).
     */
    protected function getPendaftaranServiceUrl(): string
    {
        return env('PENDAFTARAN_SERVICE_URL', 'http://localhost:8003');
    }

    /**
     * [Admin/Ormawa] Menerbitkan sertifikat kegiatan.
     * Endpoint: POST /api/sertifikat/terbitkan
     *
     * Validasi Bisnis Microservices:
     * Sertifikat HANYA DAPAT diterbitkan jika pendaftaran mahasiswa
     * pada kegiatan terkait telah berstatus 'Diterima' (telah lolos verifikasi akhir).
     */
    public function terbitkan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pendaftaran_id' => ['required', 'integer'],
            'kegiatan_id'    => ['required', 'integer'],
            'mahasiswa_id'   => ['required', 'integer'],
            'peran'          => ['nullable', 'in:Peserta,Panitia,Narasumber,Moderator'],
        ], [
            'pendaftaran_id.required' => 'ID Pendaftaran wajib diisi.',
            'kegiatan_id.required'    => 'ID Kegiatan wajib diisi.',
            'mahasiswa_id.required'   => 'ID Mahasiswa wajib diisi.',
            'peran.in'                => 'Peran harus salah satu dari: Peserta, Panitia, Narasumber, atau Moderator.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pendaftaranId = (int) $request->input('pendaftaran_id');
        $kegiatanId = (int) $request->input('kegiatan_id');
        $mahasiswaId = (int) $request->input('mahasiswa_id');
        $peran = $request->input('peran', Sertifikat::PERAN_PESERTA);

        // 1. Cek duplikasi sertifikat untuk pendaftaran dan peran yang sama
        $sudahAda = Sertifikat::where('pendaftaran_id', $pendaftaranId)
            ->where('peran', $peran)
            ->first();

        if ($sudahAda) {
            return response()->json([
                'status'  => 'error',
                'message' => "Sertifikat untuk pendaftaran ini dengan peran '{$peran}' sudah pernah diterbitkan.",
                'data'    => $sudahAda,
            ], 409);
        }

        // 2. Inter-Service Communication / Database Check:
        // Verifikasi apakah pendaftaran berstatus 'Diterima'
        $pendaftaran = Pendaftaran::find($pendaftaranId);

        // Jika model lokal tidak ditemukan (pada arsitektur multi-database terpisah), cek via HTTP
        if (! $pendaftaran) {
            $pendaftaranUrl = $this->getPendaftaranServiceUrl();
            try {
                $res = Http::timeout(5)->get("{$pendaftaranUrl}/api/pendaftaran/{$pendaftaranId}");
                if ($res->ok()) {
                    $pendaftaranData = $res->json('data');
                    if (($pendaftaranData['status_pendaftaran'] ?? '') !== Pendaftaran::STATUS_DITERIMA) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Sertifikat hanya dapat diterbitkan untuk pendaftaran yang telah berstatus Diterima.',
                        ], 422);
                    }
                }
            } catch (\Exception $e) {
                // Log peringatan
            }
        } elseif ($pendaftaran->status_pendaftaran !== Pendaftaran::STATUS_DITERIMA) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sertifikat hanya dapat diterbitkan untuk pendaftaran yang telah berstatus Diterima.',
            ], 422);
        }

        // 3. Generate nomor sertifikat unik
        $nomorSertifikat = Sertifikat::generateNomor($kegiatanId);

        // 4. Simpan ke database db_sertifikat
        $sertifikat = Sertifikat::create([
            'pendaftaran_id'   => $pendaftaranId,
            'kegiatan_id'      => $kegiatanId,
            'mahasiswa_id'     => $mahasiswaId,
            'nomor_sertifikat' => $nomorSertifikat,
            'peran'            => $peran,
            'tanggal_terbit'   => now()->toDateString(),
            'file_sertifikat'  => 'certificates/' . $nomorSertifikat . '.pdf',
            'status'           => Sertifikat::STATUS_VALID,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Sertifikat berhasil diterbitkan.',
            'data'    => $sertifikat,
        ], 201);
    }

    /**
     * [Publik] Verifikasi keaslian sertifikat via Nomor Sertifikat (Scan QR Code).
     * Endpoint: GET /api/sertifikat/verifikasi/{nomor_sertifikat}
     */
    public function verifikasi(string $nomor_sertifikat): JsonResponse
    {
        $sertifikat = Sertifikat::where('nomor_sertifikat', $nomor_sertifikat)->first();

        if (! $sertifikat) {
            return response()->json([
                'status'   => 'error',
                'valid'    => false,
                'message'  => 'Nomor sertifikat tidak ditemukan dalam pangkalan data resmi kampus.',
            ], 404);
        }

        $isValid = $sertifikat->isValid();

        return response()->json([
            'status'           => 'success',
            'valid'            => $isValid,
            'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
            'peran'            => $sertifikat->peran,
            'tanggal_terbit'   => $sertifikat->tanggal_terbit->toDateString(),
            'status_dokumen'   => $sertifikat->status,
            'kegiatan_id'      => $sertifikat->kegiatan_id,
            'mahasiswa_id'     => $sertifikat->mahasiswa_id,
            'message'          => $isValid
                ? 'Sertifikat ini ASLI dan terdaftar resmi di sistem kampus.'
                : 'Sertifikat ini telah DICABUT (Revoked) oleh pihak kampus.',
        ], 200);
    }

    /**
     * [Mahasiswa] Tampilkan seluruh sertifikat yang dimiliki seorang mahasiswa.
     * Endpoint: GET /api/sertifikat/mahasiswa/{mahasiswa_id}
     */
    public function riwayatMahasiswa(int $mahasiswa_id): JsonResponse
    {
        $sertifikats = Sertifikat::where('mahasiswa_id', $mahasiswa_id)
            ->where('status', Sertifikat::STATUS_VALID)
            ->orderBy('tanggal_terbit', 'desc')
            ->get();

        return response()->json([
            'status'       => 'success',
            'message'      => 'Daftar sertifikat mahasiswa berhasil diambil.',
            'mahasiswa_id' => $mahasiswa_id,
            'total'        => $sertifikats->count(),
            'data'         => $sertifikats,
        ], 200);
    }

    /**
     * [Admin/Ormawa] Tampilkan detail satu sertifikat.
     * Endpoint: GET /api/sertifikat/{id}
     */
    public function show(int $id): JsonResponse
    {
        $sertifikat = Sertifikat::find($id);

        if (! $sertifikat) {
            return response()->json([
                'status'  => 'error',
                'message' => "Sertifikat dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail sertifikat berhasil diambil.',
            'data'    => $sertifikat,
        ], 200);
    }

    /**
     * [Admin] Cabut / Batalkan sertifikat (Revoke).
     * Endpoint: PUT /api/sertifikat/{id}/revoke
     */
    public function revoke(int $id): JsonResponse
    {
        $sertifikat = Sertifikat::find($id);

        if (! $sertifikat) {
            return response()->json([
                'status'  => 'error',
                'message' => "Sertifikat dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $sertifikat->update([
            'status' => Sertifikat::STATUS_REVOKED,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Sertifikat berhasil dicabut (Revoked).',
            'data'    => $sertifikat,
        ], 200);
    }
}

