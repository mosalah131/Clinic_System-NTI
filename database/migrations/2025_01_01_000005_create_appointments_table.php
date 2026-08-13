<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: appointments  (merged with the diagnosis)
 *
 * This is the central entity of the whole system. Everything else
 * (prescription, medicines, analyses) hangs off an appointment.
 *
 * Life cycle of the "status" column:
 *   pending -> accepted -> completed
 *   pending -> rejected
 *   pending -> cancelled
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', [
                'pending', 'accepted', 'rejected', 'cancelled', 'completed',
            ])->default('pending');
            $table->text('cancel_reason')->nullable();
            $table->text('symptoms')->nullable();   // written by the patient when booking
            $table->text('diagnosis')->nullable();  // written by the doctor after the visit
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Makes the dashboard queries ("today's appointments", "pending") fast.
            $table->index(['appointment_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
