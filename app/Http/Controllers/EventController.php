<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query();

        if ($request->has('from') and $request->has('to')) {
            $from = Carbon::parse($request->get('from'))->startOfDay()->format('Y-m-d H:i:s');
            $to   = Carbon::parse($request->get('to'))->endOfDay()->format('Y-m-d H:i:s');

            $events->whereRaw("valid_from BETWEEN ? and ?", [$from, $to])
                ->orWhereRaw("valid_to BETWEEN ? and ?", [$from, $to]);
        } else {
            $events->where('valid_from', '<=', now()->format('Y-m-d H:i:s'))
                ->where('valid_to', '>=', now()->format('Y-m-d H:i:s'));
        }

        return EventResource::collection($events->get());
    }

    public function store(EventRequest $request)
    {
        Event::create([
            'title'      => $request->get('title'),
            'content'    => $request->get('content'),
            'valid_from' => $request->get('valid_from'),
            'valid_to'   => $request->get('valid_to'),
            'gps_lat'    => $request->get('gps_lat'),
            'gps_lng'    => $request->get('gps_lng'),
            'user_id'    => Auth::user()->id,
        ]);

        return response()->json([], 201);
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update([
            'title'      => $request->get('title'),
            'content'    => $request->get('content'),
            'valid_from' => $request->get('valid_from'),
            'valid_to'   => $request->get('valid_to'),
            'gps_lat'    => $request->get('gps_lat'),
            'gps_lng'    => $request->get('gps_lng'),
        ]);

        return response()->json([], 204);
    }

    public function destroy(Event $event)
    {
        $this->authorize('destroy', $event);

        if ($event->comments()->count()){
            return response()->json([
                'error' => true,
                'message' => 'Can not delete event when it has comments.'
            ]);
        }

        $event->delete();

        return response()->json([], 204);
    }
}
