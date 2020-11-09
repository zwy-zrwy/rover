<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/20
 * Time: 15:02
 */
namespace app\user\controller;
use think\Controller;
class Base extends Controller
{
    public function initialize()
    {
        if(empty(session('user_id')))
        {
            $this->error('请宁先登录后再操作呢','login/index');
        }
    }
}