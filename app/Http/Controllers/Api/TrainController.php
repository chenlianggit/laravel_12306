<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/12/26
 * Time: 下午9:17
 */

namespace App\Http\Controllers\Api;

use App\Jobs\TrainPython;
use App\Models\Train;
use App\Models\User12306;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

class TrainController
{

    /**
     * 创建订单
     * @param Request $request
     */
    public function create(Request $request){
        $arr = $request->json()->all();
        $accountNo      = $arr['accountNo'] ?? '';
        $formId         = $arr['formId'] ?? '';
        $sessionCode    = $arr['sessionCode'] ?? '';
        $phone          = $arr['memberPhone'] ?? '';
        $ticketItem     = $arr['ticketItem'] ?? [];   # 乘车信息
        $passengersList = $arr['passengersList'] ?? [];   # 乘车人
        $seatType       = $arr['seatType'];

        if(!$ticketItem || !$sessionCode || !$passengersList || !$accountNo){
            WxOutPutBody(WXERROR,'缺少购票信息,请重新填写');

        }
        if($ticketItem['trainDate'] < date('Y-m-d')){
            WxOutPutBody(WXERROR,'购票时间接近开车时间,请重新选择');
        }
        $openid     = WxController::getOpenidBy3rdSession($sessionCode,1);
        $User12306  = User12306::where('username',$accountNo)->first();
        if(!($User12306->pwd ?? '')){
            WxOutPutBody(WXERROR,'请重新登陆12306');

        }
        $res = Train::where(['openid'=>$openid,'username'=>$accountNo,'train_no'=>$ticketItem['trainNo'],'train_date'=>$ticketItem['trainDate'],'python_type'=>0])->first();
        if($res){
            WxOutPutBody(WXERROR,'同一时间，同一辆车，不能多次创建');
        }

        #储存formId
        WxController::_saveOneFormid($openid, $formId);

        $seat = config('dict.seat');
        $Obj = new Train();
        $Obj->openid        = $openid;
        $Obj->username      = $accountNo;
        $Obj->pwd           = $User12306->pwd;
        $Obj->phone         = $phone;
        $Obj->seat_type     = $seatType;
        $Obj->seat_name     = $seat[$seatType]['name'];
        $Obj->start_station = $ticketItem['fromStationName'];
        $Obj->to_station    = $ticketItem['toStationName'];
        $Obj->train_date    = $ticketItem['trainDate'];
        $Obj->train_no      = $ticketItem['trainNo'];
        $Obj->FromTime      = $ticketItem['FromTime'];
        $Obj->passengers    = json_encode($passengersList,JSON_UNESCAPED_UNICODE);
        $Obj->save();
        $id = $Obj->id;
        Queue::push(new TrainPython($id));
        $data = [
            "memberId"      => time().rand(10000,99999),
            "orderId"       => $id,
            "orderSerialId" => $id,
            "payExpireDate" => date('Y-m-d H:i:s',time()+3600),
            "serverTime"    => date('Y-m-d H:i:s'),
            "totalAmount"   => "0",
            "purchaseModel" => "1"
        ];
        WxOutPutBody(0,'',$data);
    }


    /**
     * 订单详情
     * @param Request $request
     */
    public function detail(Request $request){
        $sessionCode    = $request->json('sessionCode','');
        $serialId       = $request->json('serialId','');
        if(!$sessionCode || !$serialId){
            WxOutPutBody(WXERROR,'未查到该订单');
        }

        $openid     = WxController::getOpenidBy3rdSession($sessionCode,1);
        $train = Train::find($serialId);
        if(!$train || $train->openid != $openid){
            WxOutPutBody(WXERROR,'未查到该订单!');
        }
        $passengerList = json_decode($train->passengers, true);
        foreach ($passengerList as &$v){
            $v['idCard'] = ycIdCard($v['idCard']);
            $v['ticketStateName'] = '未出票';
            $v['packageName'] = '快速出票';
            $v['packagePrice'] = '2.0';
        }
        $seat = config('dict.seat');

        $data = [
            "cancelReason"      => "一天只能取消3次订单,取消订单超过3次会影响出票速度。",
            "fromDate"          => $train->train_date,
            "wxFromDate"        => "1月15日 周六",
            "fromPassType"      => 1,
            "fromStationCode"   => "from",
            "fromStationName"   => $train->start_station,
            "fromTime"          => $train->FromTime,
            "insuranceAmount"   => 4.0,
            "memberId"          => "13907982",
            "occupySeatState"   => 0,
            "orderState"        => 2,
            "orderStateName"    => "排队中",
            "orderType"         => 7,
            "outTicketFailMsg"  => "正在为您抢座,请耐心等待……",
            "passengerList"     => $passengerList,
            "payExpireDate"     => "1900-01-01 00:20:00.000",
            "purchaseModel"     => 1,
            "seatName"          => $train->seat_name,
            "seatType"          => $train->seat_type,
            "serialId"          => $train->id,
            "serverTime"        => date('Y-m-d H:s:i'),
            "showButtons"       => [
                "ifCanCancel"   => "0",
                "ifCanPay"      => "0",
                "ifContinueBook"=> "0",
                "ifRefresh"     => "0",
                "ifBookReturn"  => "0",
                "ifBookAgain"   => "0",
                "ifContinueGrab"=> "0",
                "ifGrabProcess" => "0",
                "ifCanCancelGrab"=>"0"
            ],
            "ticketCount"       => count($passengerList).".0",
            "ticketModel"       => "1",
            "ticketNo"          => "",
            "ticketPrice"       => "43.5",
            "toDate"            => $train->train_date,
            "wxToDate"          => "1月15日 周六",
            "toTime"            => "06:55",
            "toPassType"        => "1",
            "toStationCode"     => "to",
            "toStationName"     => $train->to_station,
            "totalAmount"       => "89.0",
            "trainNo"           => $train->train_no,
            "isNight"           => "0",
            "createTime"        => $train->created_at,
            "isBuyOneyuanFree"  => "0",
            "oneyuanFreeCount"  => "0",
            "couponAmount"      => "0",
            "moId"              => $openid,
            "orderId"           => $train->id,
            "encryptedOrderId"  => $train->id
        ];

        WxOutPutBody(0,'',$data);
    }
}