<?php

namespace App\Jobs;

use App\Helpers\SMSHelper;
use App\Models\Event;
use App\Models\EventIndividual;
use App\Models\Scholar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpsertEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $recipients, $event_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($recipients, $event_id)
    {
        $this->recipients = $recipients;
        $this->event_id = $event_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->recipients as $recipient)
        {
            EventIndividual::create([
                'event_id' => $this->event_id,
                'scholar_id' => $recipient
            ]);

            $scholar = Scholar::find($recipient);
            SMSHelper::send($scholar->phone_number, 'New Event has been posted! For more information check the event details.');
        }
    }
}
