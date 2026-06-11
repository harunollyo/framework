<?php

namespace Example\App\Http\Controllers;

use Example\App\Models\Event;
use Framework\Http\Request;
use function Framework\response;

class EventsController
{
	public function index(Request $request)
	{
		$events = Event::query()->with('speakers')->get();

		return response()->json([
			'events' => $events
		]);
	}

	public function create(Request $request)
	{
		$event = Event::create([
			'name' => 'Event X',
			'description' => ['name' => 'John Doe', 'email' => 'john.doe@example.com'],
			'date' => '2026-01-01',
			'time' => '10:00:00',
			'location' => 'Location 1',
			'organizer' => 'Organizer 1',
		]);

		return response()->json([
			'message' => 'Events upserted successfully.',
			'event' => $event,
		]);
	}
}
