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