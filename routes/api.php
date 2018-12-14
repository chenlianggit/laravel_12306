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

Route::namespace('Api')->group(function(){
    Route::any('interface1/Member/WxLogin', 'WxController@WxLogin');
    Route::any('publicmina/Handler/RedPacketHandler.ashx', 'WxController@RedPacketHandler');
});