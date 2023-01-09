<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentHistory;
use App\Models\Scholar;
use App\Models\ScholarHistory;
use App\Models\Scholarship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScholarController extends Controller
{

    public function index(Request $request)
    {

        $scholars_id = Scholar::when($request->scholarName != '', function ($query) use ($request) {   //Added for search
                $query->where('first_name', 'LIKE', "%$request->scholarName%")
                ->orwhere('last_name', 'LIKE', "%$request->scholarName%");
            })
            ->when($request->scholarship != 0, function ($query) use ($request) {
                $query->where('scholarship_id', "$request->scholarship");
            })            
            ->pluck('id'); //remove pagination

            $scholar_history = ScholarHistory::whereIn('scholar_id', $scholars_id)->when($request->academicYear != 0, function ($query) use ($request) {
                $query->where('academic_year', $request->academicYear);
            })->pluck('scholar_id');

            $scholars = Scholar::join('scholar_histories', 'scholar_histories.scholar_id', '=', 'scholars.id')
            ->join('scholarships', 'scholarships.id', '=', 'scholars.scholarship_id')
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
                'scholars.created_at',
                'scholar_histories.academic_year',
            ])
            ->whereIn('scholars.id', $scholar_history)
            ->get(); //remove pagination


        return response()->json(
            $scholars
        );
    }


    public function recipient()
    {
        $scholars = Scholar::join('scholarships', 'scholarships.id', '=', 'scholars.scholarship_id')
        ->select(DB::raw("CONCAT(scholars.id_number,' | ' ,scholars.first_name, ' ' ,scholars.last_name) AS display_name"), 'scholars.id', 'scholarships.scholarship_name')
        ->where('id_number', 'NOT LIKE', '%-old%')
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
            // 'major' => 'required|string', // not required
            'year_level' => 'required|string',
            'scholarship' => 'required|string',
            'academic_year' => 'required',
            'semester' => 'required'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'errors' => $validator->errors()
            ]);
        }

        $scholarship = Scholarship::select('id')->find($request->scholarship);

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

        $academicYear = AcademicYear::where('academic_year', $request->academic_year)->first();
        ScholarHistory::create([
            'scholar_id' => $scholar->id,
            'academic_year_id' => $academicYear->id, 
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'qualified' => false
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

    public function qualifyScholar(Request $request)
    {
        $scholar_history = ScholarHistory::where('id', $request->scholar_history_id)->first();

        $scholar = Scholar::where('id', $scholar_history->scholar_id)->first();

        $academicYear = AcademicYear::where('academic_year', $request->year)->first();

        $user = User::where('account_type', 2)->where('user_id', $scholar->id)->first();

        $id_number = $scholar->id_number;

        // return response()->json([$request->year == $request->year_prev, $request->year, $request->year_prev]);

        if($request->year != $request->year_prev){
            $scholar->update(['id_number' => $scholar->id_number.'-old']);
            $scholar_history->update(['qualified' => true]);
            

            $new_scholar = Scholar::create([
                'first_name' => $scholar->first_name,
                'middle_name' => $scholar->middle_name,
                'last_name' => $scholar->last_name,
                'phone_number' => $scholar->phone_number,
                'email' => $scholar->email,
                'id_number' => $id_number,
                'department' => $scholar->department,
                'course' => $scholar->course,
                'major' => $scholar->major,
                'year_level' => $scholar->year_level,
                'scholarship_id' => $scholar->scholarship_id
            ]);

            $user->update(['user_id' => $new_scholar->id]);

            $new_scholar_history = ScholarHistory::create([
                'scholar_id' => $new_scholar->id,
                'academic_year_id' => $academicYear->id, 
                'academic_year' => $request->year,
                'semester' => $request->semester,
                'qualified' => false
            ]);

            
        }else{
            $scholar_history->update(['qualified' => true]);
            $new_scholar_history = ScholarHistory::create([
                'scholar_id' => $scholar->id,
                'academic_year_id' => $academicYear->id, 
                'academic_year' => $request->year,
                'semester' => $request->semester,
                'qualified' => false
            ]);

            $document_prev = Document::where('scholar_history_id', $scholar_history->id)->latest()->get();           

            foreach($document_prev as $document)
            {
                $document_history_prev = DocumentHistory::where('document_id', $document_prev)->get();
                if($document->document_for != 'Grades'){
                    $document_new = Document::create([
                        'filename' => $document->filename,
                        'scholar_history_id' => $new_scholar_history->id,
                        'document_for' => $document->document_for,
                        'academic_year' => $document->academic_year, //need to change
                        'file_path' => $document->file_path
                    ]);
            
                    DocumentHistory::create([
                        'document_id' => $document_new->id,
                        'status' => $document_history_prev->status
                    ]);
                }

            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Scholar successfully qualified'
        ]);
    }

    
    public function getAcademicYears()
    {
        $yearOldest = Scholar::oldest()->first();
        $yearLatest = Scholar::latest()->first();

        $academicYears = [];

        foreach (range($yearOldest->created_at->format('Y') - 1, $yearLatest->created_at->format('Y') + 1) as $i)
        {
            array_push($academicYears, $i .'-'. ( (int)$i + 1));
        }


        return response()->json([
            'status' => true,
            'academicYears' => $academicYears
        ]);
    }
}
