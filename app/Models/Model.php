<?php
/**
 * Created by PhpStorm.
 * User: chenliang
 * Date: 2018/11/13
 * Time: 下午5:19
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    //数据库连接
    public $connection = 'mysql';

    //禁止注入的字段
    public  $guarded = [];



}