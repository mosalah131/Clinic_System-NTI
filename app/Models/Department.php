<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for the "departments" table.
 */
class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    /** One-to-Many: one department has many doctors. */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    /** All appointments that belong to any doctor of this department. */
    public function appointments(): HasManyThrough
    {
        return $this->hasManyThrough(Appointment::class, Doctor::class);
    }
}
