<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user has access to a specific module
     */
    public function hasModuleAccess(string $moduleName): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        $moduleRoles = $this->roles->filter(function ($role) use ($moduleName) {
            return str_starts_with($role->name, "{$moduleName}.");
        });

        return $moduleRoles->isNotEmpty();
    }

    /**
     * Get user's role for a specific module
     */
    public function getModuleRole(string $moduleName): ?string
    {
        if ($this->hasRole('super_admin')) {
            return 'super_admin';
        }

        $moduleRole = $this->roles->first(function ($role) use ($moduleName) {
            return str_starts_with($role->name, "{$moduleName}.");
        });

        return $moduleRole ? str_replace("{$moduleName}.", '', $moduleRole->name) : null;
    }

    /**
     * Get all modules user has access to
     */
    public function accessibleModules(): array
    {
        if ($this->hasRole('super_admin')) {
            return array_keys(config('modules.modules', []));
        }

        $modules = [];
        foreach ($this->roles as $role) {
            if (str_contains($role->name, '.')) {
                [$module] = explode('.', $role->name, 2);
                $modules[$module] = true;
            }
        }

        return array_keys($modules);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user has supervisor or higher role for a module
     */
    public function isModuleSupervisorOrHigher(string $moduleName): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        $higherRoles = [
            "{$moduleName}.supervisor",
            "{$moduleName}.manager",
        ];

        return $this->hasAnyRole($higherRoles);
    }
}
