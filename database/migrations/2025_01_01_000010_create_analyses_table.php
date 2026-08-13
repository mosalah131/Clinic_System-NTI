<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: analyses     (Phase 4 - Analysis Upload Module)
 *
 * Medical analyses uploaded BY THE PATIENT (blood test, x-ray, MRI ...).
 * The file may optionally be linked to the appointment it belongs to, which is
 * how the doctor sees it when opening that appointment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('title');
            $table->string('file_name');      // the original name, e.g. "blood-test.pdf"
            $table->string('file_path');      // where we stored it inside storage/app/public
            $table->string('file_type')->nullable();  // blood_test, x_ray, mri, other ...
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
