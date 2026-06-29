<?php

use Example\App\Http\Controllers\EventsController;
use Example\App\Http\Controllers\SpeakersController;
use Example\App\Http\Requests\ExampleRequest;
use Example\App\Models\Event;
use Framework\Http\Request;
use Framework\Middlewares\AdminMiddleware;
use Framework\Middlewares\AuthMiddleware;
use Framework\Route;
use Framework\Supports\Arr;
use Framework\Supports\Facades\DB;
use Framework\Supports\Facades\File;
use Framework\Supports\Facades\Option;

use function Framework\app;
use function Framework\collection;
use function Framework\config;
use function Framework\dd;
use function Framework\dump;
use function Framework\request;
use function Framework\response;

Route::set_namespace('framework/v1');

Route::get('/ping', function (Request $request) {
    return response()->json([
        'status'   => 'ok',
        'dev_mode' => app()->is_dev_mode(),
        'prefix'   => app()->prefix(),
    ]);
});

Route::get('/events', [EventsController::class, 'index'])->middleware([AdminMiddleware::class, AuthMiddleware::class]);
Route::post('/events', [EventsController::class, 'create']);

Route::get('/speakers', [SpeakersController::class, 'index']);
Route::get('/speakers/{speaker}', [SpeakersController::class, 'show']);
Route::post('/speakers', [SpeakersController::class, 'create']);
Route::put('/speakers/{speaker}', [SpeakersController::class, 'update']);

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
    return response()->json([
        'request' => $request->all(),
    ]);
});