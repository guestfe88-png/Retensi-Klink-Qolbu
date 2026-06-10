<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['berkas_id', 'certificate_number', 'approved_by', 'destroyed_at', 'catatan'])]
class DestructionCertificate extends Model
{
    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected function casts(): array
    {
        return [
            'destroyed_at' => 'datetime',
        ];
    }
}
