<?php

namespace Example\App\Http\Controllers;

use Example\App\Http\Requests\ExampleRequest;
use Example\App\Models\Event;
use Framework\Http\Request;
use Example\App\Models\Post;
use Framework\Supports\Facades\Guard;
use Framework\Supports\Facades\Option;

use function Framework\app;
use function Framework\collection;
use function Framework\response;

class ExampleController
{
	public function __construct()
	{
		//
	}

	public function index(Request $request)
	{
		// Guard::authorize('view', Event::class);
		// $event = Event::query()->get();
		$base_url = app()->base_url();

		$option = Option::get('option_3', ['option_4' => 'option 4 default', 'option_3' => 'option 3 default']);
		
		return response()->json([
			'data' => $option
		]);
	}

	public function create(Request $request)
	{
		$event = Event::query()->update_or_create([
			'name' => 'Test Event 2',
		], [
			'description' => 'Test Description',
			'date' => '2026-01-01',
			'time' => '10:00:45',
			'location' => 'Test Location',
			'organizer' => 'Test Organizer',
		]);

		return response()->json([
			'message' => 'Hello World',
			'data' => $event
		]);
	}
}
