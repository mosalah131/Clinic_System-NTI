<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: medicine_prescription   (PIVOT TABLE - Many-to-Many)
 *
 * One prescription can contain many medicines, and one medicine can appear in
 * thousands of prescriptions. The extra columns (dosage / frequency / duration)
 * belong to the *link* between them, not to the medicine itself:
 *
 *   Prescription #12  +  Panadol   ->  500mg, 3x daily, 7 days
 *   Prescription #33  +  Panadol   ->  1g,    2x daily, 3 days
 *
 * The name follows Laravel's convention (the two table names in singular,
 * alphabetical order, joined by an underscore).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_prescription', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('dosage');      // e.g. "500mg"
            $table->string('frequency');   // e.g. "3x daily"
            $table->string('duration');    // e.g. "7 days"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_prescription');
    }
};
