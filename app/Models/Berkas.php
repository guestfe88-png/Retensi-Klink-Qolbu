<?php

namespace App\Models;

use Database\Factories\BerkasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'patient_id', 'no_rm', 'nama_pasien', 'tgl_lahir', 'alamat', 'nama_berkas',
    'klasifikasi', 'lokasi_arsip', 'file_pdf', 'status', 'tgl_kunjungan_terakhir',
    'tgl_retensi', 'legal_hold', 'destruction_status', 'approved_by', 'approved_at',
    'keterangan', 'created_by',
])]
class Berkas extends Model
{
    /** @use HasFactory<BerkasFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'berkas';

    public const KLASIFIKASI = [
        'rawat_jalan' => 'Rawat Jalan',
    ];

    public const DEFAULT_KLASIFIKASI = 'rawat_jalan';

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function destructionCertificate(): HasOne
    {
        return $this->hasOne(DestructionCertificate::class);
    }

    public function getUsiaAttribute(): ?int
    {
        if (! $this->tgl_lahir) {
            return null;
        }

        return $this->tgl_lahir->age;
    }

    public function getKlasifikasiLabelAttribute(): string
    {
        return self::KLASIFIKASI[$this->klasifikasi] ?? $this->klasifikasi;
    }

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
            'tgl_kunjungan_terakhir' => 'date',
            'tgl_retensi' => 'date',
            'legal_hold' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }
}
