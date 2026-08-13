<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            ['Panadol',        'Paracetamol 500 mg tablets for pain and fever.',     'Painkiller',   15.00,  480],
            ['Augmentin 1g',   'Amoxicillin + clavulanic acid 1 g tablets.',         'Antibiotic',   85.50,  210],
            ['Brufen 400',     'Ibuprofen 400 mg anti-inflammatory tablets.',        'Painkiller',   28.00,  350],
            ['Voltaren 50',    'Diclofenac 50 mg tablets for joint pain.',           'Painkiller',   32.00,  260],
            ['Amoxil 500',     'Amoxicillin 500 mg capsules.',                       'Antibiotic',   45.00,  300],
            ['Zithromax 500',  'Azithromycin 500 mg tablets.',                       'Antibiotic',   95.00,  140],
            ['Vitamin C 1000', 'Vitamin C 1000 mg effervescent tablets.',            'Vitamin',      40.00,  520],
            ['Vitamin D3',     'Cholecalciferol 5000 IU capsules.',                  'Vitamin',      60.00,  310],
            ['Ferrous Sulfate','Iron supplement for anaemia.',                       'Supplement',   35.00,  240],
            ['Omega 3',        'Fish oil 1000 mg soft gel capsules.',                'Supplement',   75.00,  190],
            ['Concor 5',       'Bisoprolol 5 mg tablets for blood pressure.',        'Cardiac',      52.00,  180],
            ['Aspocid 75',     'Low dose aspirin 75 mg for blood thinning.',         'Cardiac',      12.00,  420],
            ['Lipitor 20',     'Atorvastatin 20 mg tablets for cholesterol.',        'Cardiac',      98.00,  160],
            ['Glucophage 500', 'Metformin 500 mg tablets for diabetes.',             'Diabetes',     30.00,  380],
            ['Lantus',         'Insulin glargine injection pen.',                    'Diabetes',    320.00,   60],
            ['Ventolin',       'Salbutamol inhaler for asthma.',                     'Respiratory',  55.00,  145],
            ['Claritine',      'Loratadine 10 mg antihistamine tablets.',            'Allergy',      38.00,  270],
            ['Zyrtec',         'Cetirizine 10 mg antihistamine tablets.',            'Allergy',      42.00,  230],
            ['Nexium 40',      'Esomeprazole 40 mg for stomach acid.',               'Digestive',    88.00,  200],
            ['Buscopan',       'Hyoscine butylbromide for stomach cramps.',          'Digestive',    26.00,  290],
            ['Antinal',        'Nifuroxazide capsules for diarrhoea.',               'Digestive',    22.00,  330],
            ['Betadine',       'Povidone iodine antiseptic solution.',               'Antiseptic',   30.00,  410],
            ['Fucidin Cream',  'Fusidic acid 2% topical antibiotic cream.',          'Dermatology',  48.00,  175],
            ['Tobradex',       'Tobramycin + dexamethasone eye drops.',              'Ophthalmic',   66.00,  120],
        ];

        foreach ($medicines as [$name, $description, $category, $price, $quantity]) {
            Medicine::firstOrCreate(['name' => $name], [
                'description' => $description,
                'category'    => $category,
                'price'       => $price,
                'quantity'    => $quantity,
                'status'      => 'active',
            ]);
        }
    }
}
