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
            $scholar = Scholar::where('id_number', $request->id_number)->first(); //wrong request
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
            'filename' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $document = Document::where('filename', $request->filename)->first();

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