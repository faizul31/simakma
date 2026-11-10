<?php

namespace App\Http\Controllers;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KegiatanController extends Controller
{
    /**
     * [Mahasiswa/Publik] Tampilkan hanya kegiatan yang berstatus 'Published'.
     * Endpoint: GET /api/kegiatan
     */
    public function index(): JsonResponse
    {
        $kegiatans = Kegiatan::with('kategori')
            ->published()
            ->orderBy('batas_pendaftaran', 'asc')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar kegiatan yang dipublikasikan berhasil diambil.',
            'total'   => $kegiatans->count(),
            'data'    => $kegiatans,
        ], 200);
    }

    /**
     * [Admin/Ormawa] Tampilkan semua kegiatan, termasuk 'Draft'.
     * Endpoint: GET /api/kegiatan/semua
     */
    public function semua(?Request $request = null): JsonResponse
    {
        $request = $request ?? request();
        $query = Kegiatan::with('kategori');

        // Filter opsional: status_publikasi
        if ($request->filled('status_publikasi')) {
            $query->where('status_publikasi', $request->input('status_publikasi'));
        }

        // Filter opsional: kategori_id
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->input('kategori_id'));
        }

        // Filter opsional: pembuat_id
        if ($request->filled('pembuat_id')) {
            $query->where('pembuat_id', $request->input('pembuat_id'));
        }

        $kegiatans = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Seluruh data kegiatan berhasil diambil.',
            'total'   => $kegiatans->count(),
            'data'    => $kegiatans,
        ], 200);
    }

    /**
     * [Admin/Ormawa] Buat kegiatan baru.
     * Endpoint: POST /api/kegiatan
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pembuat_id'        => ['required', 'integer'],
            'kategori_id'       => ['required', 'integer', 'exists:kategori_kegiatans,id'],
            'judul_kegiatan'    => ['required', 'string', 'max:255'],
            'deskripsi'         => ['required', 'string'],
            'persyaratan'       => ['nullable', 'string'],
            'batas_pendaftaran' => ['required', 'date'],
            'kuota_maksimal'    => ['required', 'integer', 'min:1'],
            'status_publikasi'  => ['nullable', 'in:Draft,Published'],
        ], [
            'pembuat_id.required'        => 'ID Pembuat kegiatan wajib diisi.',
            'kategori_id.required'       => 'Kategori kegiatan wajib dipilih.',
            'kategori_id.exists'         => 'Kategori kegiatan tidak ditemukan.',
            'judul_kegiatan.required'    => 'Judul kegiatan wajib diisi.',
            'deskripsi.required'         => 'Deskripsi kegiatan wajib diisi.',
            'batas_pendaftaran.required' => 'Batas tanggal pendaftaran wajib diisi.',
            'kuota_maksimal.required'    => 'Kuota maksimal pendaftaran wajib diisi.',
            'kuota_maksimal.min'         => 'Kuota maksimal minimal bernilai 1.',
            'status_publikasi.in'        => 'Status publikasi harus bernilai Draft atau Published.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['kuota_terisi'] = 0;
        $data['status_publikasi'] = $data['status_publikasi'] ?? Kegiatan::STATUS_DRAFT;

        $kegiatan = Kegiatan::create($data)->load('kategori');

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan baru berhasil dibuat.',
            'data'    => $kegiatan,
        ], 201);
    }

    /**
     * [Umum] Tampilkan detail satu kegiatan.
     * Endpoint: GET /api/kegiatan/{id}
     */
    public function show(int $id): JsonResponse
    {
        $kegiatan = Kegiatan::with('kategori')->find($id);

        if (! $kegiatan) {
            return response()->json([
                'status'  => 'error',
                'message' => "Kegiatan dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail kegiatan berhasil diambil.',
            'data'    => $kegiatan,
        ], 200);
    }

    /**
     * [Admin/Ormawa] Perbarui data kegiatan.
     * Endpoint: PUT /api/kegiatan/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $kegiatan = Kegiatan::find($id);

        if (! $kegiatan) {
            return response()->json([
                'status'  => 'error',
                'message' => "Kegiatan dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kategori_id'       => ['sometimes', 'required', 'integer', 'exists:kategori_kegiatans,id'],
            'judul_kegiatan'    => ['sometimes', 'required', 'string', 'max:255'],
            'deskripsi'         => ['sometimes', 'required', 'string'],
            'persyaratan'       => ['sometimes', 'nullable', 'string'],
            'batas_pendaftaran' => ['sometimes', 'required', 'date'],
            'kuota_maksimal'    => ['sometimes', 'required', 'integer', 'min:' . $kegiatan->kuota_terisi],
            'status_publikasi'  => ['sometimes', 'required', 'in:Draft,Published'],
        ], [
            'kuota_maksimal.min' => "Kuota maksimal tidak boleh lebih kecil dari kuota yang sudah terisi ({$kegiatan->kuota_terisi}).",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $kegiatan->update($validator->validated());
        $kegiatan->load('kategori');

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil diperbarui.',
            'data'    => $kegiatan,
        ], 200);
    }

    /**
     * [Admin/Ormawa] Hapus kegiatan.
     * Endpoint: DELETE /api/kegiatan/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $kegiatan = Kegiatan::find($id);

        if (! $kegiatan) {
            return response()->json([
                'status'  => 'error',
                'message' => "Kegiatan dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $kegiatan->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kegiatan berhasil dihapus.',
        ], 200);
    }

    /**
     * [ENDPOINT INTERNAL KHUSUS MICROSERVICES]
     * Endpoint: GET /api/kegiatan/{id}/cek-kuota
     *
     * Logika:
     * Cari kegiatan berdasarkan ID.
     * Cek jika kuota_terisi < kuota_maksimal DAN tanggal hari ini belum melewati batas_pendaftaran:
     * - Jika YA: return response()->json(['tersedia' => true, 'kegiatan' => $kegiatan], 200)
     * - Jika TIDAK: return response()->json(['tersedia' => false, 'pesan' => 'Kuota penuh atau pendaftaran ditutup'], 400)
     */
    public function cekKuota(int $id): JsonResponse
    {
        $kegiatan = Kegiatan::with('kategori')->find($id);

        if (! $kegiatan) {
            return response()->json([
                'status'   => 'error',
                'tersedia' => false,
                'pesan'    => "Kegiatan dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        // Cek kondisi: kuota_terisi < kuota_maksimal DAN belum lewat batas pendaftaran
        $kuotaMasihAda = $kegiatan->kuota_terisi < $kegiatan->kuota_maksimal;
        $belumLewatBatas = now()->lessThanOrEqualTo($kegiatan->batas_pendaftaran);

        if ($kuotaMasihAda && $belumLewatBatas) {
            return response()->json([
                'tersedia' => true,
                'kegiatan' => $kegiatan,
            ], 200);
        }

        return response()->json([
            'tersedia' => false,
            'pesan'    => 'Kuota penuh atau pendaftaran ditutup',
        ], 400);
    }

    /**
     * [ENDPOINT INTERNAL KHUSUS MICROSERVICES]
     * Endpoint: POST /api/kegiatan/{id}/tambah-kuota-terisi
     *
     * Logika:
     * Menambah nilai kuota_terisi + 1 setiap kali ada pendaftar yang sukses dari Pendaftaran Service.
     */
    public function tambahKuotaTerisi(int $id): JsonResponse
    {
        $kegiatan = Kegiatan::find($id);

        if (! $kegiatan) {
            return response()->json([
                'status'  => 'error',
                'message' => "Kegiatan dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        if ($kegiatan->kuota_terisi >= $kegiatan->kuota_maksimal) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kuota kegiatan sudah maksimal, tidak dapat menambah kuota terisi.',
            ], 400);
        }

        // Atomic increment untuk mencegah race conditions saat banyak pendaftar simultan
        $kegiatan->increment('kuota_terisi');
        $kegiatan->refresh();

        return response()->json([
            'status'         => 'success',
            'message'        => 'Kuota terisi berhasil ditambah.',
            'kegiatan_id'    => $kegiatan->id,
            'kuota_terisi'   => $kegiatan->kuota_terisi,
            'kuota_maksimal' => $kegiatan->kuota_maksimal,
            'sisa_kuota'     => $kegiatan->sisaKuota(),
        ], 200);
    }

    /**
     * [Master Kategori] Ambil daftar seluruh kategori kegiatan.
     * Endpoint: GET /api/kegiatan/kategori
     */
    public function kategoriIndex(): JsonResponse
    {
        $kategoris = KategoriKegiatan::orderBy('nama_kategori', 'asc')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar kategori berhasil diambil.',
            'total'   => $kategoris->count(),
            'data'    => $kategoris,
        ], 200);
    }

    /**
     * [Master Kategori] Tambah kategori kegiatan baru.
     * Endpoint: POST /api/kegiatan/kategori
     */
    public function kategoriStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:kategori_kegiatans,nama_kategori'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Kategori ini sudah ada sebelumnya.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $kategori = KategoriKegiatan::create([
            'nama_kategori' => $request->input('nama_kategori'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori kegiatan baru berhasil ditambahkan.',
            'data'    => $kategori,
        ], 201);
    }
}
