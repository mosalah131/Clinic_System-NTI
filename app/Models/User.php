<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model for the "users" table.
 *
 * Every account in the system is a row here. The role column tells us which
 * kind of user it is.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /** The four roles supported by the system. */
    public const ROLE_ADMIN     = 'admin';
    public const ROLE_DOCTOR    = 'doctor';
    public const ROLE_RECEPTION = 'reception';
    public const ROLE_PATIENT   = 'patient';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',   // Laravel hashes the password automatically
        ];
    }

    /* ------------------------------------------------------------------
     | Relationships
     | -----------------------------------------------------------------*/

    /** One-to-One: a user with the "doctor" role has one doctor profile. */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /** One-to-One: a user with the "patient" role has one patient profile. */
    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    /** Files this user has uploaded for patients (doctor / admin). */
    public function uploadedMedicalFiles(): HasMany
    {
        return $this->hasMany(MedicalFile::class, 'uploaded_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /* ------------------------------------------------------------------
     | Helpers
     | -----------------------------------------------------------------*/

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDoctor(): bool
    {
        return $this->role === self::ROLE_DOCTOR;
    }

    public function isReception(): bool
    {
        return $this->role === self::ROLE_RECEPTION;
    }

    public function isPatient(): bool
    {
        return $this->role === self::ROLE_PATIENT;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Where this user should land after logging in.
     * Used by AuthController and by the "/" route.
     */
    public function homeRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN     => route('admin.dashboard'),
            self::ROLE_DOCTOR    => route('doctor.dashboard'),
            self::ROLE_RECEPTION => route('reception.dashboard'),
            default              => route('patient.dashboard'),
        };
    }

    /** A friendly label for the navbar, e.g. "Dr. Mohamed Ali". */
    public function displayName(): string
    {
        return $this->isDoctor() && ! str_starts_with($this->name, 'Dr.')
            ? 'Dr. '.$this->name
            : $this->name;
    }

    /** Only active, non-deleted users of a given role. */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
