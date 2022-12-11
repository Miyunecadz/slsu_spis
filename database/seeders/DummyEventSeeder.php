<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventIndividual;
use App\Models\Scholar;
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
        $scholars = Scholar::inRandomOrder()->limit(1)->get();
        foreach($scholars as $scholar)
        {
            EventIndividual::create([
                'event_id' => $event->id,
                'scholar_id' => $scholar->id
            ]);
        }
    }
}
