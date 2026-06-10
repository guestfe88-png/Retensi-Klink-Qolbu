<?php

namespace App\Services;

use App\Models\Patient;

class PatientService
{
    public function syncFromBerkas(array $data): Patient
    {
        return Patient::updateOrCreate(
            ['no_rm' => $data['no_rm']],
            [
                'nama_pasien' => $data['nama_pasien'],
                'tgl_lahir' => $data['tgl_lahir'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ]
        );
    }
}
