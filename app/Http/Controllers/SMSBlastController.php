<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\Scholar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SMSBlastController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_name' => 'required|string|min:2',
            'recipients' => 'required|array',
            'sms_detail' => 'required'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        foreach($request->recipients as $recipient)
        {
            if($recipient != 0){
                $scholar = Scholar::find($recipient);
                $message = "Sender Name: ". $request->sender_name ."\nContent: ". $request->sms_detail;
                SMSHelper::send($scholar->phone_number, $message);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'SMS has been sent!'
        ]);
    }
}
