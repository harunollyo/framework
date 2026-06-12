<?php

use Example\App\Http\Controllers\EventsController;
use Example\App\Http\Controllers\SpeakersController;
use Example\App\Models\Event;
use Framework\Http\Request;
use Framework\Route;
use Framework\Supports\Arr;
use Framework\Supports\Facades\DB;
use Framework\Supports\Facades\Option;

use function Framework\app;
use function Framework\collection;
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

Route::get('/events', [EventsController::class, 'index']);
Route::post('/events', [EventsController::class, 'create']);

Route::get('/speakers', [SpeakersController::class, 'index']);
Route::get('/speakers/{speaker}', [SpeakersController::class, 'show']);
Route::post('/speakers', [SpeakersController::class, 'create']);
Route::put('/speakers/{speaker}', [SpeakersController::class, 'update']);

Route::get('/options', function (Request $request) {
    $r = request('hello');

    return response()->json([
        'events' => $r,
        'req' => $request->all(),
    ]);
});