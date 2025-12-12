<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'employee_id' => 'EMP001',
            'department' => 'IT',
            'shift' => 'Morning',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $role = Role::create(['name'=>'superadmin']);
        $admin->assignRole('superadmin');
    }
}
