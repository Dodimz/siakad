<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Tampilkan formulir pengaturan global aplikasi.
     */
    public function index(): View
    {
        return view('central.settings.index');
    }

    /**
     * Simpan pengaturan dari UI ke tabel settings (tanpa mengubah file config).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings.app_name' => ['required', 'string', 'max:255'],
            'settings.version' => ['required', 'string', 'max:20'],
            'settings.kelas_kapasitas_default' => ['required', 'integer', 'min:1', 'max:200'],
            'settings.pagination' => ['required', 'integer', 'min:5', 'max:100'],
            'settings.format_tanggal' => ['required', 'string', 'max:20'],
            'settings.semester_labels' => ['required', 'array'],
            'settings.semester_labels.1' => ['required', 'string', 'max:50'],
            'settings.semester_labels.2' => ['required', 'string', 'max:50'],

            'settings.nilai_konversi' => ['nullable', 'array'],
            'settings.nilai_konversi.*.min' => ['nullable', 'numeric', 'between:0,100'],
            'settings.nilai_konversi.*.max' => ['nullable', 'numeric', 'between:0,100'],
            'settings.nilai_konversi.*.huruf' => ['nullable', 'string', 'max:4'],
            'settings.nilai_konversi.*.bobot' => ['nullable', 'numeric', 'between:0,4'],

            'settings.maks_sks' => ['required', 'array'],
            'settings.maks_sks.default' => ['required', 'integer', 'min:1', 'max:30'],
            'settings.maks_sks.ips_rules' => ['nullable', 'array'],
            'settings.maks_sks.ips_rules.*.min' => ['nullable', 'numeric', 'between:0,4'],
            'settings.maks_sks.ips_rules.*.max' => ['nullable', 'numeric', 'between:0,4'],
            'settings.maks_sks.ips_rules.*.sks' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $settings = $validated['settings'];
        $settings['nilai_konversi'] = $this->normalizeRows($settings['nilai_konversi'] ?? []);
        $settings['maks_sks']['ips_rules'] = $this->normalizeRows($settings['maks_sks']['ips_rules'] ?? []);

        $repeaterErrors = $this->validateRows($settings);

        if (! empty($repeaterErrors)) {
            return back()->withErrors($repeaterErrors)->withInput();
        }

        Settings::setMany($settings);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Validasi baris repeater (konversi nilai & aturan SKS) setelah baris kosong dibuang.
     */
    protected function validateRows(array $settings): array
    {
        $konversi = Validator::make(['rows' => $settings['nilai_konversi']], [
            'rows.*.min' => ['required', 'numeric', 'between:0,100'],
            'rows.*.max' => ['required', 'numeric', 'between:0,100'],
            'rows.*.huruf' => ['required', 'string', 'max:4'],
            'rows.*.bobot' => ['required', 'numeric', 'between:0,4'],
        ], messages: [], attributes: ['rows' => 'Konversi nilai']);

        $sks = Validator::make(['rows' => $settings['maks_sks']['ips_rules']], [
            'rows.*.min' => ['required', 'numeric', 'between:0,4'],
            'rows.*.max' => ['required', 'numeric', 'between:0,4'],
            'rows.*.sks' => ['required', 'integer', 'min:1', 'max:30'],
        ], messages: [], attributes: ['rows' => 'Aturan IPS']);

        return array_merge(
            $konversi->errors()->messages(),
            $sks->errors()->messages(),
        );
    }

    /**
     * Buang baris repeater yang kosong.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeRows(array $rows): array
    {
        return array_values(array_filter($rows, fn (array $row) => array_filter($row) !== []));
    }
}
