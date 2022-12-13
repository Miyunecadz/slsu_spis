<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentHistory;
use App\Models\Scholar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documentQuery = new Document;
        if($request->has('id_number')) {
            $scholar = Scholar::where('id_number', $request->id_number)->first();
            $documentQuery->where('scholar_id', $scholar->id);
        }

        return response()->json([
            'status' => true,
            'documents' => $documentQuery->with('document_histories')->get()
        ]);

    }
    public function upload(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'scholar_id' => 'required|exists:scholars,id',
            'file' => 'required'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        $temporaryFilename = "slsu-spis-".rand(0001, 9999);
        $scholar = Scholar::find($request->scholar_id);
        $path = Storage::putFileAs('public', $request->file, $scholar->first_name.' '.$scholar->id_number.'-'. $temporaryFilename . $request->filename . "." . $request->file->getClientOriginalExtension());

        $document = Document::create([
            'filename' => $request->file,
            'scholar_id' => $scholar->id,
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
}
