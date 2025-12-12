<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

// database/seeders/RolePermissionSeeder.php
class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create permissions
        $permissions = [
            // Batch Management
            'batch.view',
            'batch.create',
            'batch.edit',
            'batch.delete',
            'batch.approve',
            'batch.cancel',
            
            // Operation Tracking
            'operation.view',
            'operation.start',
            'operation.pause',
            'operation.complete',
            'operation.edit_time',
            'operation.override',
            
            // Quality
            'qc.inspect',
            'qc.approve',
            'qc.reject',
            'qc.view_report',
            
            // Reporting
            'report.view',
            'report.export',
            'report.analytics',
            
            // Master Data
            'master.view',
            'master.edit',
            'master.delete',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Create roles and assign permissions
        $roles = [
            'operator' => [
                'batch.view',
                'operation.view',
                'operation.start',
                'operation.pause',
                'operation.complete',
            ],
            
            'supervisor' => [
                'batch.view',
                'batch.create',
                'batch.edit',
                'batch.approve',
                'batch.cancel',
                'operation.view',
                'operation.edit_time',
                'qc.inspect',
                'qc.approve',
                'qc.reject',
                'report.view',
            ],
            
            'production_planner' => [
                'batch.view',
                'batch.create',
                'batch.edit',
                'batch.delete',
                'operation.view',
                'master.view',
                'report.view',
                'report.export',
            ],
            
            'quality_inspector' => [
                'batch.view',
                'operation.view',
                'qc.inspect',
                'qc.approve',
                'qc.reject',
                'qc.view_report',
            ],
            
            'production_manager' => [
                'batch.view',
                'batch.create',
                'batch.edit',
                'batch.delete',
                'batch.approve',
                'batch.cancel',
                'operation.view',
                'operation.start',
                'operation.pause',
                'operation.complete',
                'operation.edit_time',
                'operation.override',
                'qc.inspect',
                'qc.approve',
                'qc.reject',
                'qc.view_report',
                'report.view',
                'report.export',
                'report.analytics',
                'master.view',
                'master.edit',
            ],
            
            //'admin' => ['*'], // All permissions
        ];
        
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }
    }
}
