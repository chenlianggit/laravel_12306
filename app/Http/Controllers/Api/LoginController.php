<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/12/25
 * Time: 下午3:28
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;


class LoginController
{
    public function login12306(Request $request){
        $username   = get('accountNo');
        $pwd        = get('accountPwd');
        $phone      = get('mobileNo');
        $openid     = get('openid');
        if(!$username || !$pwd || !$openid){
            outputToJson(ERROR,'error');
        }
        outputToJson(OK,'success');
    }
}