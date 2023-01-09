<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Scholar;
use App\Models\ScholarHistory;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyScholarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $counts = 1;

        for($x = 0 ; $x < $counts ; $x++)
        {
            $scholarship = Scholarship::inRandomOrder()->limit(1)->first();
            Scholar::truncate();
            $scholars = Scholar::factory()->count($counts)->create([
                'scholarship_id' => $scholarship->id
            ]);

            ScholarHistory::truncate();
            AcademicYear::truncate();
            $academicYear = AcademicYear::factory()->create([
                'academic_year' => '2022-2023',
                'active' => true
            ]);

            foreach($scholars as $scholar)
            {

                ScholarHistory::factory()->create([
                    'scholar_id' => $scholar->id,
                    'academic_year' => $academicYear->academic_year,
                    'academic_year_id' => $academicYear->id,
                    'semester' => '1st Semester',
                    'qualified' => false
                ]);

                User::factory()->create([
                    'account_type' => 2,
                    'user_id' => $scholar->id,
                ]);
            }
        }
    }
}
