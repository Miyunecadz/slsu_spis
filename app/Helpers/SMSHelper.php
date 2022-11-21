<?php

namespace App\Helpers;

use Twilio\Rest\Client;

class SMSHelper
{
    public static function send($phone_number, $message)
    {
        $receiverNumber ='+63'.$phone_number;

        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_TOKEN");
        $twilio_number = getenv("TWILIO_FROM");

        $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number,
                    'body' => $message]);
    }
}
