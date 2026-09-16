<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Mahasiswa management
            'view mahasiswa',
            'create mahasiswa',
            'edit mahasiswa',
            'delete mahasiswa',

            // Dosen management
            'view dosen',
            'create dosen',
            'edit dosen',
            'delete dosen',

            // Kelas management
            'view kelas',
            'create kelas',
            'edit kelas',
            'delete kelas',

            // Mata Kuliah management
            'view mata kuliah',
            'create mata kuliah',
            'edit mata kuliah',
            'delete mata kuliah',

            // KRS management
            'view krs',
            'approve krs',
            'reject krs',

            // Nilai management
            'view nilai',
            'input nilai',

            // Skripsi management
            'view skripsi',
            'manage skripsi',

            // KP management
            'view kp',
            'manage kp',

            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Master data
            'manage fakultas',
            'manage prodi',
            'manage tahun akademik',
            'manage ruangan',

            // Reports
            'view reports',
            'export reports',

            // Announcements
            'manage announcements',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with permissions

        // Superadmin - has all permissions
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superadmin->givePermissionTo(Permission::all());

        // Admin Fakultas - has most admin permissions but scoped to fakultas
        $adminFakultas = Role::firstOrCreate(['name' => 'admin_fakultas']);
        $adminFakultas->givePermissionTo([
            'view mahasiswa', 'create mahasiswa', 'edit mahasiswa',
            'view dosen', 'create dosen', 'edit dosen',
            'view kelas', 'create kelas', 'edit kelas', 'delete kelas',
            'view mata kuliah', 'create mata kuliah', 'edit mata kuliah',
            'view krs', 'approve krs', 'reject krs',
            'view nilai',
            'view skripsi', 'manage skripsi',
            'view kp', 'manage kp',
            'manage prodi',
            'manage ruangan',
            'view reports', 'export reports',
            'manage announcements',
        ]);

        // Admin Kampus - akses admin di seluruh fakultas dalam kampus
        $adminKampus = Role::firstOrCreate(['name' => 'admin_kampus']);
        $adminKampus->givePermissionTo([
            'view mahasiswa', 'create mahasiswa', 'edit mahasiswa',
            'view dosen', 'create dosen', 'edit dosen',
            'view kelas', 'create kelas', 'edit kelas', 'delete kelas',
            'view mata kuliah', 'create mata kuliah', 'edit mata kuliah',
            'view krs', 'approve krs', 'reject krs',
            'view nilai', 'input nilai',
            'view skripsi', 'manage skripsi',
            'view kp', 'manage kp',
            'manage fakultas', 'manage prodi', 'manage tahun akademik',
            'manage ruangan',
            'view reports', 'export reports',
            'manage announcements',
        ]);

        // Dosen - can view and input nilai, manage bimbingan
        $dosen = Role::firstOrCreate(['name' => 'dosen']);
        $dosen->givePermissionTo([
            'view kelas',
            'view nilai', 'input nilai',
            'view skripsi',
            'view kp',
            'view mahasiswa',
        ]);

        // Mahasiswa - minimal permissions (most access via routes)
        $mahasiswa = Role::firstOrCreate(['name' => 'mahasiswa']);
        // Mahasiswa typically don't need explicit permissions
        // Their access is controlled by role middleware

        $this->command->info('Roles and permissions created successfully!');
    }
}
