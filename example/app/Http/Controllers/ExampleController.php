<?php

namespace Example\App\Http\Controllers;

use Example\App\Http\Requests\ExampleRequest;
use Example\App\Models\Event;
use Framework\Http\Request;
use Example\App\Models\Post;
use Framework\Supports\Facades\DB;
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
		Guard::authorize('view', Event::class);
		$events = Event::query()->get();
		$base_url = app()->base_url();

		$option = Option::get(['option_1', 'option_2']);
		
		return response()->json([
			'data' => $option,
			'base_url' => $base_url,
			'events' => $events
		]);
	}

	public function create(Request $request)
	{
		$timestamp = date('Y-m-d H:i:s');

		$events = [
			[
				'id' => 1,
				'name' => 'Annual Tech Summit',
				'description' => 'Updated keynote and workshop schedule.',
				'date' => '2026-09-15',
				'time' => '09:00:00',
				'location' => 'San Francisco',
				'organizer' => 'Ollyo Events',
				'updated_at' => $timestamp,
			],
			[
				'id' => 2,
				'name' => 'Product Launch',
				'description' => 'Launch day agenda and speaker lineup.',
				'date' => '2026-10-01',
				'time' => '14:30:00',
				'location' => 'New York',
				'organizer' => 'Marketing Team',
				'updated_at' => $timestamp,
			],
			[
				'id' => 0,
				'name' => 'Community Meetup',
				'description' => 'Monthly community gathering for contributors.',
				'date' => '2026-11-20',
				'time' => '18:00:00',
				'location' => 'Austin',
				'organizer' => 'Community Team',
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			],
		];

		DB::enable_query_log();
		$affected = Event::upsert(
			$events,
			['name', 'description', 'date', 'time', 'location', 'organizer', 'updated_at']
		);

		$log = DB::get_query_log();

		return response()->json([
			'message' => 'Events upserted successfully.',
			'affected' => $affected,
			'log' => $log,
		]);
	}
}
