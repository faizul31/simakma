<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('pendaftarans')]
#[Fillable([
    'kegiatan_id',
    'mahasiswa_id',
    'dosen_wali_id',
    'file_syarat',
    'status_rekomendasi_dosen',
    'status_pendaftaran',
    'catatan_verifikator',
])]
class Pendaftaran extends Model
{
    use HasFactory;

    /**
     * Otomatis sertakan atribut daftar_berkas saat serialisasi JSON.
     */
    protected $appends = ['daftar_berkas'];

    // Konstanta Status Rekomendasi Dosen
    public const REKOMENDASI_MENUNGGU          = 'Menunggu';
    public const REKOMENDASI_DIREKOMENDASIKAN  = 'Direkomendasikan';
    public const REKOMENDASI_DITOLAK           = 'Ditolak';

    // Konstanta Status Pendaftaran
    public const STATUS_MENUNGGU      = 'Menunggu';
    public const STATUS_LOLOS_BERKAS  = 'Lolos Berkas';
    public const STATUS_DITERIMA      = 'Diterima';
    public const STATUS_REVISI        = 'Revisi';
    public const STATUS_DITOLAK       = 'Ditolak';

    /**
     * Tipe casting atribut standar Laravel 13.
     */
    protected function casts(): array
    {
        return [
            'kegiatan_id'   => 'integer',
            'mahasiswa_id'  => 'integer',
            'dosen_wali_id' => 'integer',
        ];
    }

    /**
     * Helper cek apakah membutuhkan rekomendasi dosen wali.
     */
    public function butuhDosenWali(): bool
    {
        return ! is_null($this->dosen_wali_id);
    }

    /**
     * Helper cek status rekomendasi dosen.
     */
    public function isDirekomendasikan(): bool
    {
        return $this->status_rekomendasi_dosen === self::REKOMENDASI_DIREKOMENDASIKAN;
    }

    /**
     * Helper validasi bisnis:
     * Admin hanya boleh memilih 'Diterima' jika:
     * - Kegiatan tidak membutuhkan dosen wali, ATAU
     * - Status rekomendasi dosen sudah bernilai 'Direkomendasikan'.
     */
    public function bolehDiterima(): bool
    {
        if (! $this->butuhDosenWali()) {
            return true;
        }

        return $this->isDirekomendasikan();
    }

    /**
     * Helper parsing file_syarat ke daftar berkas terstruktur:
     * Mengembalikan associative array: ['Label Syarat' => 'path/file.ext'].
     * Mendukung multi-file JSON maupun format legacy single-file string.
     */
    public function getDaftarBerkasAttribute(): array
    {
        if (empty($this->file_syarat)) {
            return [];
        }

        $raw = trim($this->file_syarat);
        if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return ['Dokumen Syarat' => $this->file_syarat];
    }
}
