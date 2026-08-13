<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates 25 patients. The first one (ahmed@clinic.com) is the account you
 * use to test the patient side of the system.
 */
class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            ['Ahmed Ali',        'ahmed@clinic.com',      '01011112222', '1995-04-12', 'male',   'O+',  'Nasr City, Cairo'],
            ['Sara Mohamed',     'sara.p@clinic.com',     '01022223333', '1990-09-30', 'female', 'A+',  'Maadi, Cairo'],
            ['Omar Hassan',      'omar.p@clinic.com',     '01033334444', '1988-01-22', 'male',   'B+',  'Dokki, Giza'],
            ['Mona Sayed',       'mona@clinic.com',       '01044445555', '2000-07-08', 'female', 'AB+', 'Heliopolis, Cairo'],
            ['Kareem Adel',      'kareem@clinic.com',     '01055556666', '1975-11-19', 'male',   'O-',  'Zamalek, Cairo'],
            ['Yasmin Tarek',     'yasmin@clinic.com',     '01066667777', '1998-03-05', 'female', 'A-',  'Mohandessin, Giza'],
            ['Hassan Mahmoud',   'hassan@clinic.com',     '01077778888', '1982-06-27', 'male',   'B-',  '6th of October City'],
            ['Fatma Gamal',      'fatma@clinic.com',      '01088889999', '1993-12-14', 'female', 'O+',  'Shubra, Cairo'],
            ['Mostafa Nabil',    'mostafa@clinic.com',    '01099990000', '1979-02-09', 'male',   'A+',  'New Cairo'],
            ['Aya Ashraf',       'aya@clinic.com',        '01111112222', '2002-08-21', 'female', 'AB-', 'Faisal, Giza'],
            ['Ibrahim Fathy',    'ibrahim@clinic.com',    '01122223333', '1968-05-03', 'male',   'O+',  'Helwan, Cairo'],
            ['Nourhan Salah',    'nourhan@clinic.com',    '01133334444', '1996-10-17', 'female', 'B+',  'Agouza, Giza'],
            ['Ali Ramadan',      'ali@clinic.com',        '01144445555', '1985-04-29', 'male',   'A+',  'Sheikh Zayed'],
            ['Dina Wael',        'dina@clinic.com',       '01155556666', '1991-01-11', 'female', 'O-',  'Rehab City, Cairo'],
            ['Tamer Sobhy',      'tamer@clinic.com',      '01166667777', '1973-09-24', 'male',   'AB+', 'Haram, Giza'],
            ['Salma Ezzat',      'salma@clinic.com',      '01177778888', '2004-06-16', 'female', 'A+',  'Manial, Cairo'],
            ['Marwan Sherif',    'marwan@clinic.com',     '01188889999', '1999-03-30', 'male',   'B+',  'Obour City'],
            ['Rania Hosny',      'rania@clinic.com',      '01199990000', '1987-07-13', 'female', 'O+',  'Madinat Nasr, Cairo'],
            ['Sherif Magdy',     'sherif@clinic.com',     '01211112222', '1994-11-02', 'male',   'A-',  'Giza Square, Giza'],
            ['Hana Yasser',      'hana@clinic.com',       '01222223333', '2001-02-25', 'female', 'B-',  'Katameya, Cairo'],
            ['Waleed Fahmy',     'waleed@clinic.com',     '01233334444', '1980-08-07', 'male',   'O+',  'Imbaba, Giza'],
            ['Noha Sami',        'noha@clinic.com',       '01244445555', '1992-05-19', 'female', 'A+',  'Ain Shams, Cairo'],
            ['Ziad Alaa',        'ziad@clinic.com',       '01255556666', '2006-12-01', 'male',   'AB+', 'Badr City'],
            ['Amira Reda',       'amira@clinic.com',      '01266667777', '1977-10-09', 'female', 'O-',  'Sayeda Zeinab, Cairo'],
            ['Mahmoud Anwar',    'mahmoud@clinic.com',    '01277778888', '1989-06-04', 'male',   'B+',  'Tagamoa, New Cairo'],
        ];

        foreach ($patients as [$name, $email, $phone, $dob, $gender, $blood, $address]) {

            $user = User::firstOrCreate(['email' => $email], [
                'name'     => $name,
                'phone'    => $phone,
                'password' => 'password123',
                'role'     => User::ROLE_PATIENT,
                'status'   => 'active',
            ]);

            Patient::firstOrCreate(['user_id' => $user->id], [
                'dob'         => $dob,
                'gender'      => $gender,
                'blood_group' => $blood,
                'address'     => $address,
            ]);
        }
    }
}
