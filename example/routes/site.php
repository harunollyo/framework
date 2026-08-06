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
        
        return view('sample', ['name' => 'ping', 'data' => [
            'name' => 'ping',
            'age' => 20,
            'email' => 'ping@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St, Anytown, USA',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip' => '12345',
            'country' => 'USA',
        ]]);
    })->name('ping');

    Route::get('products', function (Request $request) {
        return view('sample', ['name' => 'products'])->partial();
    })->match_page()->name('products');
});