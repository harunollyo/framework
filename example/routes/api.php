<?php

use Example\App\Http\Controllers\EventsController;
use Example\App\Http\Controllers\SpeakersController;
use Framework\Http\Request;
use Framework\Route;
use Framework\Supports\Facades\DB;
use Framework\Supports\Facades\Option;

use function Framework\app;
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
    DB::enable_query_log();
    // $option1 = Option::get(['widget_custom_html', 'sidebars_widgets'], null, false);
    // $option2 = Option::get(['widget_custom_html'], null, false);
    $option3 = Option::get('sidebars_widgets', null, false);
    $query_log = DB::get_query_log();

    return response()->json([
        // 'options' => $option1,
        // 'options2' => $option2,
        'options3' => $option3,
        'query' => $query_log,
    ]);
});