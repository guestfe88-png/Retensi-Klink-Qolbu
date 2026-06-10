<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Patient> */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'no_rm' => fake()->unique()->numerify('RM#####'),
            'nama_pasien' => fake()->name(),
            'tgl_lahir' => fake()->date(),
            'alamat' => fake()->address(),
        ];
    }
}
