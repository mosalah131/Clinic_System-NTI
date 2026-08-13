<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Database\Seeder;

/**
 * Every COMPLETED appointment gets a diagnosis and a prescription with
 * 1 to 3 medicines - exactly what the doctor would have written.
 */
class PrescriptionSeeder extends Seeder
{
    public function run(): void
    {
        $medicineIds = Medicine::pluck('id')->all();

        if (empty($medicineIds)) {
            return;
        }

        $diagnoses = [
            'Acute Tonsillitis',
            'Influenza',
            'Mild Hypertension',
            'Type 2 Diabetes - follow up',
            'Dental Caries',
            'Contact Dermatitis',
            'Lower Back Strain',
            'Seasonal Allergic Rhinitis',
            'Gastritis',
            'Migraine',
            'Conjunctivitis',
            'Iron Deficiency Anaemia',
        ];

        $instructions = [
            'Drink plenty of water and rest for 5 days.',
            'Take the medicine after meals. Come back in two weeks.',
            'Avoid cold drinks and spicy food until the pain is gone.',
            'Do the blood test before the next visit.',
            'Apply the cream twice a day on the affected area only.',
            'Reduce salt in your food and walk 30 minutes every day.',
        ];

        $dosages     = ['500mg', '1g', '250mg', '50mg', '10ml', '75mg'];
        $frequencies = ['1x daily', '2x daily', '3x daily', 'When needed'];
        $durations   = ['3 days', '5 days', '7 days', '10 days', '1 month'];

        $completed = Appointment::where('status', Appointment::STATUS_COMPLETED)->get();

        foreach ($completed as $appointment) {

            // 1. The diagnosis lives on the appointment itself.
            $appointment->update([
                'diagnosis' => $diagnoses[array_rand($diagnoses)],
                'notes'     => 'Patient examined. Vital signs are within the normal range.',
            ]);

            // 2. The prescription.
            $prescription = Prescription::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id'    => $appointment->doctor_id,
                    'patient_id'   => $appointment->patient_id,
                    'instructions' => $instructions[array_rand($instructions)],
                ]
            );

            // 3. The medicines, written into the pivot table with their
            //    dosage / frequency / duration.
            $chosen = (array) array_rand(array_flip($medicineIds), random_int(1, 3));

            $pivot = [];
            foreach ($chosen as $medicineId) {
                $pivot[$medicineId] = [
                    'dosage'    => $dosages[array_rand($dosages)],
                    'frequency' => $frequencies[array_rand($frequencies)],
                    'duration'  => $durations[array_rand($durations)],
                ];
            }

            $prescription->medicines()->sync($pivot);
        }

        $this->command->info("  {$completed->count()} prescriptions created.");
    }
}
