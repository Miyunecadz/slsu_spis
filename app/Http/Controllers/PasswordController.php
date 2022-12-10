<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\PasswordReset;
use App\Models\Scholar;
use App\Models\User;
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

        $account = User::where('user_id', $scholar->id)->where('account_type', 2)->first();

        if(!$account->password_modified != now()->format('Y-m-d'))
        {
            return response()->json([
                'status' => false,
                'message' => 'You changed your password recently, Unable to change password'
            ]);
        }

        // generate random code
        $temporaryPassword = "slsu-spis-".rand(1000, 9999);

        $account->password = bcrypt($temporaryPassword);
        $account->password_modified = now()->addMonth()->format('Y-m-d');
        $account->save();

        try{
            $receiverNumber = $scholar->phone_number;
            $message = 'Your temporary password is '. $temporaryPassword . ". Don't share your credentials to others.";
            SMSHelper::send($receiverNumber, $message);

            return response()->json([
                'status' => true,
                'message' => 'Your request has been process kindly check your sms.'
            ]);
        }catch(Exception $e)
        {
            return response()->json([
                'status' => false,
                'message' => 'Error has been occured kindly call the administrator',
                'error' => $e->getMessage()
            ]);
        }
    }
}
