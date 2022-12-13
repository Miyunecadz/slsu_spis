<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\ConcernReply;
use App\Models\Scholar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConcernController extends Controller
{
    
    public function index()
    {
        $concerns = Concern::with('replies')->withCount('replies')->orderBy('created_at', 'desc')->get();

        return response()->json($concerns);
    }

    public function scholarConcern(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scholar_id' => Rule::exists('scholars')->where(function ($query) use ($request) {
                return $query->find($request->scholar_id);
            })
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $concerns = Concern::where('scholar_id', $request->scholar_id)->with('replies')->withCount('replies')->orderBy('created_at', 'desc')->get();

        return response()->json($concerns);
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

        $concerns = Concern::whereIn('scholar_id', $scholars)->with('scholars')->with('replies')->withCount('replies')->orderBy('created_at', 'desc')->get();

        return response()->json($concerns);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => Rule::exists('scholars')->where(function ($query) use ($request) {
                return $query->find($request->scholar_id);
            }),
            'details' => 'required'
        ]);

        if($validator->fails())
        {   
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        Concern::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Query/Concern has been successfully added!'
        ]);
    }

    public function storeReply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => Rule::exists('concerns')->where(function ($query) use ($request) {
                return $query->find($request->concern_id);
            }),
            'reply' => 'required',
            'user_id' => 'required'
        ]);

        if($validator->fails())
        {   
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        ConcernReply::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Query/Concern has been successfully added!'
        ]);
    }

    public function destroy(Request $request)
    {

    }
}
