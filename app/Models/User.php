<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles ;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',      // NIK
        'department',
        'shift',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relation ke operation sebagai operator
    // public function operations()
    // {
    //     return $this->hasMany(Operation::class, 'operator_id', 'employee_id');
    // }
    
    // Scope untuk user aktif
    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'user_divisions')
            ->withPivot('role')  // supervisor, operator, inspector
            ->withTimestamps();
    }

    public function hasDivisionAccess($divisionId)
    {
        // Admin bisa akses semua
        if ($this->hasRole('superadmin') || $this->hasRole('production_manager')) {
            return true;
        }
        
        return $this->divisions()->where('division_id', $divisionId)->exists();
    }

    public function getDivisionRole($divisionId)
    {
        $division = $this->divisions()->where('division_id', $divisionId)->first();
        return $division ? $division->pivot->role : null;
    }
}
