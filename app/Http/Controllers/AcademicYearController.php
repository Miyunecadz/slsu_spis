<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    //

    public function index()
    {
        $academicYears = AcademicYear::orderBy('academic_year', 'desc')->get();

        return response()->json([
            'status' => true,
            'academicYears' => $academicYears
        ]);
    }


    public function show()
    {
        $academicYears = AcademicYear::where('active', true)->get();

        return response()->json([
            'status' => true,
            'activeYear' => $academicYears
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year' => ['required', Rule::unique('academic_years')->where(function($query) use ($request) {
                return $query->where('academic_year', '=',$request->academic_year);
            })]
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $academicYear = AcademicYear::create(['academic_year' => $request->academic_year, 'active' => false]);

        return response()->json([
            'status' => true,
            'message' => 'AcademicYear successfully created'
        ]);
    }

    public function update(Request $request)
    {
        AcademicYear::where('active', true)->update(['active' => false]);

        $academicYear = AcademicYear::find($request->id)->update(['active' => true]);

        return response()->json([
            'status' => true,
            'message' => 'AcademicYear successfully updated'
        ]);
    }
}
