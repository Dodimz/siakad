<?php

namespace Database\Factories;

use App\Models\Kampus;
use App\Models\PmbImportLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PmbImportLog>
 */
class PmbImportLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kampus_id' => Kampus::factory(),
            'no_pendaftar' => 'PMB-'.fake()->unique()->numberBetween(1000, 9999),
            'nama' => fake()->name(),
            'aksi' => fake()->randomElement([PmbImportLog::AKSI_CREATED, PmbImportLog::AKSI_DUPLICATE]),
            'ip_address' => '127.0.0.1',
        ];
    }
}
