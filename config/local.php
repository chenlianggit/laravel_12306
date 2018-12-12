<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/11/21
 * Time: 下午2:32
 */


return [

    'oss'       => [
        'accessKeyId'       => env('OSS_accessKeyId'),
        'accessKeySecret'   => env('OSS_accessKeySecret'),
        'endpoint'          => env('OSS_endpoint'),
        'bucket'            => env('OSS_bucket'),
        'url'               => env('OSS_url'),
    ],
    'weixin'    => [
        'appid'             => env('WEIXIN_appid'),                //小程序appid
        'secret'            => env('WEIXIN_secret'),  //小程序secret
    ],
];