<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/12/26
 * Time: 下午9:17
 */

namespace App\Http\Controllers\Api;

use App\Models\Train;
use App\Models\User12306;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TrainController
{

    public function create(Request $request){
        $arr = $request->json()->all();
        $accountNo      = $arr['accountNo'] ?? '';
        $formId         = $arr['formId'] ?? '';
        $sessionCode    = $arr['sessionCode'] ?? '';
        $phone          = $arr['memberPhone'] ?? '';
        $ticketItem     = $arr['ticketItem'] ?? [];   # 乘车信息
        $passengersList = $arr['passengersList'] ?? [];   # 乘车人

        if(!$ticketItem || !$sessionCode || !$passengersList || !$accountNo){
            outputToJson(ERROR,'error');
        }
        if($ticketItem['trainDate'] < date('Y-m-d')){
            outputToJson(ERROR,'购票时间接近开车时间,请重新选择');
        }
        $openid     = WxController::getOpenidBy3rdSession($sessionCode);
        $User12306  = User12306::where('username',$accountNo)->first();
        if(!($User12306->pwd ?? '')){
            outputToJson(ERROR,'请重新登陆12306');
        }
        $res = Train::where(['openid'=>$openid,'username'=>$accountNo,'train_no'=>$ticketItem['trainNo'],'train_date'=>$ticketItem['trainDate'],'python_type'=>0])->first();
        if($res){
            outputToJson(ERROR,'同一时间，同一辆车，不能多次创建');
        }

        #储存formId
//        WxController::_saveOneFormid($openid, $formId);

        $Obj = new Train();
        $Obj->openid        = $openid;
        $Obj->username      = $accountNo;
        $Obj->pwd           = $User12306->pwd;
        $Obj->phone         = $phone;
        $Obj->start_station = $ticketItem['fromStationName'];
        $Obj->to_station    = $ticketItem['toStationName'];
        $Obj->train_date    = $ticketItem['trainDate'];
        $Obj->train_no      = $ticketItem['trainNo'];
        $Obj->passengers    = json_encode($passengersList,JSON_UNESCAPED_UNICODE);
        $Obj->save();
        $id = $Obj->id;
        Artisan::call('train:create-python',[ '--id' => $id ]);
        outputToJson(OK,'success',$id);

    }
}