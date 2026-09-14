<?php

namespace Database\Factories;

use App\Models\Kampus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kampus>
 */
class KampusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = 'Kampus '.$this->faker->city();

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama).'-'.$this->faker->unique()->numberBetween(100, 999),
            'kode' => Str::upper(Str::random(3)),
            'alamat' => $this->faker->address(),
            'telepon' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->companyEmail(),
            'status' => Kampus::STATUS_ACTIVE,
        ];
    }

    public function trial(): static
    {
        return $this->state(fn () => ['status' => Kampus::STATUS_TRIAL]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => Kampus::STATUS_SUSPENDED]);
    }

    public function withPmbToken(): static
    {
        return $this->state(fn () => ['pmb_token' => 'pmb_'.Str::random(64)]);
    }
}
