<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'admins';
    protected $primaryKey = 'id';
    public $timestamps = true;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    protected $fillable = [
        'email',
        'password',
        'role',
        'token',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    public function scopeApiUsers($query)
    {
        return $query->where('role', 'api');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isApiUser(): bool
    {
        return $this->role === 'api';
    }
}
