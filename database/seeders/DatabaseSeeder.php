<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * The master seeder.
 *
 * Run it with:   php artisan db:seed
 * Or wipe the database and fill it again with:  php artisan migrate:fresh --seed
 *
 * The order matters! A doctor needs a department to exist first, an
 * appointment needs a doctor and a patient, and so on.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,     // 1. departments
            MedicineSeeder::class,       // 2. medicine catalogue
            UserSeeder::class,           // 3. admin + reception accounts
            DoctorSeeder::class,         // 4. doctors + their weekly schedules
            PatientSeeder::class,        // 5. patients
            AppointmentSeeder::class,    // 6. appointments
            PrescriptionSeeder::class,   // 7. diagnoses + prescriptions
        ]);

        $this->command->newLine();
        $this->command->info('=================================================');
        $this->command->info('  The database is ready. Test accounts:');
        $this->command->info('=================================================');
        $this->command->info('  Admin      : admin@clinic.com      / password123');
        $this->command->info('  Doctor     : mohamed@clinic.com    / password123');
        $this->command->info('  Reception  : reception@clinic.com  / password123');
        $this->command->info('  Patient    : ahmed@clinic.com      / password123');
        $this->command->info('=================================================');
        $this->command->newLine();
    }
}
