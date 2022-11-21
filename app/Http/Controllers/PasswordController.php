<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\PasswordReset;
use App\Models\Scholar;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class PasswordController extends Controller
{
    public function changePasswordRequest(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(),[
            'id_number' => 'required'
        ]);

        // check if validator fails
        if($validator->fails())
        {
            return response()->json([
                'errors' => $validator->errors()
            ]);
        }

        // find the id number in scholars table
        $scholar = Scholar::where('id_number', $request->id_number)->first();

        // check if scholar not found
        if(!$scholar)
        {
            return response()->json([
                'status' => false,
                'message' => 'Scholar ID number does not exist in our record.'
            ]);
        }

        // generate random code
        $code = rand(1000, 9999);

        // update or create record in password reset
        PasswordReset::updateOrCreate(
            ['scholar_id' => $scholar->id],
            ['code' => $code]
        );



        try{
            $receiverNumber ='+63'.$scholar->phone_number;
            $message = 'Password request code '. $code;
            SMSHelper::send($receiverNumber, $message);

            return response()->json([
                'status' => true,
                'message' => 'Your request has been process kindly check your sms for confirmation code'
            ]);
        }catch(Exception $e)
        {
            return response()->json([
                'status' => false,
                'message' => 'Error has been occured kindly call the administrator'
            ]);
        }
    }
}
