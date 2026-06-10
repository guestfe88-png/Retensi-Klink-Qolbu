<?php

namespace Database\Factories;

use App\Models\Berkas;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Berkas> */
class BerkasFactory extends Factory
{
    protected $model = Berkas::class;

    public function definition(): array
    {
        $patient = Patient::factory()->create();

        return [
            'patient_id' => $patient->id,
            'no_rm' => $patient->no_rm,
            'nama_pasien' => $patient->nama_pasien,
            'tgl_lahir' => fake()->date(),
            'alamat' => fake()->address(),
            'nama_berkas' => 'RM-'.fake()->word(),
            'klasifikasi' => 'rawat_jalan',
            'lokasi_arsip' => 'Rak A / Box '.fake()->numberBetween(1, 20),
            'status' => 'Aktif',
            'tgl_kunjungan_terakhir' => now()->subMonths(6),
            'tgl_retensi' => now()->addYears(2),
            'legal_hold' => false,
            'keterangan' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
