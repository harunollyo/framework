<?php

use Themeum\Framework\Http\Request;
use Themeum\Framework\Route;

use function Themeum\Framework\app;
use function Themeum\Framework\response;

Route::set_namespace('framework/v1');

Route::get('/ping', function (Request $request) {
    return response()->json([
        'status'   => 'ok',
        'dev_mode' => app()->is_dev_mode(),
        'prefix'   => app()->prefix(),
    ]);
});
