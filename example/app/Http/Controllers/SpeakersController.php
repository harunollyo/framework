<?php

namespace Example\App\Http\Controllers;

use Example\App\Models\Speaker;
use Framework\Http\Request;
use Framework\Supports\Arr;

use function Framework\response;

class SpeakersController
{
	public function index(Request $request)
	{
		$speakers = Speaker::query()->with('events')->get();

		return response()->json([
			'speakers' => $speakers,
		]);
	}

	public function show(Request $request, Speaker $speaker)
	{
		$speaker->load('events');

		return response()->json([
			'speaker' => $speaker,
		]);
	}

	public function create(Request $request)
	{
		$speaker = Speaker::create(Arr::only($request->all(), [
			'name',
			'designation',
			'email',
			'website',
		]));

		return response()->json([
			'message' => 'Speaker created successfully.',
			'speaker' => $speaker,
		]);
	}

	public function update(Request $request, Speaker $speaker)
	{
		$speaker->update(Arr::only($request->all(), [
			'name',
			'designation',
			'email',
			'website',
		]));

		return response()->json([
			'message' => 'Speaker updated successfully.',
			'speaker' => $speaker,
		]);
	}
}
