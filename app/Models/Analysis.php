<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for the "analyses" table - medical analyses uploaded by the patient.
 *
 * "analyses" is an irregular plural, so we tell Eloquent the table name
 * explicitly (otherwise it would look for a table called "analyses" derived
 * from "Analysis" incorrectly).
 */
class Analysis extends Model
{
    use HasFactory;

    protected $table = 'analyses';

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'title',
        'file_name',
        'file_path',
        'file_type',
        'description',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** Public URL used by the "view / download" buttons. */
    public function getUrlAttribute(): string
    {
        return url('storage/'.$this->file_path);
    }

    public function getTypeLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', (string) $this->file_type));
    }

    /** File extension in capitals, e.g. "PDF" - used for the little icon. */
    public function getExtensionAttribute(): string
    {
        return strtoupper(pathinfo((string) $this->file_name, PATHINFO_EXTENSION));
    }
}
