<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for the "medicines" table.
 */
class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Many-to-Many: a medicine can appear in thousands of prescriptions.
     * The pivot carries the dosage, frequency and duration for each link.
     */
    public function prescriptions(): BelongsToMany
    {
        return $this->belongsToMany(Prescription::class, 'medicine_prescription')
                    ->withPivot(['dosage', 'frequency', 'duration'])
                    ->withTimestamps();
    }
}
