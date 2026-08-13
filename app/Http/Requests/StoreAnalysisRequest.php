<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * PHASE 4 - validation for the analysis upload.
 *
 * Three things are checked: the file must exist, its extension must be allowed,
 * and it must not be bigger than the limit set in config/clinic.php.
 */
class StoreAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isPatient();
    }

    public function rules(): array
    {
        $maxKb   = config('clinic.uploads.max_size_kb');
        $allowed = implode(',', config('clinic.uploads.analysis_extensions'));

        return [
            'title'          => ['required', 'string', 'max:150'],
            'file_type'      => ['required', 'string', 'max:50'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'description'    => ['nullable', 'string', 'max:500'],
            'file'           => ['required', 'file', "mimes:{$allowed}", "max:{$maxKb}"],
        ];
    }

    public function messages(): array
    {
        $maxMb = round(config('clinic.uploads.max_size_kb') / 1024);

        return [
            'title.required' => 'Please give the file a title.',
            'file.required'  => 'Please choose a file to upload.',
            'file.file'      => 'The upload did not arrive correctly. Please try again.',
            'file.mimes'     => 'Allowed file types are: PDF, JPG, PNG and DOCX.',
            'file.max'       => "The file is too large. The maximum size is {$maxMb} MB.",
        ];
    }
}
