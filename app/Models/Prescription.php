<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model for the "prescriptions" table.
 */
class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'instructions',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Many-to-Many: the medicines written on this prescription, together with
     * their dosage / frequency / duration taken from the pivot table.
     */
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class, 'medicine_prescription')
                    ->withPivot(['dosage', 'frequency', 'duration'])
                    ->withTimestamps();
    }
}
