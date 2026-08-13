<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Cardiology',    'description' => 'Diagnosis and treatment of heart and blood vessel conditions.'],
            ['name' => 'Dentistry',     'description' => 'Care of the teeth, gums and mouth.'],
            ['name' => 'Orthopedics',   'description' => 'Bones, joints, muscles and sports injuries.'],
            ['name' => 'Dermatology',   'description' => 'Skin, hair and nail conditions.'],
            ['name' => 'Pediatrics',    'description' => 'Medical care for infants, children and teenagers.'],
            ['name' => 'Neurology',     'description' => 'Disorders of the brain, spine and nervous system.'],
            ['name' => 'Ophthalmology', 'description' => 'Eye examinations and vision care.'],
            ['name' => 'ENT',           'description' => 'Ear, nose and throat conditions.'],
        ];

        foreach ($departments as $department) {
            // firstOrCreate means: running the seeder twice will not duplicate rows.
            Department::firstOrCreate(['name' => $department['name']], $department);
        }
    }
}
