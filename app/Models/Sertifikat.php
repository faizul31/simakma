<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Table('sertifikats')]
#[Fillable([
    'pendaftaran_id',
    'kegiatan_id',
    'mahasiswa_id',
    'nomor_sertifikat',
    'peran',
    'tanggal_terbit',
    'file_sertifikat',
    'status',
])]
class Sertifikat extends Model
{
    use HasFactory;

    // Konstanta Peran
    public const PERAN_PESERTA    = 'Peserta';
    public const PERAN_PANITIA    = 'Panitia';
    public const PERAN_NARASUMBER = 'Narasumber';
    public const PERAN_MODERATOR  = 'Moderator';

    // Konstanta Status
    public const STATUS_VALID   = 'Valid';
    public const STATUS_REVOKED = 'Revoked';

    /**
     * Tipe casting atribut standar Laravel 13.
     */
    protected function casts(): array
    {
        return [
            'pendaftaran_id' => 'integer',
            'kegiatan_id'    => 'integer',
            'mahasiswa_id'   => 'integer',
            'tanggal_terbit' => 'date',
        ];
    }

    /**
     * Generator nomor unik sertifikat resmi kampus.
     * Contoh: CERT/2026/XI/A1B2-0042
     */
    public static function generateNomor(int $kegiatanId): string
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $roman = $romanMonths[(int) date('n')] ?? 'I';

        return 'CERT/' . date('Y') . '/' . $roman . '/KG' . $kegiatanId . '-' . strtoupper(Str::random(4));
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }
}

