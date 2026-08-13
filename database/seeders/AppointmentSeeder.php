<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

/**
 * Fills the appointments table with a realistic mix:
 *
 *   - past dates    -> mostly completed, a few rejected / cancelled
 *   - today         -> accepted and pending
 *   - future dates  -> pending and accepted
 *
 * The seeder makes sure the same doctor is never booked twice at the same
 * date and time, exactly like the real booking rule in the controllers.
 */
class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $doctors  = Doctor::pluck('id')->all();
        $patients = Patient::pluck('id')->all();

        if (empty($doctors) || empty($patients)) {
            return;
        }

        $slots = ['09:00:00', '09:30:00', '10:00:00', '10:30:00', '11:00:00',
                  '11:30:00', '12:00:00', '12:30:00', '13:00:00', '13:30:00'];

        $symptoms = [
            'Chest pain and shortness of breath for three days.',
            'Severe toothache on the lower right side.',
            'Knee pain after playing football.',
            'Itchy red rash on both arms.',
            'High fever and a persistent cough.',
            'Frequent headaches and dizziness.',
            'Blurred vision when reading.',
            'Sore throat and difficulty swallowing.',
            'Stomach pain after meals.',
            'Lower back pain when standing for a long time.',
            'Skin irritation after using a new cream.',
            'Follow-up visit for blood pressure control.',
        ];

        // "taken" remembers which doctor+date+time combinations are already used.
        $taken   = [];
        $created = 0;

        // 60 days in the past  ->  20 days in the future
        for ($dayOffset = -60; $dayOffset <= 20; $dayOffset++) {

            $date    = now()->addDays($dayOffset)->toDateString();
            $isPast  = $dayOffset < 0;
            $isToday = $dayOffset === 0;

            // A handful of appointments per day.
            $perDay = $isToday ? 8 : random_int(1, 3);

            for ($i = 0; $i < $perDay; $i++) {

                $doctorId = $doctors[array_rand($doctors)];
                $slot     = $slots[array_rand($slots)];
                $key      = $doctorId.'|'.$date.'|'.$slot;

                if (isset($taken[$key])) {
                    continue;   // that doctor is already busy at that moment
                }

                $taken[$key] = true;

                // Which status makes sense for this date?
                if ($isPast) {
                    $status = match (random_int(1, 10)) {
                        1, 2    => Appointment::STATUS_CANCELLED,
                        3       => Appointment::STATUS_REJECTED,
                        default => Appointment::STATUS_COMPLETED,
                    };
                } elseif ($isToday) {
                    $status = random_int(1, 2) === 1
                        ? Appointment::STATUS_ACCEPTED
                        : Appointment::STATUS_PENDING;
                } else {
                    $status = random_int(1, 3) === 1
                        ? Appointment::STATUS_ACCEPTED
                        : Appointment::STATUS_PENDING;
                }

                Appointment::create([
                    'patient_id'       => $patients[array_rand($patients)],
                    'doctor_id'        => $doctorId,
                    'appointment_date' => $date,
                    'appointment_time' => $slot,
                    'status'           => $status,
                    'symptoms'         => $symptoms[array_rand($symptoms)],
                    'cancel_reason'    => match ($status) {
                        Appointment::STATUS_CANCELLED => 'Cancelled by the patient',
                        Appointment::STATUS_REJECTED  => 'The doctor is not available at this time',
                        default                       => null,
                    },
                ]);

                $created++;
            }
        }

        $this->command->info("  {$created} appointments created.");
    }
}
