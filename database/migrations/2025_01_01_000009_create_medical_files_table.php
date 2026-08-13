<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: medical_files
 *
 * Files uploaded BY THE STAFF (doctor or admin) about a patient:
 * an x-ray, a scanned report, a lab result, and so on.
 *
 * Files uploaded by the PATIENT go to the "analyses" table instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->enum('file_type', ['lab_result', 'x_ray', 'prescription_scan', 'other'])
                  ->default('other');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_files');
    }
};
