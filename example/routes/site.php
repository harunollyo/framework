<?php

use Framework\Http\Request;
use Framework\Middlewares\AuthMiddleware;
use Framework\Route;
use Framework\Wordpress\Constants\HookNames;

use function Framework\back;
use function Framework\dd;
use function Framework\redirect;
use function Framework\response;
use function Framework\session;
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

    Route::post('/cart/add', function (Request $request) {
        $id = $request->int('product_id');
        $quantity = max(1, $request->int('quantity', 1));

        // Dot notation writes into a nested array.
        $current = session("cart.items.{$id}.quantity", 0);
        session()->put("cart.items.{$id}", [
            'product_id' => $id,
            'quantity' => $current + $quantity,
        ]);

        return redirect(Route::site_url('products'))->with('notice', 'Added to your cart.');
    })->template_redirect()->name('cart.add');
});