<?php

namespace Example\App\Http\Controllers;

use Example\App\Http\Requests\ExampleRequest;
use Example\App\Models\Event;
use Framework\Http\Request;
use Example\App\Models\Post;
use Framework\Supports\Facades\Guard;

use function Framework\response;

class ExampleController
{
	public function __construct()
	{
		//
	}

	public function index(Request $request)
	{
		Guard::authorize('view', Event::class);

		$event = Event::query()->get();

		return response()->json([
			'message' => 'Hello World',
			'data' => $event
		]);
	}

	public function create(Request $request)
	{
		$event = Event::query()->first_or_create([
			'name' => 'Test Event',
		], [
			'description' => 'Test Description',
			'date' => '2026-01-01',
			'time' => '10:00:00',
			'location' => 'Test Location',
			'organizer' => 'Test Organizer',
		]);

		return response()->json([
			'message' => 'Hello World',
			'data' => $event
		]);
	}
}
