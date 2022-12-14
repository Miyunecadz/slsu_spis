<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Seeder;

class ScholarshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Scholarship::truncate();

        Scholarship::create([
            'scholarship_name' => 'DOST-SEI',
            'scholarship_detail' => 'Studetns who have a strong aptitude in science and mathematics and are willing to pursue careers in the fields of scinece and technology are eligible to receive scholarships through the DOST-SEI Merit Scholarship Program. This scholarship was formerly known as the NSDB or NSTA scholarship under Repulibc Act No. 2067.'
        ]);

        Scholarship::create([
            'scholarship_name' => 'TES',
            'scholarship_detail' => 'The Tertiary Education Subsidy (TES), one of the fundamental initiavtives of Republic Act No. 10931, the Universal Access to Quality Tertiary Education Acts, provides financing for all Filipino students from the poorest-of-the-poor households enrolling in public and private HEIs.'
        ]);

        Scholarship::create([
            'scholarship_name' => 'FHE',
            'scholarship_detail' => 'Free Higher Education is not a financial assistance program for students (StuFAP). It is distributed to all qualified pupils. AS a result, as a general rule, students can benefit from both Free Higher Education and existing merit-based StuFAPs. Section 46 of the IRR of RA 10931.'
        ]);

        Scholarship::create([
            'scholarship_name' => 'CHED',
            'scholarship_detail' => 'The CHED scholarship is intended to provide financial support to students enrolled in accredited public or private Higher Education Institutions (HEIs). This ensures that education is available to all, particularly disadvantaged and deserving pupils.'
        ]);
    }
}
