<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('kategori_kegiatans')]
#[Fillable([
    'nama_kategori',
])]
class KategoriKegiatan extends Model
{
    use HasFactory;

    /**
     * Relasi One-to-Many ke model Kegiatan.
     */
    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class, 'kategori_id');
    }
}
