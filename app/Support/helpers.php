<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/11/21
 * Time: 下午2:39
 */

const OK    = 200;
const ERROR = 0;

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



function WxOutPut($data){
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
    $msg['response']['body'] = array_merge($msg['response']['body'],$data);

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