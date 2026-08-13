<?php

/**
 * Project-specific settings for the Clinic System.
 *
 * Keeping these in one place means you can change a rule (for example the
 * maximum upload size) without hunting through the controllers.
 */
return [

    // Phase 4 - Analysis Upload Module validation rules.
    'uploads' => [
        // Maximum size in kilobytes. 10240 KB = 10 MB.
        'max_size_kb' => (int) env('CLINIC_UPLOAD_MAX_KB', 10240),

        // Extensions a patient may upload as a medical analysis.
        'analysis_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'docx'],

        // Extensions a doctor may upload as a patient medical file.
        'medical_file_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'docx'],
    ],

    // Time slots offered on the "Book Appointment" page.
    'time_slots' => [
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
        '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
        '18:00', '18:30', '19:00', '19:30', '20:00',
    ],

];
