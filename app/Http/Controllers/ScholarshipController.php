<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScholarshipController extends Controller
{
    public function index()
    {
        $scholarship = Scholarship::select('id', 'scholarship_name', 'scholarship_detail')->get();

        return response()->json(
            $scholarship
        );
    }

    public function scholarCounts(Request $request)
    {
        $scholarship = Scholarship::withCount('scholars')
        ->when($request->has('limit'), function ($query) use ($request) {
            $query->limit($request->limit);
        })
        ->get();

        return response()->json(
            $scholarship
        );
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scholarship_name' => 'required|string',
            'scholarship_detail' => 'required|string'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'errors' => $validator->errors()
            ]);
        }

        Scholarship::create([
            'scholarship_name' => $request->scholarship_name,
            'scholarship_detail' => $request->scholarship_detail
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Scholarship successfully added!'
        ]);        
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => Rule::exists('scholarships')->where(function ($query) use ($request) {
                return $query->find($request->id);
            }),
            'scholarship_name' => 'required|string',
            'scholarship_detail' => 'required|string'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $updateResult = Scholarship::find($request->id)->update($request->all());
        
        if(!$updateResult) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to update scholarship information'
            ]);
        }

        return response()->json([
            'status' => true,
            'messsage' => "Scholarship: " . $request->scholarship_name . " information has been updated"
        ]);
    }
}
