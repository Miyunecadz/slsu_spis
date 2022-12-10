<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateEvent;
use App\Jobs\UpsertEvent;
use App\Models\Event;
use App\Models\EventIndividual;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'event_start' => 'date',
            'event_end' => 'date',
            'details' => 'string',
            'id_number' => Rule::exists('scholars', 'id_number')
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $events = Event::
            when($request->has('title'), function ($query) use ($request) {
                $query->where('title', 'LIKE', "%$request->title%");
            })
            ->when($request->has('event_start'), function ($query) use ($request) {
                $query->where('event_start', $request->event_start);
            })
            ->when($request->has('event_end'), function ($query) use ($request) {
                $query->where('event_end', $request->event_end);
            })
            ->when($request->has('details'), function ($query) use ($request) {
                $query->where('details', $request->details);
            })
            ->when($request->has('id_number'), function ($query) use ($request) {
                $query->join('event_individuals', 'event_individuals.event_id', 'events.id')
                    ->join('scholars', 'event_individuals.scholar_id', 'scholars.id')
                    ->where('id_number', $request->id_number);
            })
            ->paginate(10);

        return response($events); 
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

        $event = Event::create(Arr::except($request->all), ['recipients']);
        UpsertEvent::dispatch($request->all(), $event->id);

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

        $event = Event::find($params['id']);

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
            'id' => 'exists:events,id',
            'title' => 'string',
            'event_start' => 'date',
            'event_end' => 'date',
            'details' => 'string',
            'recipients' => 'array'
        ]);

        if($validator->fails())
        {   
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $event = Event::find($params['id'])->update($request->all());
        UpdateEvent::dispatch($event->id, $request->recipients);

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
