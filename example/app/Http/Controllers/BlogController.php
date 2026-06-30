<?php

namespace Example\App\Http\Controllers;

use Example\App\Models\Blog;
use Framework\Http\Request;
use Framework\Supports\Facades\Guard;

use function Framework\response;

class BlogController
{
	public function update(Request $request, Blog $blog)
	{
		Guard::authorize('update', $blog, $request);

		return response()->json([
			'message' => 'Blog updated successfully.',
			'blog' => $blog,
		]);
	}
}