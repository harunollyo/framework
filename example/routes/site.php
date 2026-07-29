<?php

use Framework\Http\Request;
use Framework\Middlewares\AuthMiddleware;
use Framework\Route;
use Framework\Wordpress\Constants\HookNames;

use function Framework\dd;
use function Framework\response;
use function Framework\view;

Route::set_site_namespace('example');

Route::site(function () {
    Route::get('hello/{name}', function (Request $request, string $name) {
        return view('sample', ['name' => Route::site_url('hello', ['name' => $name])]);
    })->where('name', 'slug')->name('hello');

    Route::get('ping', function (Request $request) {
        return view('sample', ['name' => 'ping']);
    })->name('ping');

    Route::get('products', function (Request $request) {
        return view('sample', ['name' => 'products']);
    })->match_page()->name('products');
});