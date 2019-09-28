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

Route::get('poll/{id}', 'PollController@show');
Route::post('poll', 'PollController@store');
Route::post('poll/{id}/vote', 'PollController@vote');
Route::get('poll/{id}/stats', 'PollController@stats');
