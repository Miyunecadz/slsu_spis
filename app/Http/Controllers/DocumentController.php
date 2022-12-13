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
        if ($request->has('id_number')) {
            $scholar = Scholar::where('id_number', $request->scholar)->first();
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
            'filename' => 'required',
            'scholar' => 'required|exists:scholars,id_number',
            'document' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $fileName = $request->filename . "." . $request->document->getClientOriginalExtension();

        $index = 1;
        while (Storage::exists('public/' . $fileName)) {
            $fileName = $request->filename . "_$index.".$request->document->getClientOriginalExtension();
            $index++;
        }

        $scholar = Scholar::where('id_number', $request->scholar)->first();
        $path = Storage::putFileAs('public', $request->document, $fileName);

        $document = Document::create([
            'filename' => $fileName,
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

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $document = Document::where('filename', $request->filename)->first();

        if(!$document) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to execute, Document not found!'
            ]);
        }

        Storage::delete('public/'.$document->filename);
        $document->delete();
        DocumentHistory::where('document_id', $document->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Document has been successfully removed!'
        ]);
    }

}