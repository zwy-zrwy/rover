<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/28
 * Time: 9:22
 */
namespace app\mobile\controller;
class Goods extends Base
{
    public function index()
    {
        $id = input('id');
        $goods = db('goods')->find($id);
        $pic = getWebUrl().'/uploads/'.str_replace('\\','/',$goods['pic']);
        $this->assign('goods',$goods);
        $this->assign('pic',$pic);
        $url = getWebUrl().request()->url();
        $this->assign('url',$url);
        $jsapi_ticket = db('wxconfig')->where('id',1)->value('jsapi_ticket');
        $timestamp = time();
        $noncestr = time();
        $str = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $signature = sha1($str);
        $wxconfig = db('wxconfig')->find();
        $config = [
            'appId'=>$wxconfig['appid'],
            'timestamp'=>$timestamp,
            'nonceStr'=>$noncestr,
            'signature'=>$signature,
        ];
        $this->assign('config',$config);
        return $this->fetch();
    }
}