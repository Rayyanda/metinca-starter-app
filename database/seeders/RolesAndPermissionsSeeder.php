<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Repair Module Permissions
        $repairPermissions = [
            'repair.view',
            'repair.create',
            'repair.update',
            'repair.delete',
            'repair.export',
            'repair.assign',
            'repair.update-status',
        ];

        foreach ($repairPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Global Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Create Repair Module Roles
        $repairUser = Role::firstOrCreate(['name' => 'repair.user', 'guard_name' => 'web']);
        $repairUser->syncPermissions(['repair.view', 'repair.create']);

        $repairTechnician = Role::firstOrCreate(['name' => 'repair.technician', 'guard_name' => 'web']);
        $repairTechnician->syncPermissions(['repair.view', 'repair.update-status']);

        $repairSupervisor = Role::firstOrCreate(['name' => 'repair.supervisor', 'guard_name' => 'web']);
        $repairSupervisor->syncPermissions($repairPermissions);

        $repairManager = Role::firstOrCreate(['name' => 'repair.manager', 'guard_name' => 'web']);
        $repairManager->syncPermissions($repairPermissions);

        // Create Test Users
        $this->createTestUsers($superAdmin, $repairUser, $repairTechnician, $repairSupervisor, $repairManager);
    }

    private function createTestUsers($superAdmin, $repairUser, $repairTechnician, $repairSupervisor, $repairManager): void
    {
        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'super@metinca.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($superAdmin);

        // Repair User (Reporter)
        $reporter = User::firstOrCreate(
            ['email' => 'reporter@metinca.local'],
            [
                'name' => 'Reporter User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $reporter->assignRole($repairUser);

        // Repair Technician
        $tech = User::firstOrCreate(
            ['email' => 'tech@metinca.local'],
            [
                'name' => 'Technician',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $tech->assignRole($repairTechnician);

        // Another Technician
        $tech2 = User::firstOrCreate(
            ['email' => 'tech2@metinca.local'],
            [
                'name' => 'Technician 2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $tech2->assignRole($repairTechnician);

        // Repair Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@metinca.local'],
            [
                'name' => 'Supervisor',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $supervisor->assignRole($repairSupervisor);

        // Repair Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@metinca.local'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole($repairManager);
    }
}
