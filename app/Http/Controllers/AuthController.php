<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Scholar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'string|required',
            'password' => 'string'
        ],[
            'username.required' => 'Email/Student-ID is required'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'errors' => $validator->errors()
            ]);
        }

        if(!$token = Auth::setTTL(1440)->attempt($request->only(['username', 'password'])))
        {
            return response()->json([
                'status' => false,
                'message' => 'Email and Password does not match with our record.'
            ], 401);
        }

        $user = auth()->user();

        return response()->json([
            'status' => true,
            'message' => 'User successfully logged in.',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $this->getUser()
        ]);

    }

    public function logout(Request $request)
    {
        auth()->logout(true);

        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out!'
        ]);
    }

    private function getUser()
    {
        $user = request()->user();

        if($user->account_type == 1)
        {
            $user = Admin::
                join('users', 'users.user_id', '=', 'admins.id')
                ->select([
                    'account_type',
                    'username',
                    'first_name',
                    'last_name',
                    'admins.created_at'
                ])
                ->where('account_type', 1)
                ->find($user->user_id);
            return $user;
        }

        $user = Scholar::
            join('users', 'users.user_id', '=', 'scholars.id')
            ->join('scholarships', 'scholarships.id', '=', 'scholars.scholarship_id')
            ->select([
                'account_type',
                'first_name',
                'middle_name',
                'last_name',
                'phone_number',
                'id_number',
                'department',
                'course',
                'major',
                'year_level',
                'email',
                'scholarships.scholarship_name',
                'scholars.created_at'
            ])
            ->where('account_type', 2)
            ->find($user->user_id);
        return $user;
    }

}
