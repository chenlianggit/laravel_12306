<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/12/14
 * Time: 下午5:15
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Wechart\WxAppController;
use App\Models\WxFormId;
use Illuminate\Http\Request;

class WxController
{
    /**
     * 手机号+微信解密
     */
    public function wxMobile() {
        $sessionkey     = get('session_key');
        $iv             = get('iv');
        $encryptedData  = get('encryptedData');



        # 解密手机号
        $WxAppObj   = new WxAppController();
        $errCode    = $WxAppObj->decryptData($sessionkey, $encryptedData, $iv, $data );

        if ($errCode != 0) {
            outputToJson(ERROR,'解密失败');
        }
        $data       = json_decode($data,true);
        outputToJson(OK,'解密成功', $data);
    }

    /**
     * 获取opened和session_key
     */
    public function WxLogin(Request $req){
        $code = $req->json('code');

        if(!$code){
            outputToJson(ERROR, '预授权错误');
        }
        $WxAppObj   = new WxAppController();
        $res       = $WxAppObj->jsCode2Session($code);

        $openid     = $res['openid'] ?? '';
        $sessionKey = $res['session_key'] ?? '';
        if(!$openid){
            outputToJson(ERROR, '登陆失败',$res);
        }
        $data['sessionCode']    = $sessionKey;
        $data['openid']         = $openid;
        $data['isBind']         = 1;
        $data['mobileNo']       = '13833333333';
        $data['uid']            = rand(10000,99999);
        WxOutPut($data);
    }

    /**
     * 验证账号登陆状态
     */
    public function RedPacketHandler(Request $req){
        $sessionCode = $req->json('body')['sessionCode'] ?? '';

        $memberCouponList = $this->_memberCouponList();
        WxOutPut($memberCouponList);
    }

    /**
     * 构造火车票红包
     * @return mixed
     */
    private function _memberCouponList(){
        $memberCouponList[0] = [
            'redPackageCode'    => '309336V33353BH93Y3J6',
            'name'              => '火车票红包',
            'couponAmount'      => '2',
            'limitAmountDesp'   => '票单价满15元可用',
            'limitDesp'         => '',
            'limitDateDes'      => '',
            'overdueDate'       => '2019-12-27 20:28:49.000',
            'activeLabel'       => '',
            'isConfirmRule'     => '1',
            'projectType'       => '7',
            'onlyApp'           => '0',
        ];

        $memberCouponList[1] = $memberCouponList[0];
        $memberCouponList[2] = $memberCouponList[1];
        $memberCouponList[1]['redPackageCode'] = '1208873V2T3TTTWVHT43';
        $memberCouponList[2]['redPackageCode'] = '949411VTTT44OD94YTXF';
        return $data=['memberCouponList' => $memberCouponList];
    }



    /**
     * 上传Formid
     */
    public function postFormid()
    {
        $uid    = get('uid', 'int', 0);
        $openid = get('openid', 'string', '');
        $formid = get('formid', 'string', '');


        if (!$openid || !$formid) {
            outputToJson(ERROR, '请上传完整参数');
        }
        $formid = trim($formid);
        if (preg_match("/\s/", $formid)) {
            outputToJson(ERROR, '请上传有效的formid');
        }

        $res = WxFormId::where('formid', $formid)->first();
        if ($res) {
            outputToJson(ERROR, 'formid请勿重复提交');
        }
        $formidObj = new WxFormId();
        $formidObj->uid = $uid;
        $formidObj->openid = $openid;
        $formidObj->formid = $formid;
        $formidObj->del = 0;
        $formidObj->create_time = time();
        $formidObj->save();
        outputToJson(OK, 'success');
    }


}