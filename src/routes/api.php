<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->post('/room/create', 'RoomController@store');

Route::middleware('auth:api')->post('/sugoroku/start', 'SugorokuController@startGame');
Route::middleware('auth:api')->post('/sugoroku/save_log', 'SugorokuController@saveLog');
Route::middleware('auth:api')->post('/sugoroku/delete', 'SugorokuController@deleteRoom');

Route::middleware('auth:api')->get('/sugoroku/position/{user_id}/{room_id}', 'SugorokuController@getKomaPosition');
Route::middleware('auth:api')->get('/sugoroku/members/{room_id}', 'SugorokuController@getMembers');
Route::middleware('auth:api')->get('/sugoroku/last_go/{room_id}', 'SugorokuController@getLastGo');
