<?php

namespace App\Http\Controllers;

use App\Models\Scholar;
use App\Models\Scholarship;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ScholarController extends Controller
{

    public function index()
    {
        $scholars = Scholar::join('scholarships', 'scholarships.id', '=', 'scholars.scholarship_id')
            ->select([
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
            ])->paginate(10);

        return response()->json([
            $scholars
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'middle_name' => 'string',
            'last_name' => 'required|string',
            'phone_number' => 'required|numeric|max:10',
            'email' => 'required|email',
            'id_number' => 'required|max:12',
            'department' => 'required|string',
            'course' => 'required|string',
            'major' => 'required|string',
            'year_level' => 'required|string',
            'scholarship' => 'required|string',
        ]);

        if($validator->fails())
        {
            return response()->json([
                'errors' => $validator->errors()
            ]);
        }

        $scholarship_id = Scholarship::select('id')->where('scholarship_name', 'like', "$request->scholarship%")->first();

        Scholar::create([
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'last_name' => $request->input('last_name'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'id_number' => $request->input('id_number'),
            'department' => $request->input('department'),
            'course' => $request->input('course'),
            'major' => $request->input('major'),
            'year_level' => $request->input('year_level'),
            'scholarship_id' => $scholarship_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'New scholar has been added to database'
        ]);
    }

    public function show($id)
    {
        $scholar = Scholar::join('scholarships', 'scholarships.id', '=', 'scholars.scholarship_id')
        ->select([
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
        ])->find($id);

        if(!$scholar)
        {
            return response()->json([
                'status' => false,
                'message' => 'Scholar does not found in our record.'
            ]);
        }

        return response()->json([
            'status' => true,
            'scholar' => $scholar
        ]);
    }
}
