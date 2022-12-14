<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => Rule::exists('admins')->where(function ($query) use ($request) {
                return $query->find($request->id);
            }),
            'first_name' => 'required|string',
            'last_name' => 'required|string'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }


        $admin = Admin::find($request->id);
        $updateResult = $admin->update($request->all());

        if(!$updateResult) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to update admin information'
            ]);
        }

        return response()->json([
            'status' => true,
            'messsage' => "Admin information has been updated"
        ]);
    }
}
