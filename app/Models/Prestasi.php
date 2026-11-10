<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('prestasis')]
#[Fillable([
    'mahasiswa_id',
    'dosen_pembimbing_id',
    'judul_prestasi',
    'tingkat',
    'file_sertifikat',
    'status_validasi_dosen',
    'status_validasi_akhir',
    'catatan',
])]
class Prestasi extends Model
{
    use HasFactory;

    // Konstanta Tingkat Lomba
    public const TINGKAT_LOKAL         = 'Lokal';
    public const TINGKAT_PROVINSI      = 'Provinsi';
    public const TINGKAT_NASIONAL      = 'Nasional';
    public const TINGKAT_INTERNASIONAL = 'Internasional';

    // Konstanta Status Validasi
    public const STATUS_MENUNGGU   = 'Menunggu';
    public const STATUS_DISETUJUI  = 'Disetujui';
    public const STATUS_DITOLAK    = 'Ditolak';

    /**
     * Tipe casting atribut standar Laravel 13.
     */
    protected function casts(): array
    {
        return [
            'mahasiswa_id'        => 'integer',
            'dosen_pembimbing_id' => 'integer',
        ];
    }

    /**
     * Helper cek apakah pengajuan klaim prestasi memiliki dosen pembimbing.
     */
    public function hasDosenPembimbing(): bool
    {
        return ! is_null($this->dosen_pembimbing_id);
    }

    /**
     * Helper cek apakah status validasi dosen pembimbing sudah disetujui.
     */
    public function isDisetujuiDosen(): bool
    {
        return $this->status_validasi_dosen === self::STATUS_DISETUJUI;
    }

    /**
     * Helper cek apakah validasi akhir admin sudah disetujui.
     */
    public function isDisetujuiAkhir(): bool
    {
        return $this->status_validasi_akhir === self::STATUS_DISETUJUI;
    }

    /**
     * Validasi aturan bisnis:
     * Admin HANYA BISA menyetujui klaim prestasi (status_validasi_akhir = 'Disetujui') jika:
     * - Mahasiswa TIDAK mencantumkan dosen pembimbing (dosen_pembimbing_id = null), ATAU
     * - Status validasi dosen sudah bernilai 'Disetujui'.
     */
    public function bolehDisetujuiAdmin(): bool
    {
        if (! $this->hasDosenPembimbing()) {
            return true;
        }

        return $this->isDisetujuiDosen();
    }
}

