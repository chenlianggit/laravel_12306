<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/12/25
 * Time: 下午3:28
 */

namespace App\Http\Controllers\Api;

use App\Models\User12306;
use Illuminate\Http\Request;


class LoginController
{
    public function login12306(){
        $username       = get('accountNo');
        $pwd            = get('accountPwd');
        $phone          = get('mobileNo');
        $sessionCode    = get('sessionCode');

        if(!$username || !$pwd || !$sessionCode){
            outputToJson(ERROR,'error');
        }
        $openid = WxController::getOpenidBy3rdSession($sessionCode);

        $res = User12306::where(['username' => $username,'openid'=>$openid])->first();
        if(!$res){
            $res = new User12306();
        }
        $res->openid = $openid;
        $res->username = $username;
        $res->pwd = $pwd;
        $res->phone = $phone;
        $res->save();
        
        outputToJson(OK,'success');
    }
}