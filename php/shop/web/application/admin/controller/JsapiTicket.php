<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/29
 * Time: 12:14
 */
namespace app\admin\controller;
class JsapiTicket
{
    public function index()
    {
//                获取jsapi_ticket
        $access_token = getAccessToken();
        $jsapi_ticket_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
        $json = curl_get($jsapi_ticket_url);
        $data = json_decode($json,1);
        $jsapi_ticket = $data['ticket'];
        db('wxconfig')->update(['jsapi_ticket'=>$jsapi_ticket,'id'=>1]);
    }
}