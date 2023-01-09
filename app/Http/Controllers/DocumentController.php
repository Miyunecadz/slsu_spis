<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\Document;
use App\Models\DocumentHistory;
use App\Models\Requirement;
use App\Models\Scholar;
use App\Models\ScholarHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documentQuery = new Document;
        if ($request->has('id_number')) {
            $scholar = Scholar::where('id_number', $request->id_number)->first(); //wrong request. $request->scholar
            $scholar_history = ScholarHistory::where('scholar_id', $scholar->id)->latest()->first();
            $documentQuery = Document::where('scholar_history_id', $scholar_history->id); // bug here
        }

        return response()->json([
            'status' => true,
            'documents' => $documentQuery->with('document_histories')
            ->orderBy('created_at', 'desc')->get()
        ]);

    }

    public function search(Request $request)
    {
        $scholars = Scholar::when($request->has('scholarName') && $request->scholarName != '', function ($query) use ($request) {   //Added for search
            $query->where('first_name', 'LIKE', "%$request->scholarName%")
            ->orwhere('last_name', 'LIKE', "%$request->scholarName%");
        })
        ->when($request->has('scholarship') && $request->scholarship != 0, function ($query) use ($request) {
            $query->where('scholarship_id', "$request->scholarship");
        })
        ->when($request->has('scholarId_number'), function ($query) use ($request) {
            $query->where('id_number', $request->scholarId_number);
        })
        ->pluck('id')->all();   

        $scholar_history = ScholarHistory::whereIn('scholar_id', $scholars)
        ->when($request->academic_year != 0, function ($query) use ($request) {
            $query->where('academic_year', $request->academic_year);
        })
        ->when($request->semester != 0, function ($query) use ($request) {
            $query->where('semester', $request->semester);
        })
        ->pluck('id')->all();

           
        
        $documentQuery = Document::whereIn('scholar_history_id', $scholar_history)
                        ->with('document_histories')
                        ->with(['scholarHistories' => function ($query) {
                            return $query->with('scholars');
                        }])
                        ->orderBy('created_at', 'desc')
                        ->when($request->has('limit'), function ($query) use ($request) {
                            $query->limit($request->limit);
                        })
                        ->get();
        
        return response()->json([
            'status' => true,
            'documents' => $documentQuery,
            'scholars' => $scholars
        ]);

    }

    public function download(Request $request)
    {     
        $documentQuery = Document::where('filename',$request->filename)->first();

        $file_path = $documentQuery->file_path;

        $extension = explode(".", $request->fileName);
        $file_type = 'Image';

        if ($extension == 'pdf'){
            $file_type = 'PDF';
        }


        if (substr($file_path, 0, strlen('public/')) == 'public/') {
            $file_path = substr($file_path, strlen('public/'));
        } 

        $file = Storage::disk('public')->get($file_path);
        // $file = public_path($file_path);

        return response($file);

        $headers = [
            'Content-Type' => 'application/pdf',
         ];
  
        //  return response()->download($file, $request->filename ,$headers);
         return (new Response($file, 200));
    }

    public function update(Request $request)
    {       
        $documentQuery = DocumentHistory::find($request->document_history);
        $updateResult = $documentQuery->update($request->all());

        if ($request->status == 'denied'){
            $document = DocumentHistory::find($request->document_history)->documents->first();
            $scholar = ScholarHistory::find($document->scholar_history_id)->scholars();
            $message = "Sender Name: SLSU-SPIS Admin \nContent: Your document for ".$document->document_for." has been denied";
            SMSHelper::send($scholar->phone_number, $message);
        }

        if(!$updateResult) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to update scholar document'
            ]);
        }
        return response()->json([
            'status' => true,
            'documents' => $documentQuery
        ]);

    }

    public function upload(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'scholar_id' => 'required|exists:scholars,id',
            'file' => 'required',
            'document_for' => ['required', Rule::unique('documents')->where(function($query) use ($request) {
                $scholar_history = ScholarHistory::where('scholar_id', $request->scholar_id)->where('academic_year', '=', $request->academic_year)->latest()->first();
                return $query->where([['academic_year', '=', $request->academic_year], ['document_for', '=', $request->document_for], ['scholar_history_id', '=', $scholar_history->id]]); //$request->academic_year
            })]
        ], [
            'document_for' => 'Duplicate file for :attribute. Please delete duplicate file and try again.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        $scholar_history = ScholarHistory::where('scholar_id', $request->scholar_id)->where('academic_year',  $request->academic_year)->latest()->first();

        // return response()->json($scholar_history);
        $fileName = $request->file->getClientOriginalName();
      

        $index = 1;
        while (Storage::exists($request->path . '/' . $fileName)) {
            $separatedName = explode(".", $fileName);
            $fileName = $separatedName[0] . " ($index)." . $separatedName[1];
            $extension = $separatedName[1];
            $index++;
        }

        $path = Storage::putFileAs('public', $request->file, $fileName);

        
        $document = Document::create([
            'filename' => $fileName,
            'scholar_history_id' => $scholar_history->id,
            'document_for' => $request->document_for,
            'academic_year' => $request->academic_year, //need to change
            'file_path' => $path
        ]);

        DocumentHistory::create([
            'document_id' => $document->id,
            'status' => 'uploaded'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Document has been successfully uploaded'
        ]);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $document = Document::find($request->document_id);

        if (!$document) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to execute, Document not found!'
            ]);
        }

        Storage::delete('public/' . $document->filename);
        $document->delete();
        DocumentHistory::where('document_id', $document->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Document has been successfully removed!'
        ]);
    }


    public function checkScholarQualification(Request $request)
    {
        $requirements = Requirement::where('scholarship_id', $request->scholarship_id)->get();

        foreach($requirements as $requirement){
            $document = Document::where([['scholar_id', $request->scholar_id], ['academic_year', $request->academic_year], ['document_for', $requirement->requirement]])->get();

            $documentHistory = DocumentHistory::where('document_id', $document->id)->first();

            if($documentHistory->status != 'approved'){
                return response()->json([
                    'status' => false,
                    'message' => 'Scholar Not Qualified'
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Scholar is Qualified'
        ]);
    }

    public function getCheckList(Request $request, $id_number)
    {
        $isQualified = true;
        $checkList = [];

        $scholar = Scholar::where('id_number', $id_number)->first();

        $scholar_history = ScholarHistory::where('scholar_id', $scholar->id)
        ->where('academic_year', 'LIKE' , '%'.$request->academic_year.'%')
        ->where('semester', 'LIKE','%'.$request->semester.'%')
        // ->where('academic_year', $request->academic_year)
        // ->where('semester',$request->semester)
        ->first();

        $requirements = Requirement::where('scholarship_id', $scholar->scholarship_id)->get();
        
        if(empty($scholar_history)){
            return response()->json([
                'status' => false,
                'message' => 'No Data'
            ]);
        }
       
        foreach($requirements as $requirement){
            $document = Document::
            // where([['scholar_history_id', $scholar_history->id], ['academic_year', $request->academic_year], ['document_for', $requirement->requirement]])
            where('scholar_history_id', $scholar_history->id)
            ->where('document_for', '=', $requirement->requirement)
            ->get();
            
            try{
                $documentHistory = DocumentHistory::where('document_id', $document->id)->first();
                       
                $checkList[$requirement->requirement] =  $documentHistory->status;
    
                if($documentHistory->status != 'approved'){
                    $isQualified = false;
                }

            }catch(Exception $ex){
                $checkList[$requirement->requirement] =  'No Document';
                $isQualified = false;
            }
            
        }

        if(!(Arr::exists($checkList, 'Grade') || Arr::exists($checkList, 'Grades'))){
            $checkList['Grades'] = 'No Document';
            $isQualified = false;
        }


        return response()->json([
            'status' => true,
            'checkList' => $checkList,
            'isQualified' => $isQualified,
            'qualified' => $scholar_history->qualified,
            'scholar_history_id' => $scholar_history->id
        ]); 
    }
}