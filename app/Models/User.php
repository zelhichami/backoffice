<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Role definitions for backoffice platform
    const ROLE_INTEGRATOR = 'integrator';           // Can create landing page sections
    const ROLE_REVIEWER = 'reviewer';               // Verifies and reviews submitted sections
    const ROLE_PROMPT_ENGINEER = 'prompt_engineer'; // Edits AI prompts and makes sections dynamic with Liquid
    const ROLE_ADMIN = 'admin';                     // Final validation + full access
    const ROLE_SUPERADMIN = 'superadmin';           // Full system control

    /**
     * Get role of user.
     *
     * @return string
     */

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPERADMIN]);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'hosting_id',
        'role'
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



}
