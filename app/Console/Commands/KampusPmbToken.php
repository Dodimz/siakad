<?php

namespace App\Console\Commands;

use App\Models\Kampus;
use Illuminate\Console\Command;

class KampusPmbToken extends Command
{
    protected $signature = 'kampus:pmb-token {kampus? : Slug kampus. Kosongkan untuk daftar semua} {--force : Terbitkan token baru meski token lama ada}';

    protected $description = 'Tampilkan atau terbitkan token API PMB per kampus';

    public function handle(): int
    {
        $slug = $this->argument('kampus');

        if (! $slug) {
            return $this->listTokens();
        }

        $kampus = Kampus::where('slug', $slug)->first();

        if (! $kampus) {
            $this->error("Kampus dengan slug '{$slug}' tidak ditemukan.");

            return self::FAILURE;
        }

        $token = $kampus->issuePmbToken($this->option('force'));

        $this->info("Kampus: {$kampus->nama} ({$kampus->slug})");
        $this->line($token);

        return self::SUCCESS;
    }

    protected function listTokens(): int
    {
        $kampuses = Kampus::orderBy('id')->get();

        if ($kampuses->isEmpty()) {
            $this->info('Belum ada kampus terdaftar.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Nama', 'Slug', 'Token'], $kampuses->map(fn (Kampus $kampus): array => [
            $kampus->id,
            $kampus->nama,
            $kampus->slug,
            $kampus->pmb_token ? substr($kampus->pmb_token, 0, 16).'…' : '-',
        ])->all());

        return self::SUCCESS;
    }
}
