<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MataKuliah>
 */
class MataKuliahFactory extends Factory
{
    protected $model = MataKuliah::class;

    public function definition(): array
    {
        return [
            'prodi_id' => Prodi::factory(),
            'kode_mk' => 'MK'.fake()->unique()->numerify('####'),
            'nama_mk' => fake()->randomElement([
                'Matematika Dasar',
                'Algoritma Pemrograman',
                'Basis Data',
                'Jaringan Komputer',
                'Sistem Operasi',
            ]),
            'sks' => fake()->randomElement([2, 3, 4]),
            'semester' => fake()->numberBetween(1, 8),
        ];
    }
}
