<?php

use App\Models\Setting;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dashboardSettingsAdmin(): User
{
    return User::factory()->create([
        'role' => 'superadmin',
        'name' => 'Super Admin Dashboard',
        'email' => 'dashboard.super@test.com',
    ]);
}

test('admin dapat melihat halaman pengaturan dengan form repeater', function () {
    $response = $this->actingAs(dashboardSettingsAdmin())->get('/central/settings');

    $response->assertOk();
    $response->assertViewIs('central.settings.index');
    $response->assertSee('SIAKAD Universitas');
    $response->assertSee('Konversi Nilai');
});

test('pengaturan tersimpan ke database dan ter-merge ke config', function () {
    $this->actingAs(dashboardSettingsAdmin())->put('/central/settings', [
        'settings' => [
            'app_name' => 'SIAKAD Test',
            'version' => '2.0.0',
            'kelas_kapasitas_default' => 50,
            'pagination' => 20,
            'format_tanggal' => 'Y-m-d',
            'semester_labels' => [1 => 'Ganjil', 2 => 'Genap'],
            'nilai_konversi' => [
                ['min' => 85, 'max' => 100, 'huruf' => 'A', 'bobot' => 4.00],
                ['min' => 0, 'max' => 54, 'huruf' => 'E', 'bobot' => 0.00],
            ],
            'maks_sks' => [
                'default' => 24,
                'ips_rules' => [
                    ['min' => 3.51, 'max' => 4.00, 'sks' => 24],
                ],
            ],
        ],
    ])->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('settings', ['key' => 'app_name']);

    $konversi = Setting::where('key', 'nilai_konversi')->value('value');

    expect(config('siakad.app_name'))->toBe('SIAKAD Test')
        ->and(config('siakad.pagination'))->toBe(20)
        ->and(config('siakad.kelas_kapasitas_default'))->toBe(50)
        ->and($konversi)->toHaveCount(2)
        ->and(collect($konversi)->pluck('huruf'))->toContain('A', 'E');
});

test('baris repeater kosong dibuang, baris parsial tetap divalidasi', function () {
    $user = dashboardSettingsAdmin();

    $this->actingAs($user)->put('/central/settings', [
        'settings' => [
            'app_name' => 'SIAKAD Test',
            'version' => '1.0.0',
            'kelas_kapasitas_default' => 40,
            'pagination' => 15,
            'format_tanggal' => 'd-m-Y',
            'semester_labels' => [1 => 'Ganjil', 2 => 'Genap'],
            'nilai_konversi' => [
                ['min' => '', 'max' => '', 'huruf' => '', 'bobot' => ''],
                ['min' => 70, 'max' => 74, 'huruf' => 'B', 'bobot' => 3.00],
            ],
            'maks_sks' => [
                'default' => 24,
                'ips_rules' => [['min' => '', 'max' => '', 'sks' => '']],
            ],
        ],
    ])->assertRedirect()
        ->assertSessionHas('success');

    $konversi = Setting::where('key', 'nilai_konversi')->value('value');

    expect($konversi)->toHaveCount(1)
        ->and(collect($konversi)->first()['huruf'])->toBe('B')
        ->and(Setting::where('key', 'maks_sks')->value('value'))->toEqual(['default' => 24, 'ips_rules' => []]);

    $this->actingAs($user)->put('/central/settings', [
        'settings' => [
            'app_name' => 'SIAKAD Test',
            'version' => '1.0.0',
            'kelas_kapasitas_default' => 40,
            'pagination' => 15,
            'format_tanggal' => 'd-m-Y',
            'semester_labels' => [1 => 'Ganjil', 2 => 'Genap'],
            'nilai_konversi' => [['min' => 85, 'max' => '', 'huruf' => '', 'bobot' => '']],
            'maks_sks' => ['default' => 24, 'ips_rules' => []],
        ],
    ])->assertSessionHasErrors();
});

test('validasi menolak pengaturan tidak valid', function () {
    $this->actingAs(dashboardSettingsAdmin())->put('/central/settings', [
        'settings' => [
            'app_name' => '',
            'version' => '1.0.0',
            'kelas_kapasitas_default' => 40,
            'pagination' => 15,
            'format_tanggal' => 'd-m-Y',
            'semester_labels' => [1 => 'Ganjil', 2 => 'Genap'],
            'maks_sks' => ['default' => 24, 'ips_rules' => []],
        ],
    ])->assertSessionHasErrors(['settings.app_name']);

    expect(Setting::count())->toBe(0);
});

test('nilai yang belum disimpan memakai default dari config file', function () {
    expect(Settings::get('app_name'))->toBe(config('siakad.app_name'))
        ->and(Settings::get('tidak_ada'))->toBeNull();
});
