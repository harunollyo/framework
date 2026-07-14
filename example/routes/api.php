<?php

use Example\App\Events\SampleEvent;
use Example\App\Http\Controllers\BlogController;
use Example\App\Http\Controllers\EventsController;
use Example\App\Http\Controllers\SpeakersController;
use Example\App\Http\Requests\ExampleRequest;
use Example\App\Models\Blog;
use Example\App\Models\Event;
use Example\App\Resources\TestResource;
use Framework\Http\Request;
use Framework\Middlewares\AdminMiddleware;
use Framework\Middlewares\AuthMiddleware;
use Framework\Resource;
use Framework\Route;
use Framework\Supports\Arr;
use Framework\Supports\Facades\Http;
use Framework\Validation\Rule;
use Framework\Validation\Validator;

use function Framework\dd;
use function Framework\response;

Route::set_namespace('framework/v1');

Route::get('/ping', function (Request $request) {
    $blog = Blog::with(['comments' => fn ($query) => $query->select('id', 'blog_id', 'body')])->find(1);

    return response()->json([
        'data' => $blog,
    ]);
});

Route::get('/events/{event}', [EventsController::class, 'index'])->middleware([AdminMiddleware::class, AuthMiddleware::class]);
Route::post('/events', [EventsController::class, 'create']);

Route::get('/speakers', [SpeakersController::class, 'index']);
Route::get('/speakers/{speaker}', [SpeakersController::class, 'show']);
Route::post('/speakers', [SpeakersController::class, 'create']);
Route::put('/speakers/{speaker}', [SpeakersController::class, 'update']);

Route::post('/blogs/{blog}', [BlogController::class, 'update']);

Route::get('/options', function (Request $request) {
    $events = Event::query()->get();

    return response()->json([
        'events' => Arr::last($events),
        'req' => $request->all(),
    ]);
});

Route::get('/check', function (ExampleRequest $request) {
    return response()->json([
        'request' => $request->string('name'),
    ]);
});

Route::post('/check', function (Request $request) {
    $data = [
        'name' => 'John Doe',
        // 'email' => 'john.doe@example',
    ];
    $validator = Validator::make($data, [
        'name' => [
            'required',
            'string',
        ],
        'email' => [
            'email',
        ],
    ]);

    $validator->sometimes('email', 'required|email', function ($data) {
        return $data['name'] === 'John Doe';
    });

    return response()->json([
        'validator' => $validator->validated(),
    ]);
});