<?php

use Example\App\Http\Controllers\ExampleController;
use Framework\Http\Request;
use Framework\Route;

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

Route::get('/example', [ExampleController::class, 'index']);