<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use App\Models\Scholar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RequirementController extends Controller
{
    //

    public function index() 
    {
        $requirements = Requirement::orderBy('scholarship_id', 'asc')
            ->orderBy('requirement', 'asc')
            ->with('scholarships')
            ->get();



        return response()->json([
            'status' => true,
            'requirements' => $requirements
        ]);
    }

    public function show(Request $request) 
    {
        $requirements = Scholar::find($request->id)->scholarships->requirements;

        return response()->json([
            'status' => true,
            'requirements' => $requirements
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scholarship_id' => 'exists:scholarships,id',
            'requirement' => ['required', Rule::unique('requirements')->where(function($query) use ($request) {
                return $query->where('scholarship_id', '=',$request->scholarship_id);
            })]
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $requirement = Requirement::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Requirement successfully saved!'
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scholarship_id' => 'exists:scholarships,id',
            'requirement' => [Rule::unique('requirements')->where(function($query) use ($request) {
                return $query->where('scholarship_id', '!=',$request->scholarship_id);
            })]
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $requirement = Requirement::find($request->id)->update($request->all());
        
        return response()->json([
            'status' => true,
            'message' => 'Requirement successfully updated!'
        ]);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'exists:requirements,id'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $requirement = Requirement::find($request->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Requirement successfully deleted!'
        ]);
    }
}
