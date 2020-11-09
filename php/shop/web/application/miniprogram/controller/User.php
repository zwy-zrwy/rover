<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/6
 * Time: 11:36
 */
namespace app\miniprogram\controller;
class User
{
    public function index()
    {
        $post = input();
        $uid = encryptDecrypt('zhouweiyao',input('token'),1);
        $info = db('user')->find($uid);
        $arr = ['info'=>$info];
        return json($arr);
    }

    public function address()
    {
        $uid = encryptDecrypt('zhouweiyao',input('token'),1);
        $address = db('user_address')->where('uid',$uid)->select();
        $arr = ['address'=>$address];
        return json($arr);
    }
}