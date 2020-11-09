<?php
namespace app\mobile\controller;
class Index extends Base
{
    public function index()
    {
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

    public function getAddress()
    {
        $locations = input('location');
        $address = GouldAddress($locations);
        $res = ['code'=>0,'msg'=>'ok','data'=>$address];
        return json($res);
    }
}
