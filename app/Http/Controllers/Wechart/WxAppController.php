<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/12/14
 * Time: 下午5:03
 */

namespace App\Http\Controllers\Wechart;


class WxAppController extends WxBaseController
{
    /**
     * 开发者服务器使用登录凭证 code 获取 session_key 和 openid
     * @param string $code 登录时获取的 code
     * @return array
     */
    public function jsCode2Session($code) {
        $api_url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->_app_id . '&secret=' . $this->_app_secret . '&js_code=' . $code . '&grant_type=authorization_code';
        $s = self::get($api_url);
        return json_decode($s, true);
    }
    /**
     * 生成小程序二维码
     * @param string $path 对应页面
     * @param int $width 二维码宽度，默认为430
     * @return mixed 当请求失败时，返回<b>FALSE</b>，成功时返回<b>array</b>。当array中<b>errcode</b>为<b>0</b>时，<b>data</b>中即为二维码的二进制内容。
     */
    public function createWxaQrcode($path, $width = 430) {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=' . $this->getAccessToken();
        $post = array(
            'path' => $path,
            'width' => $width
        );
        $s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
        if ($s === false) {
            return false;
        }
        $r = json_decode($s, true);
        if (!is_array($r)) {
            $r = array('errcode' => 0, 'data' => $s);
        }
        return $r;
    }
    /**
     * 生成小程序码，有数量限制
     * @param string $path 对应页面
     * @param int $width 小程序码宽度，默认为430
     * @param bool $is_hyaline 是否需要透明底色， is_hyaline 为true时，生成透明底色的小程序码
     * @return mixed 当请求失败时，返回<b>FALSE</b>，成功时返回<b>array</b>。当array中<b>errcode</b>为<b>0</b>时，<b>data</b>中即为二维码的二进制内容。
     */
    public function getWxaCode($path, $width = 430, $is_hyaline = false) {
        $api_url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=' . $this->getAccessToken();
        $post = array(
            'path' => $path,
            'width' => $width,
            'is_hyaline' => $is_hyaline
        );
        $s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
        if ($s === false) {
            return false;
        }
        $r = json_decode($s, true);
        if (!is_array($r)) {
            $r = array('errcode' => 0, 'data' => $s);
        }
        return $r;
    }
    /**
     * 生成小程序码，无数量限制
     * @param string $scene 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
     * @param string $page 必须是已经发布的小程序存在的页面（否则报错），例如 "pages/index/index" ,根路径前不要填加'/',不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
     * @param int $width 小程序码宽度，默认为430
     * @param bool $is_hyaline 是否需要透明底色， is_hyaline 为true时，生成透明底色的小程序码
     * @return mixed 当请求失败时，返回<b>FALSE</b>，成功时返回<b>array</b>。当array中<b>errcode</b>为<b>0</b>时，<b>data</b>中即为二维码的二进制内容。
     */
    public function getWxaCodeUnlimit($scene, $page, $width = 430, $is_hyaline = false) {
        $api_url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getAccessToken();
        $post = array(
            'scene' => $scene,
            'page' => $page,
            'width' => $width,
            'is_hyaline' => $is_hyaline
        );
        $s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
        if ($s === false) {
            return false;
        }
        $r = json_decode($s, true);
        if (!is_array($r)) {
            $r = array('errcode' => 0, 'data' => $s);
        }
        return $r;
    }
    /**
     * 检查一段文本是否含有违法违规内容。
     * 频率限制：单个 appId 调用上限为 2000 次/分钟，1,000,000 次/天
     * @param string $content 要检测的文本内容，长度不超过 500KB
     * @return array errcode的合法值	0-内容正常	87014-内容含有违法违规内容
     */
    public function msgSecCheck($content) {
        $api_url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token=' . $this->getAccessToken();
        $post = array('content' => $content);
        $s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
        return json_decode($s, true);
    }
    /**
     * 检查一张图片是否含有违法违规内容。
     * 频率限制：单个 appId 调用上限为 2000 次/分钟，1,000,000 次/天
     * @param Form-Data $image 要检测的图片文件，格式支持PNG、JPEG、JPG、GIF，图片尺寸不超过 750px * 1334px
     * @return array errcode的合法值	0-内容正常	87014-内容含有违法违规内容
     */
    public function imgSecCheck($image) {
        $api_url = 'https://api.weixin.qq.com/wxa/img_sec_check?access_token=' . $this->getAccessToken();
        $post = array('media' => '@' . $image);
        $s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
        return json_decode($s, true);
    }
    /**
     * 小程序发送模板消息
     * @param array $msg 模板消息内容
     * @return array
     */
    public function sendTemplateMessage($msg) {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $this->getAccessToken();
        $s = self::post($api_url, json_encode($msg, JSON_UNESCAPED_UNICODE));
        return json_decode($s, true);
    }
    /**
     * 小程序发送客服消息
     * @param array $msg 客服消息内容
     * @return array
     */
    public function sendCustomMessage($msg) {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $this->getAccessToken();
        $s = self::post($api_url, json_encode($msg, JSON_UNESCAPED_UNICODE));
        return json_decode($s, true);
    }
    /**
     * 客服输入状态
     * @param string $open_id 普通用户(openid)
     * @param string $command Typing：对用户下发"正在输入"状态；CancelTyping：取消对用户的"正在输入"状态
     * @return array
     */
    public function typingCustomMessage($open_id, $command) {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/typing?access_token=' . $this->getAccessToken();
        $post = array('touser' => $open_id, 'command' => $command);
        $s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
        return json_decode($s, true);
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $sessionKey   string 用户sessionkey
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $sessionKey, $encryptedData, $iv, &$data )
    {
        if (strlen($sessionKey) != 24) {
            return self::$IllegalAesKey;
        }
        $aesKey=base64_decode($sessionKey);


        if (strlen($iv) != 24) {
            return self::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return self::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->_app_id )
        {
            return self::$IllegalBuffer;
        }
        $data = $result;
        return self::$OK;
    }
}