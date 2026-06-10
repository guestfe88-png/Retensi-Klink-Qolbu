<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['no_rm', 'nama_pasien', 'tgl_lahir', 'alamat'])]
class Patient extends Model
{
    use HasFactory;

    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class);
    }

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
        ];
    }
}
