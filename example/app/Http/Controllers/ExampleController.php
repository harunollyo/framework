<?php

namespace Example\App\Http\Controllers;

use Example\App\Http\Requests\ExampleRequest;
use Framework\Http\Request;

use function Framework\response;

class ExampleController
{
	public function __construct()
	{
		//
	}

	public function index(ExampleRequest $request)
	{
		return response()->json([
			'message' => 'Hello World',
			'data' => $request->all(),
			'sanitized' => $request->sanitized(),
			'validated' => $request->validated(),
		]);
	}
}
