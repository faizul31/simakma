<?php

namespace App\Http\Controllers;

use App\Models\Prestasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PrestasiController extends Controller
{
    /**
     * A. [Khusus Mahasiswa] Mengunggah klaim prestasi dan file sertifikat untuk SKPI.
     * Endpoint: POST /api/prestasi
     *
     * File sertifikat disimpan ke: storage/app/public/sertifikat
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mahasiswa_id'        => ['required', 'integer'],
            'dosen_pembimbing_id' => ['nullable'],
            'judul_prestasi'      => ['required', 'string', 'max:255'],
            'tingkat'             => ['required', 'in:Lokal,Provinsi,Nasional,Internasional'],
            'file_sertifikat'     => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Maksimal 5MB
        ], [
            'mahasiswa_id.required'    => 'ID Mahasiswa wajib diisi.',
            'judul_prestasi.required'  => 'Judul prestasi / nama kejuaraan lomba wajib diisi.',
            'tingkat.required'         => 'Tingkat kejuaraan lomba wajib dipilih.',
            'tingkat.in'               => 'Tingkat lomba harus salah satu dari: Lokal, Provinsi, Nasional, atau Internasional.',
            'file_sertifikat.required' => 'File sertifikat lomba wajib diunggah.',
            'file_sertifikat.file'     => 'File sertifikat harus berupa dokumen atau gambar yang valid.',
            'file_sertifikat.mimes'    => 'Format file sertifikat harus berupa PDF, JPG, JPEG, atau PNG.',
            'file_sertifikat.max'      => 'Ukuran file sertifikat maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Upload file sertifikat ke direktori: storage/app/public/sertifikat
        $filePath = $request->file('file_sertifikat')->store('sertifikat', 'public');

        $prestasi = Prestasi::create([
            'mahasiswa_id'          => (int) $request->input('mahasiswa_id'),
            'dosen_pembimbing_id'   => ($request->filled('dosen_pembimbing_id') && is_numeric($request->input('dosen_pembimbing_id'))) ? (int) $request->input('dosen_pembimbing_id') : null,
            'judul_prestasi'        => $request->input('judul_prestasi'),
            'tingkat'               => $request->input('tingkat'),
            'file_sertifikat'       => $filePath,
            'status_validasi_dosen' => Prestasi::STATUS_MENUNGGU,
            'status_validasi_akhir' => Prestasi::STATUS_MENUNGGU,
            'catatan'               => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengajuan klaim prestasi lomba berhasil diunggah.',
            'data'    => $prestasi,
        ], 201);
    }

    /**
     * B. [Mahasiswa] Menampilkan daftar prestasi yang diajukan oleh seorang mahasiswa.
     * Endpoint: GET /api/prestasi/mahasiswa/{mahasiswa_id}
     *
     * Berfungsi sebagai rekap portofolio mahasiswa untuk SKPI.
     */
    public function riwayatMahasiswa(int $mahasiswa_id): JsonResponse
    {
        $prestasis = Prestasi::where('mahasiswa_id', $mahasiswa_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status'       => 'success',
            'message'      => 'Daftar portofolio prestasi mahasiswa berhasil diambil.',
            'mahasiswa_id' => $mahasiswa_id,
            'total'        => $prestasis->count(),
            'data'         => $prestasis,
        ], 200);
    }

    /**
     * C. [Khusus Dosen] Validasi klaim prestasi oleh Dosen Pembimbing.
     * Endpoint: PUT /api/prestasi/{id}/validasi-dosen
     *
     * Aturan Bisnis:
     * Mengubah status_validasi_dosen menjadi 'Disetujui' atau 'Ditolak'.
     * JIKA DITOLAK, WAJIB mengisi kolom catatan sebagai alasan penolakan.
     */
    public function validasiDosen(Request $request, int $id): JsonResponse
    {
        $prestasi = Prestasi::find($id);

        if (! $prestasi) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data prestasi dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_validasi_dosen' => ['required', 'in:Disetujui,Ditolak'],
            'catatan'               => ['required_if:status_validasi_dosen,Ditolak', 'nullable', 'string'],
        ], [
            'status_validasi_dosen.required' => 'Status validasi dosen wajib dipilih.',
            'status_validasi_dosen.in'       => 'Status validasi dosen harus bernilai Disetujui atau Ditolak.',
            'catatan.required_if'            => 'Kolom catatan wajib diisi jika dosen menolak pengajuan prestasi ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $prestasi->update([
            'status_validasi_dosen' => $request->input('status_validasi_dosen'),
            'catatan'               => $request->input('catatan', $prestasi->catatan),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Validasi prestasi oleh dosen pembimbing berhasil disimpan.',
            'data'    => $prestasi,
        ], 200);
    }

    /**
     * D. [Khusus Admin Kemahasiswaan] Validasi Akhir Pengajuan Prestasi.
     * Endpoint: PUT /api/prestasi/{id}/validasi-admin
     *
     * Validasi Bisnis Penting:
     * Admin HANYA BISA menyetujui (status_validasi_akhir = 'Disetujui') jika:
     * - Mahasiswa tidak mencantumkan dosen pembimbing (dosen_pembimbing_id = null), ATAU
     * - Status validasi dosen sudah bernilai 'Disetujui'.
     */
    public function validasiAdmin(Request $request, int $id): JsonResponse
    {
        $prestasi = Prestasi::find($id);

        if (! $prestasi) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data prestasi dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_validasi_akhir' => ['required', 'in:Disetujui,Ditolak'],
            'catatan'               => ['nullable', 'string'],
        ], [
            'status_validasi_akhir.required' => 'Status validasi akhir wajib dipilih.',
            'status_validasi_akhir.in'       => 'Status validasi akhir harus bernilai Disetujui atau Ditolak.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $statusAkhirBaru = $request->input('status_validasi_akhir');

        // Validasi Aturan Bisnis: Cek persetujuan dosen sebelum admin menyetujui
        if ($statusAkhirBaru === Prestasi::STATUS_DISETUJUI) {
            if (! $prestasi->bolehDisetujuiAdmin()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Admin tidak dapat menyetujui prestasi ini karena belum divalidasi dan disetujui oleh dosen pembimbing.',
                ], 422);
            }
        }

        $prestasi->update([
            'status_validasi_akhir' => $statusAkhirBaru,
            'catatan'               => $request->input('catatan', $prestasi->catatan),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Validasi akhir kemahasiswaan berhasil disimpan.',
            'data'    => $prestasi,
        ], 200);
    }

    /**
     * [Admin/Ormawa] Tampilkan seluruh daftar prestasi.
     * Endpoint: GET /api/prestasi
     */
    public function index(Request $request): JsonResponse
    {
        $query = Prestasi::query();

        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->input('tingkat'));
        }

        if ($request->filled('status_validasi_akhir')) {
            $query->where('status_validasi_akhir', $request->input('status_validasi_akhir'));
        }

        $prestasis = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar seluruh klaim prestasi berhasil diambil.',
            'total'   => $prestasis->count(),
            'data'    => $prestasis,
        ], 200);
    }

    /**
     * [Umum] Tampilkan detail satu data prestasi.
     * Endpoint: GET /api/prestasi/{id}
     */
    public function show(int $id): JsonResponse
    {
        $prestasi = Prestasi::find($id);

        if (! $prestasi) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data prestasi dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail data prestasi berhasil diambil.',
            'data'    => $prestasi,
        ], 200);
    }
}

