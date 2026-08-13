<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for the "doctors" table.
 */
class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'specialization',
        'consultation_fee',
        'bio',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
        ];
    }

    /* ------------------------------------------------------------------
     | Relationships
     | -----------------------------------------------------------------*/

    /** One-to-One (inverse): the login account of this doctor. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** One-to-Many (inverse): the department this doctor works in. */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** One-to-Many: the doctor's weekly working hours. */
    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    /** One-to-Many: every appointment booked with this doctor. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** One-to-Many: every prescription this doctor has written. */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /* ------------------------------------------------------------------
     | Helpers
     | -----------------------------------------------------------------*/

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown doctor';
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->name;

        return str_starts_with($name, 'Dr.') ? $name : 'Dr. '.$name;
    }
}
