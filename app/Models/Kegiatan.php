<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('kegiatans')]
#[Fillable([
    'pembuat_id',
    'kategori_id',
    'judul_kegiatan',
    'deskripsi',
    'persyaratan',
    'batas_pendaftaran',
    'kuota_maksimal',
    'kuota_terisi',
    'status_publikasi',
])]
class Kegiatan extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'Draft';
    public const STATUS_PUBLISHED = 'Published';

    /**
     * Tipe casting atribut standar Laravel 13.
     */
    protected function casts(): array
    {
        return [
            'batas_pendaftaran' => 'datetime',
            'kuota_maksimal'    => 'integer',
            'kuota_terisi'      => 'integer',
        ];
    }

    /**
     * Relasi ke Kategori Kegiatan.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriKegiatan::class, 'kategori_id');
    }

    /**
     * Scope untuk menyaring kegiatan yang berstatus 'Published'.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status_publikasi', self::STATUS_PUBLISHED);
    }

    /**
     * Business logic: Cek apakah kuota_terisi masih di bawah kuota_maksimal.
     */
    public function isKuotaTersedia(): bool
    {
        return $this->kuota_terisi < $this->kuota_maksimal;
    }

    /**
     * Business logic: Cek apakah hari ini belum melewati batas pendaftaran.
     */
    public function isPendaftaranBuka(): bool
    {
        return now()->lessThanOrEqualTo($this->batas_pendaftaran);
    }

    /**
     * Business logic: Cek apakah kuota tersedia DAN pendaftaran belum tutup.
     */
    public function isBisaMendaftar(): bool
    {
        return $this->isKuotaTersedia() && $this->isPendaftaranBuka();
    }

    /**
     * Business logic: Hitung sisa slot pendaftaran.
     */
    public function sisaKuota(): int
    {
        return max(0, $this->kuota_maksimal - $this->kuota_terisi);
    }
}
