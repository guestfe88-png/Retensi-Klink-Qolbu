<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'klasifikasi', 'tahun_aktif', 'tahun_inaktif', 'alert_hari', 'keterangan', 'is_active'])]
class RetentionPolicy extends Model
{
    public const RAWAT_JALAN_KLASIFIKASI = Berkas::DEFAULT_KLASIFIKASI;

    public static function forKlasifikasi(?string $klasifikasi = null): self
    {
        return static::query()
            ->where('is_active', true)
            ->where('klasifikasi', self::RAWAT_JALAN_KLASIFIKASI)
            ->firstOrFail();
    }

    public function scopeRawatJalan(Builder $query): Builder
    {
        return $query->where('klasifikasi', self::RAWAT_JALAN_KLASIFIKASI);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
