<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for the "medical_files" table - files uploaded by the staff.
 */
class MedicalFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'uploaded_by',
        'title',
        'file_path',
        'file_type',
        'description',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Public URL used by the "view / download" buttons.
     *
     * url() builds the address from the site you are actually visiting, so the
     * link keeps working whether you run the project with "php artisan serve"
     * or through Apache in XAMPP.
     */
    public function getUrlAttribute(): string
    {
        return url('storage/'.$this->file_path);
    }

    /** "Lab Result" instead of "lab_result". */
    public function getTypeLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', (string) $this->file_type));
    }
}
