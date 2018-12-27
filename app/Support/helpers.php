<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/11/21
 * Time: 下午2:39
 */

const OK    = 200;
const ERROR = 0;

const WXERROR = 4001;# 错误

function array2object($arr)
{
    $json = json_encode($arr);
    return json_decode($json, false);
}


/**
 * 输出json数据
 * @param int $code
 * @param string $message
 * @param string $data
 * @param string $noCache
 */
function outputToJson($code, $message, $data = [])
{
    header("Content-type: application/json; charset=utf-8");
    $msg = array(
        'code' => $code,
        'msg' => $message,
        'data' => $data,
    );

    $msg = json_encode($msg);

    echo $msg;
    exit();
}

/**
 * 获取请求数据
 **/
function get($key, $type = "string", $value = "@place@")
{
    // $data = $this->_request->getRequest($key);
    $data = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
    return _judgeValue($data, $type, $value);
}

function _judgeValue($data, $type = "string", $value = "@place@")
{
    $result = "";
    if ($type == "int") {
        if ($value == "@place@") {
            $value = 0;
        }
        $result = $data != "" ? intval($data) : $value;
    } else if ($type == "string") {
        if ($value == "@place@") {
            $value = '';
        }
        $result = $data != "" ? trim($data) : $value;
    } else if ($type == "array") {
        if ($value == "@place@") {
            $value = array();
        }
        $result = $data ? $data : $value;
    } else if ($type == "double") {
        if ($value == "@place@") {
            $value = 0;
        }
        $result = $data ? doubleval($data) : $value;
    }
    return $result;
}



function WxOutPut($data = []){
    header("Content-type: application/json; charset=utf-8");
    $msg['response'] = [
        'header'=>[
            'rspType' => '0',
            'rspCode' => '0',
            'rspDesc' => '执行成功',
            'serverTime' => mtimeDateTime(),
        ],
        'body'  => [
            'isSubscribe'       => '0',
            'isSubscribeTips'   => '0',
            'isSuccess'         => '1',
            'description'       => '执行成功',
            'code'              => '0',
            'rspType'           => '0',
            'mileSeconds'       => '0',
            'verifyReturn'      => false,
        ],
    ];
    if($data){
        $msg['response']['body'] = array_merge($msg['response']['body'],$data);
    }

    $msg = json_encode($msg);

    echo $msg;
    exit();
}

function WxOutPutBody($code=0, $msg='', $data = []){
    header("Content-type: application/json; charset=utf-8");
    $msg = [
        'header'=>[
            'rspType' => '0',
            'rspCode' => $code,
            'rspDesc' => $msg,
            'serverTime' => mtimeDateTime(),
        ],
        'body'  => [
            'isSubscribe'       => '0',
            'isSubscribeTips'   => '0',
            'isSuccess'         => '1',
            'description'       => '执行成功',
            'code'              => '0',
            'rspType'           => '0',
            'mileSeconds'       => '0',
            'verifyReturn'      => false,
        ],
    ];
    if($data){
        $msg['body'] = array_merge($msg['body'],$data);
    }

    $msg = json_encode($msg);

    echo $msg;
    exit();
}

function mtimeDateTime(){
    $mtimestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳

    $timestamp = floor($mtimestamp); // 时间戳
    $milliseconds = round(($mtimestamp - $timestamp) * 1000); // 毫秒

    $datetime = date("Y-m-d H:i:s", $timestamp) . '.' . $milliseconds;
    return $datetime;
}


function send_post( $url, $post_data ) {
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-type:application/json',
            //header 需要设置为 JSON
            'content' => json_encode($post_data),
            'timeout' => 60
            //超时时间
        )
    );
    $context = stream_context_create( $options );
    $result = file_get_contents( $url, false, $context );

    return $result;
}

/**
 * 用来拼接文件名, 网址等路径
 *
 * @return string
 */
function join_paths()
{
    $args  = func_get_args();
    $paths = [];
    foreach ($args as $arg) {
        $paths = array_merge($paths, (array)$arg);
    }

    $paths = array_map(create_function('$p', 'return trim($p, "/");'), $paths);
    $paths = array_filter($paths);
    return join('/', $paths);
}

//自定义函数隐藏中间数字
function ycIdCard($str){
    if(!$str){
        return '';
    }
    $resstr = substr_replace($str,'************',3,12);
    return $resstr;
}