<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'klasifikasi', 'tahun_aktif', 'tahun_inaktif', 'alert_hari', 'keterangan', 'is_active'])]
class RetentionPolicy extends Model
{
    public static function forKlasifikasi(?string $klasifikasi): self
    {
        $policy = static::query()
            ->where('is_active', true)
            ->where('klasifikasi', $klasifikasi)
            ->first();

        if ($policy) {
            return $policy;
        }

        return static::query()
            ->where('is_active', true)
            ->whereNull('klasifikasi')
            ->firstOrFail();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
