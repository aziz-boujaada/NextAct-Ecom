<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;


#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{


    protected $fillable = ['name', 'email', 'password', 'role'];
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EMPLOYEE = 'employee';

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

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions->contains('name', $permission);
    }

    public function hasAnyPermission($permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $permissions = (array) $permissions;

        return $this->permissions
            ->pluck('name')
            ->intersect($permissions)
            ->isNotEmpty();
    }

    public function hasAllPermissions($permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $permissions = (array) $permissions;

        return collect($permissions)->every(
            fn ($permission) => $this->permissions->contains('name', $permission)
        );
    }

    public function giveAllPermissions(): void
    {
        $this->permissions()->sync(
            Permission::all()->pluck('id')
        );
    }
}
