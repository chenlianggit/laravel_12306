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
    #微信常规
    Route::any('interface1/Member/WxLogin', 'WxController@WxLogin');
    #握手
    Route::any('publicmina/Handler/RedPacketHandler.ashx', 'WxController@RedPacketHandler');
    #收集formid
    Route::any('formid', 'WxController@postFormid');
    #登陆
    Route::any('12306/login', 'LoginController@login12306');
    #新增常用联系人
    Route::any('list/friends', 'LoginController@friends');
    #创建抢票信息
    Route::any('train/order/create', 'TrainController@create');
    #抢票订单详情
    Route::any('train/order/detail', 'TrainController@detail');
    #取消抢票
    Route::any('train/order/cancel', 'TrainController@cancel');
    #微信支付
    Route::any('train/order/minapay', 'TrainController@minapay');
});