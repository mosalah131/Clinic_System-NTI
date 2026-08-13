<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for the "appointments" table - the centre of the whole system.
 */
class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /** The five states an appointment can be in. */
    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'cancel_reason',
        'symptoms',
        'diagnosis',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    /* ------------------------------------------------------------------
     | Relationships
     | -----------------------------------------------------------------*/

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** One appointment has one prescription. */
    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    /** One appointment can have many uploaded analyses. */
    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    /* ------------------------------------------------------------------
     | Query scopes - small reusable filters
     | -----------------------------------------------------------------*/

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '>=', today());
    }

    /* ------------------------------------------------------------------
     | Business rules (Phase 3)
     | -----------------------------------------------------------------*/

    /** A doctor may only accept / reject while the request is still pending. */
    public function canBeReviewed(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Cancelling is possible while nothing final has happened yet. */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACCEPTED], true);
    }

    /** A cancelled or completed appointment must not be edited any more. */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACCEPTED], true);
    }

    /** A prescription may only be written for an accepted (or completed) visit. */
    public function canHavePrescription(): bool
    {
        return in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_COMPLETED], true);
    }

    /** Completed medical records are never deleted. */
    public function canBeDeleted(): bool
    {
        return $this->status !== self::STATUS_COMPLETED;
    }

    /* ------------------------------------------------------------------
     | Display helpers
     | -----------------------------------------------------------------*/

    /** Bootstrap badge colour for the current status. */
    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTED  => 'bg-success',
            self::STATUS_PENDING   => 'bg-warning text-dark',
            self::STATUS_REJECTED  => 'bg-danger',
            self::STATUS_CANCELLED => 'bg-secondary',
            self::STATUS_COMPLETED => 'bg-primary',
            default                => 'bg-light text-dark',
        };
    }

    /** "10:00 AM" instead of "10:00:00". */
    public function getTimeLabelAttribute(): string
    {
        return date('h:i A', strtotime((string) $this->appointment_time));
    }

    /** All five statuses, handy for filter dropdowns. */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
        ];
    }
}
