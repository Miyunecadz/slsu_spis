<?php

namespace App\Http\Controllers;

use App\Helpers\SMSHelper;
use App\Models\Event;
use App\Models\EventIndividual;
use App\Models\Scholar;
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
            })
        ]);

        // $validator = Validator::make($request->all(), [
        //     'title' => 'string',
        //     'event_start' => 'date',
        //     'event_end' => 'date',
        //     'details' => 'string',
        //     'id_number' => Rule::exists('scholars', 'id_number')
        // ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }
        
        // $events = Event::join('event_individuals', 'event_individuals.scholar_id')
        // // ->whereDate('event_start', '>=', Carbon::yesterday()->toDateString())
        //     ->when($request->has('id'), function ($query) use ($request) {
        //         $query->join('event_individuals', 'event_individuals.event_id', 'events.id')
        //             ->join('scholars', 'event_individuals.scholar_id', 'scholars.id')
        //             ->where('id_number', $request->id_number);
        //     })
        //     ->get();
        if($request->has('id'))
        {
            $events = EventIndividual::where('scholar_id', $request->id)->with('event')->get();
        }else
        {
            $events = Event::with('eventIndividual')->get();
        }
        
       
        // $events = Event::
        //     when($request->has('title'), function ($query) use ($request) {
        //         $query->where('title', 'LIKE', "%$request->title%");
        //     })
        //     ->when($request->has('event_start'), function ($query) use ($request) {
        //         $query->where('event_start', $request->event_start);
        //     })
        //     ->when($request->has('event_end'), function ($query) use ($request) {
        //         $query->where('event_end', $request->event_end);
        //     })
        //     ->when($request->has('details'), function ($query) use ($request) {
        //         $query->where('details', $request->details);
        //     })
        //     ->when($request->has('id_number'), function ($query) use ($request) {
        //         $query->join('event_individuals', 'event_individuals.event_id', 'events.id')
        //             ->join('scholars', 'event_individuals.scholar_id', 'scholars.id')
        //             ->where('id_number', $request->id_number);
        //     })
        //     ->paginate(10);

        return response()->json($events); 
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'event_start' => 'required|date',
            'event_end' => 'required|date',
            'details' => 'required',
            'recipients' => 'required|array'
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
            EventIndividual::create([
                'event_id' => $event->id,
                'scholar_id' => $recipient
            ]);

            $scholar = Scholar::find($recipient);
            SMSHelper::send($scholar->phone_number, 'New Event has been posted! For more information check the event details.');
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

    public function update(Request $request)
    {
        $params = $request->all();
        $params['id'] = $request->id;
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

        $event = Event::find($params['id'])->update($request->all());
        EventIndividual::where('event_id', $request->id)->delete();

        foreach($request->recipients as $recipient)
        {
            EventIndividual::create([
                'event_id' => $request->id,
                'scholar_id' => $recipient
            ]);

            $scholar = Scholar::find($recipient);
            SMSHelper::send($scholar->phone_number, 'New Event has been posted! For more information check the event details.');
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
