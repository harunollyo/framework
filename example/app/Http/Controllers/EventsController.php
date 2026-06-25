<?php

namespace Example\App\Http\Controllers;

use Example\App\Models\Blog;
use Example\App\Models\BlogSeo;
use Example\App\Models\Category;
use Example\App\Models\Event;
use Example\App\Models\Speaker;
use Example\App\Models\Tag;
use Framework\Http\Request;
use Framework\Supports\Facades\DB;

use function Framework\request;
use function Framework\response;

class EventsController
{
	public function index(Request $request)
	{
		DB::enable_query_log();
		$tags = Blog::query()->with('comments.replies')->get();
		$queries = DB::get_query_log();

		return response()->json([
			'request' => $request->all(),
			'blog' => $tags,
			'queries' => $queries,
		]);
	}

	public function create(Request $request)
	{
		$event = Event::create([
			'name' => 'Tech Conference 2026',
			'description' => 'A premier event for technology leaders and enthusiasts.',
			'date' => '2026-05-10',
			'time' => '09:30:00',
			'location' => 'Convention Center Hall A',
			'organizer' => 'TechEvents Co.',
		]);

		$speaker_one = Speaker::create([
			'name' => 'John Doe',
			'designation' => 'Keynote Speaker',
			'email' => 'john.doe@example.com',
			'website' => 'https://example.com/john',
		]);

		$speaker_two = Speaker::create([
			'name' => 'Jane Smith',
			'designation' => 'Panelist',
			'email' => 'jane.smith@example.com',
			'website' => 'https://example.com/jane',
		]);

		$event->speakers()->attach([
			$speaker_one->id,
			$speaker_two->id,
		]);

		$event->load('speakers');

		return response()->json([
			'message' => 'Events upserted successfully.',
			'event' => $event,
		]);
	}

	public function options(Request $request)
	{
		$r = request();

		return response()->json([
			'events' => $r->all(),
			'req' => $request->all(),
		]);
	}
}
