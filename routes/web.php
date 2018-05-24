<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/bilibili_986f22bdf80494fd755c11bc450a6ec0.html', function () {
    return File::get(public_path() . '/bilibili_986f22bdf80494fd755c11bc450a6ec0.html');
});
