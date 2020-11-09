<?php
namespace app\admin\controller;
class AccessToken {
    public function index()
    {
        $data = db('wxconfig')->find(1);
        $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$data['appid'].'&secret='.$data['appsecret'];
        $json = curl_get($access_token_url);
        $arr = json_decode($json);
        $access_token = $arr->access_token;
        db('wxconfig')->update(['access_token'=>$access_token,'id'=>1]);
    }
}