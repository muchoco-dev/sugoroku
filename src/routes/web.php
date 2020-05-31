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

Auth::routes();

Route::middleware('auth')->get('/', 'RoomController@index')->name('room.index');

Route::middleware('auth')->get('/home', 'HomeController@index')->name('home');
Route::middleware('auth')->get('/rooms', 'RoomController@index')->name('rooms');
Route::middleware('auth')->get('/room/{uname}', 'RoomController@show')->name('room.show');;
Route::middleware('auth')->get('/room/{uname}/join', 'RoomController@join');
Route::middleware('auth')->put('/user/{user}/update', 'UserController@update')->name('user.update');

Route::get('/privacy', function() {
  return view('privacy');
});
Route::get('/terms', function() {
  return view('terms');
});
Route::get('/developers', function() {
  return view('developers');
});
