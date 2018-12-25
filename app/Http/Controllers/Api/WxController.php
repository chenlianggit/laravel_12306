<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/12/14
 * Time: 下午5:15
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Wechart\WxAppController;
use App\Models\Wx3rdSession;
use App\Models\WxAccessToken;
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
        #维护3rd_session

        $session3rd = md5($openid.$sessionKey.time().rand(10000,99999));
        Wx3rdSession::updateOrCreate(
            ['openid' => $openid],
            [
                'openid'        => $openid,
                'session_key'   => $sessionKey,
                '3rd_session'   => $session3rd
            ]
        );

        $data = [];
        $data['sessionCode']    = $session3rd;
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

    /**
     * 获取一个有效formid
     * @param $uid
     * @param int $type
     * @return bool
     */
    private static function _getFormid($openid)
    {
        if (!$openid) {
            return false;
        }
        $where['openid'] = $openid;

        # 创建时间大于等于 6天23小时前时间
        $where = [];
        $where[] = ['openid','=', $openid];
        $where[] = ['create_time','>=',time() - (60 * 60 * 24 * 7 - 3600)];
        $where[] = ['del','=',0];

        $res = WxFormId::where($where)->orderBy('create_time', 'asc')->first();
        if (!$res) {
            return false;
        }
        $res->del = 1;
        $res->save();
        return $res->formid;
    }

    # 获取最新的access_token
    public static function _getNewAccessToken()
    {
        $res    = WxAccessToken::find(1);
        # 检查是否过期 2个小时
        $bool   = (int)( (time() - (int)$res->update_time) > 6500);
        if (!$res || $bool) {
            $res->access_token  = self::_getAccessToken();
            $res->update_time   = (int)time();
            $res->save();
        }
        return $res->access_token;
    }

    # 从微信获取access_token
    public static function _getAccessToken()
    {
        $weixin = array2object(config('local.weixin'));
        $url    = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $weixin->appid . '&secret=' . $weixin->secret;
        $html   = file_get_contents($url);
        $output = json_decode($html, true);
        return $output['access_token'];
    }


    # 认证提醒消息推送
    public static function authRemind($uid, $openid = '')
    {
        $templateid = array2object(C('template_id'));
        if ($uid) {
            $res = M('user')->field('openid,uname,type')->where(['id' => $uid])->find();
            if (!$res) {
                return false;
            }
            $openid = $res['openid'];
        }
        if ($openid) {
            $res = M('user')->field('id,uname,type')->where(['openid' => $openid])->find();
        }
        $openid = trim($openid);
        $data_arr = array(
            # 这里根据你的模板对应的关键字建立数组，color 属性是可选项目，用来改变对应字段的颜色
            'keyword1' => array("value" => $res['uname']),
            'keyword2' => array("value" => "您已提交认证资料，请前往个人中心去认证吧~"),
            'keyword3' => array("value" => date('Y/m/d H:i')),
        );

        $formid = self::getFormid($openid);
        $page = 'pages/user/user';
        $bool = self::postWX($openid, $formid, $templateid->auth_remind, $data_arr, $page);
        if (!$bool) {
            return false;
        }
        return true;
    }

    private static function postWX($openid, $formid, $templateid, $data_arr, $page = 'pages/user/user')
    {
        if (!$openid || !$formid || !$templateid || !$data_arr) {
            return false;
        }
        $post_data = array(
            "touser" => $openid,              //用户的 openID，可用过 wx.getUserInfo 获取
            "template_id" => $templateid,          //小程序后台申请到的模板编号
            "page" => $page, //点击模板消息后跳转到的页面，可以传递参数
            "form_id" => $formid,              //第一步里获取到的 formID
            "data" => $data_arr,
            "emphasis_keyword" => ""                    //需要强调的关键字，会加大居中显示
        );
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . self::getNewAccessToken();

        $return = CommonController::send_post($url, $post_data);
        $res = json_decode($return, true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return true;
    }

}