<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for the "patients" table.
 */
class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'dob',
        'gender',
        'blood_group',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    /* ------------------------------------------------------------------
     | Relationships
     | -----------------------------------------------------------------*/

    /** One-to-One (inverse): the login account of this patient. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** One-to-Many: every appointment this patient has booked. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** One-to-Many: every prescription written for this patient. */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /** One-to-Many: files uploaded by the staff about this patient. */
    public function medicalFiles(): HasMany
    {
        return $this->hasMany(MedicalFile::class);
    }

    /** One-to-Many: analyses this patient uploaded. */
    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    /* ------------------------------------------------------------------
     | Helpers
     | -----------------------------------------------------------------*/

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown patient';
    }

    /** Age in whole years, calculated from the date of birth. */
    public function getAgeAttribute(): int
    {
        return $this->dob ? $this->dob->age : 0;
    }
}
