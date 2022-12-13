<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScholarController extends Controller
{

    public function index(Request $request)
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
            ])
            ->when($request->scholarName != '', function ($query) use ($request) {   //Added for search
                $query->where('first_name', 'LIKE', "%$request->scholarName%")
                ->orwhere('last_name', 'LIKE', "%$request->scholarName%");
            })
            ->when($request->scholarship != 0, function ($query) use ($request) {
                $query->where('scholarship_id', "$request->scholarship");
            })
            ->get(); //remove pagination



        return response()->json(
            $scholars
        );
    }


    public function recipient()
    {
        $scholars = Scholar::join('scholarships', 'scholarships.id', '=', 'scholars.scholarship_id')
        // ->select([
        //     'scholars.id',
        //     'first_name',
        //     'last_name',
        //     'id_number',
        //     'scholarships.scholarship_name',
        // ])->get();
        ->select(DB::raw("CONCAT(scholars.id_number,' | ' ,scholars.first_name, ' ' ,scholars.last_name) AS display_name"), 'scholars.id', 'scholarships.scholarship_name')
        ->get();

        return response()->json(
            $scholars
        );
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'middle_name' => 'string',
            'last_name' => 'required|string',
            'phone_number' => 'required|numeric',
            'email' => 'required|email|unique:scholars',
            'id_number' => 'required|max:12|unique:scholars',
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

        $scholarship = Scholarship::select('id')->where('scholarship_name', 'like', "$request->scholarship%")->first();

        $scholar = Scholar::create([
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
            'scholarship_id' => $scholarship->id
        ]);

        $password = "slsu-spis-". strtolower($scholar->last_name);

        User::create([
            'account_type' => 2,
            'user_id' => $scholar->id,
            'username' => $scholar->id_number,
            'password' => bcrypt($password)
        ]);

        SMSHelper::send($scholar->phone_number, "Hello $scholar->first_name,This is to inform you that your successfully registered to SLSU SPIS your temporary password: ". $password);

        return response()->json([
            'status' => true,
            'message' => 'New scholar has been added to database'
        ]);
    }

    public function show($id_number)
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
        ])->where('id_number', $id_number)->get();

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

    public function update(Request $request)
    {
        $parameters = $request->all();
        $parameters['id'] = $request->id_number;

        $validator = Validator::make($parameters, [
            // 'id' => Rule::exists('scholars')->where(function ($query) use ($parameters) {
            //     return $query->where('id_number', $parameters['id']);
            // }),
            'first_name' => 'string',
            'middle_name' => 'string',
            'last_name' => 'string',
            'phone_number' => 'numeric',
            // 'email' => 'email|unique:scholars', //error on validating own email of scholar
            // 'id_number' => 'max:12|unique:scholars',
            'department' => 'string',
            'course' => 'string',
            'major' => 'string',
            'year_level' => 'string',
            'scholarship' => 'string',
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $scholar = Scholar::where('id_number', $parameters['id'])->first();
        $updateResult = $scholar->update($parameters);

        if(!$updateResult) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to update scholar information'
            ]);
        }

        return response()->json([
            'status' => true,
            'messsage' => "Scholar ID Number: " . $parameters['id'] . " information has been updated"
        ]);
    }

    public function destroy($id_number)
    {
        $scholar = Scholar::where('id_number', $id_number)->first();

        if(!$scholar) {
            return response()->json([
                'status' => false,
                'message' => 'Scholar not found in our database'
            ]);
        }

        $deleteResult = $scholar->delete();
        if(!$deleteResult) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to delete scholar information'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Scholar ID Number: '. $id_number . ' has been deleted!'
        ]);
    }
}
