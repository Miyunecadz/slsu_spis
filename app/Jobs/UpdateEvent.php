<?php

namespace App\Jobs;

use App\Helpers\SMSHelper;
use App\Models\EventIndividual;
use App\Models\Scholar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventId, $recipients;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($eventId, $recipients)
    {
        $this->eventId = $eventId;
        $this->recipients = $recipients;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        EventIndividual::where('event_id', $this->eventId)->delete();

        foreach($this->recipients as $recipient)
        {
            EventIndividual::create([
                'event_id' => $this->eventId,
                'scholar_id' => $recipient
            ]);

            $scholar = Scholar::find($recipient);
            SMSHelper::send($scholar->phone_number, 'Event has been posted! For more information check the event details.');
        }
    }
}
