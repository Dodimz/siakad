<?php

namespace Database\Factories;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalKuliah>
 */
class JadwalKuliahFactory extends Factory
{
    protected $model = JadwalKuliah::class;

    public function definition(): array
    {
        return [
            'kelas_id' => Kelas::factory(),
            'hari' => fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']),
            'jam_mulai' => fake()->time('H:i'),
            'jam_selesai' => '16:00:00',
            'ruangan' => fake()->randomElement(['R.101', 'R.102', 'R.201', 'Lab. Komputer']),
        ];
    }
}
