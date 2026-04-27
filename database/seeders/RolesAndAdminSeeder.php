<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view_dashboard',
            'manage_santri',
            'manage_attendance',
            'manage_finance',
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'kepala_madrasah' => ['view_dashboard', 'view_reports'],
            'admin_tu' => ['view_dashboard', 'manage_santri', 'manage_attendance', 'manage_finance', 'view_reports'],
            'wali_kelas' => ['view_dashboard', 'manage_attendance'],
            'guru' => ['view_dashboard', 'manage_attendance'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::query()->firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@tpq.local'],
            [
                'name' => 'Admin TU',
                'phone' => '081234567890',
                'password' => Hash::make('Admin12345!'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin_tu']);

        $headmaster = User::query()->firstOrCreate(
            ['email' => 'kepala@tpq.local'],
            [
                'name' => 'Kepala Madrasah',
                'phone' => '081234567891',
                'password' => Hash::make('Kepala12345!'),
                'is_active' => true,
            ]
        );
        $headmaster->syncRoles(['kepala_madrasah']);
    }
}
