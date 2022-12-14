<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentHistory;
use App\Models\Scholar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documentQuery = new Document;
        if ($request->has('id_number')) {
            $scholar = Scholar::where('id_number', $request->id_number)->first(); //wrong request. $request->scholar
            $documentQuery->where('scholar_id', $scholar->id);
        }

        return response()->json([
            'status' => true,
            'documents' => $documentQuery->with('document_histories')->get()
        ]);

    }

    public function search(Request $request)
    {
        $scholars = Scholar::when($request->scholarName != '', function ($query) use ($request) {   //Added for search
            $query->where('first_name', 'LIKE', "%$request->scholarName%")
            ->orwhere('last_name', 'LIKE', "%$request->scholarName%");
        })
        ->when($request->scholarship != 0, function ($query) use ($request) {
            $query->where('scholarship_id', "$request->scholarship");
        })->pluck('id')->all();
        
        $documentQuery = Document::whereIn('scholar_id', $scholars)
                        ->with('document_histories')
                        ->with('scholars')
                        ->orderBy('created_at', 'desc')
                        ->when($request->has('limit'), function ($query) use ($request) {
                            $query->limit($request->limit);
                        })
                        ->get();
        
        return response()->json([
            'status' => true,
            'documents' => $documentQuery
        ]);

    }

    public function download(Request $request)
    {     
        $documentQuery = Document::where('filename',$request->filename)->first();

        $file_path = $documentQuery->file_path;

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
            'file' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $fileName = $request->file->getClientOriginalName();

        $index = 1;
        while (Storage::exists($request->path . '/' . $fileName)) {
            $separatedName = explode(".", $fileName);
            $fileName = $separatedName[0] . " ($index)." . $separatedName[1];
            $index++;
        }

        $path = Storage::putFileAs('public', $request->file, $fileName);

        $document = Document::create([
            'filename' => $fileName,
            'scholar_id' => $request->scholar_id,
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

}