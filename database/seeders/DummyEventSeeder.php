<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Event;
use App\Models\EventIndividual;
use App\Models\Scholar;
use App\Models\ScholarHistory;
use Illuminate\Database\Seeder;

class DummyEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Event::truncate();
        EventIndividual::truncate();
        Event::factory()->count(1)->create();

        $event = Event::inRandomOrder()->limit(1)->first();
        $scholars = ScholarHistory::inRandomOrder()->limit(1)->get();
        foreach($scholars as $scholar)
        {
            EventIndividual::create([
                'event_id' => $event->id,
                'scholar_history_id' => $scholar->id
            ]);
        }
    }
}
