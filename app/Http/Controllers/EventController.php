<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\AcademicYear;
use App\Models\Event;
use App\Models\EventIndividual;
use App\Models\Scholar;
use App\Models\ScholarHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => Rule::exists('scholars')->where(function ($query) use ($request) {
                return $query->find($request->id);
            }),
            'academic_year' => 'required'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }
        if($request->has('id'))
        {
            $scholar_history_id = ScholarHistory::where('scholar_id', $request->id)
            ->where('academic_year', $request->academic_year)
            ->when($request->semester != 0, function ($query) use ($request) {
                $query->where('semester', $request->semester);
            })->latest()
            ->first();
            $event_individual = EventIndividual::where('scholar_history_id', $scholar_history_id->id)->pluck('event_id')->all();
            $events = Event::whereIn('id', $event_individual)->get();
        }
        else if($request->has('id_number')){
            $scholar = Scholar::where('id_number', $request->id_number)->first();
            $scholar_history_id = ScholarHistory::where('scholar_id', $scholar->id)
            ->where('academic_year', $request->academic_year)
            ->when($request->semester != 0, function ($query) use ($request) {
                $query->where('semester', $request->semester);
            })->latest()
            ->first();
            $event_individual = EventIndividual::where('scholar_history_id', $scholar_history_id->id)->pluck('event_id')->all();
            $events = Event::whereIn('id', $event_individual)->get();
        }
        else
        {
            $events = Event::with('eventIndividual')->get();
        }
        return response()->json($events); 
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'event_start' => 'required|date',
            'event_end' => 'required|date',
            'details' => 'required',
            'recipients' => 'required|array',
            'academic_year' => 'required'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        $event = Event::create(Arr::except($request->all(), ['recipients']));

       
        foreach($request->recipients as $recipient)
        {
            if($recipient != 0){
                $scholar_history_id = ScholarHistory::where('scholar_id', $recipient)
                ->where('academic_year', $request->academic_year)
                ->latest()->first();
                EventIndividual::create([
                    'event_id' => $event->id,
                    'scholar_history_id' => $scholar_history_id->id,
                    'scholar_id' => $recipient
                ]);
    
                $scholar = Scholar::find($recipient);
                SMSHelper::send($scholar->phone_number, 'New Event has been posted! ' . $request->details);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'New event has been successfully added!'
        ]);
    }

    public function show(Request $request)
    {
        $params['id'] = $request->id;
        $validator = Validator::make($params, [
            'id' => 'exists:events,id'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ]);
        }

        if ($request->has('id_number')){
            $event = Event::where('id_number', $request->id_number)->get();
        }else{
            $event = Event::find($params['id']);
        }

        return response()->json([
            'status' => true,
            'event' => $event
        ]);
    }

    public function update(Request $request, $id)
    {
        $params = $request->all();
        $params['id'] = $id;
        $validator = Validator::make($params, [
            // 'id' => 'exists:events,id',
            'title' => 'string|required',
            'event_start' => 'date|required',
            'event_end' => 'date|required',
            'details' => 'string|required',
            'recipients' => 'array|required'
        ]);

        if($validator->fails())
        {   
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // return response()->json($request->all());
        $event = Event::find($params['id'])->update($request->all());
        EventIndividual::where('event_id', $request->id)->delete();

        foreach($request->recipients as $recipient)
        {
            if($recipient != 0){
                $scholar_history_id = ScholarHistory::where('scholar_id', $recipient)
                ->where('academic_year', $request->academic_year)
                ->latest()->first();
            
                EventIndividual::create([
                    'event_id' => $request->id,
                    'scholar_history_id' => $scholar_history_id->id,
                    'scholar_id' => $recipient
                ]);
    
                $scholar = Scholar::find($recipient);
                SMSHelper::send($scholar->phone_number, 'New Event has been posted! ' . $request->details);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Event has been updated'
        ]);
    }

    public function delete(Request $request)
    {
        $params['id'] = $request->id;
        $validator = Validator::make($params, [
            'id' => 'exists:events,id'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ]);
        }

        $event = Event::find($params['id']);
        EventIndividual::where('event_id', $event->id)->delete();
        $event->delete();

        return response()->json([
            'status' => false,
            'message' => 'Event has been deleted!'
        ]);
    }
}
