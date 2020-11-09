<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/4
 * Time: 12:16
 */
namespace app\miniprogram\controller;
use miniprogram\ExBizDataCrypt;
class Login
{
    public function index()
    {
        $data = input('data');
        $data = explode('_',$data);
        $post = ['code','encryptedData','iv'];
        $post = array_combine($post,$data);
        $appid = 'wx6562826c68d5440b';
        $secret = 'cac1ddab51a8fdec42c86564986fb76a';
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$post['code'].'&grant_type=authorization_code';
        $auth = curl_get($url);
        $auth = json_decode($auth,1);
        $xopenid = $auth['openid'];
        $uid = db('user')->where('xopenid',$xopenid)->value('id');
        if($uid){
            $uid = encryptDecrypt('zhouweiyao', $uid,0);
            return $uid;
        }
        else
        {
            $sessionKey = $auth['session_key'];
            $encryptedData = $post['encryptedData'];
            $iv = $post['iv'];
            $pc = new ExBizDataCrypt($appid, $sessionKey);
            $errCode = $pc->decryptData($encryptedData, $iv,$data);
            if ($errCode == 0)
            {
                $data = json_decode($data,1);
                $insert = [
                    'province'=>$data['province'],
                    'xopenid'=>$data['openId'],
                    'nickname'=>$data['nickName'],
                    'city'=>$data['city'],
                    'headimgurl'=>$data['avatarUrl'],
                    'sex'=>$data['gender']
                ];
                $uid = db('user')->insertGetId($insert);
                $uid = encryptDecrypt('zhouweiyao', $uid,0);
                return $uid;
            }
            else
            {
                return $errCode;
            }
        }
    }

    public function bindMobile()
    {
        $data = input('data');
        $data = explode('_',$data);
        $post = ['code','encryptedData','iv'];
        $post = array_combine($post,$data);
        $appid = 'wx6562826c68d5440b';
        $secret = 'cac1ddab51a8fdec42c86564986fb76a';

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$post['code'].'&grant_type=authorization_code';
        $auth = curl_get($url);
        $auth = json_decode($auth,1);
//        $xopenid = $auth['openid'];
//        db('user')->where('xopenid',$xopenid)->update([]);
//        $uid = db('user')->where('xopenid',$xopenid)->value('id');
        $sessionKey = $auth['session_key'];
        $encryptedData = $post['encryptedData'];
        $iv = $post['iv'];
        $pc = new ExBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv,$data);
        if ($errCode == 0)
        {
            return $data;
        }
        else
        {
            return $errCode;
        }
    }
}