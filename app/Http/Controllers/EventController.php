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
        $from = now()->startOfDay();
        if ($request->has('from')) {
            $from = Carbon::parse($request->get('from'))->startOfDay();
        }

        $to = now()->endOfDay();
        if ($request->has('to')) {
            $to = Carbon::parse($request->get('to'))->endOfDay();
        }

        $events = Event::query()
            ->whereRaw('? between valid_from and valid_to', [$from->format('Y-m-d H:i:s')])
            ->orWhereRaw('? between valid_from and valid_to', [$to->format('Y-m-d H:i:s')])
            ->orWhere(function($query) use ($from, $to){
                if (!$from->isSameDay($to)){
                    $query->where('valid_to', '<=', $to->format('Y-m-d H:i:s'));
                }

                $query->where('valid_from', '>=', $from->format('Y-m-d H:i:s'));
            })->get();

        return EventResource::collection($events);
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

        if ($event->comments()->count()) {
            return response()->json([
                'error'   => true,
                'message' => 'Can not delete event when it has comments.',
            ]);
        }

        $event->delete();

        return response()->json([], 204);
    }
}
