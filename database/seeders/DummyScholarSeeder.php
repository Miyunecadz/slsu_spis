<?php

namespace Database\Seeders;

use App\Models\Scholar;
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

            foreach($scholars as $scholar)
            {
                User::factory()->create([
                    'account_type' => 2,
                    'user_id' => $scholar->id,
                ]);
            }
        }
    }
}
