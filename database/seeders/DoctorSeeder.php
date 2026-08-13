<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates 12 doctors. Each one gets:
 *   - a user row  (the login account)
 *   - a doctor row (the professional profile)
 *   - a few doctor_schedules rows (weekly working hours)
 */
class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            ['Mohamed Ali',    'mohamed@clinic.com',  '01012345678', 'Cardiology',    'Interventional Cardiology', 350],
            ['Sara Ahmed',     'sara@clinic.com',     '01123456789', 'Dentistry',     'Cosmetic Dentistry',        250],
            ['Ahmed Hassan',   'ahmed.d@clinic.com',  '01234567890', 'Orthopedics',   'Joint Replacement',         400],
            ['Youssef Omar',   'youssef@clinic.com',  '01512345678', 'Dermatology',   'Cosmetic Dermatology',      300],
            ['Nada Ibrahim',   'nada@clinic.com',     '01098765432', 'Pediatrics',    'Neonatal Care',             280],
            ['Khaled Mostafa', 'khaled@clinic.com',   '01187654321', 'Neurology',     'Epilepsy & Seizures',       450],
            ['Mariam Fouad',   'mariam@clinic.com',   '01276543210', 'Ophthalmology', 'Retina Surgery',            380],
            ['Tarek Samir',    'tarek@clinic.com',    '01565432109', 'ENT',           'Sinus Surgery',             320],
            ['Laila Hassan',   'laila@clinic.com',    '01055554444', 'Cardiology',    'Heart Failure',             360],
            ['Omar Farouk',    'omar@clinic.com',     '01166663333', 'Orthopedics',   'Sports Injuries',           340],
            ['Heba Nasser',    'heba@clinic.com',     '01277772222', 'Pediatrics',    'Childhood Allergies',       260],
            ['Amr Zaki',       'amr@clinic.com',      '01588881111', 'Dermatology',   'Skin Cancer Screening',     310],
        ];

        foreach ($doctors as [$name, $email, $phone, $departmentName, $specialization, $fee]) {

            $department = Department::where('name', $departmentName)->first();

            if (! $department) {
                continue;   // the department seeder must run first
            }

            $user = User::firstOrCreate(['email' => $email], [
                'name'     => $name,
                'phone'    => $phone,
                'password' => 'password123',
                'role'     => User::ROLE_DOCTOR,
                'status'   => 'active',
            ]);

            $doctor = Doctor::firstOrCreate(['user_id' => $user->id], [
                'department_id'    => $department->id,
                'specialization'   => $specialization,
                'consultation_fee' => $fee,
                'bio'              => "Dr. {$name} is a specialist in {$specialization} at the {$departmentName} department.",
            ]);

            // Weekly working hours: Sunday to Thursday, 09:00 - 15:00.
            foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'] as $day) {
                DoctorSchedule::firstOrCreate([
                    'doctor_id'   => $doctor->id,
                    'day_of_week' => $day,
                ], [
                    'start_time' => '09:00:00',
                    'end_time'   => '15:00:00',
                ]);
            }
        }
    }
}
