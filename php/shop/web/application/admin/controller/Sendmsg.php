<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/29
 * Time: 18:16
 */
namespace app\admin\controller;
class Sendmsg extends Base
{
    public function index()
    {
        $data = db('sendmsg')->paginate(10)->each(function($item, $key){
            $item['news'] = getTittle($item['ids']);
            return $item;
        });
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function send()
    {
        $id = input('id');
        $info = db('sendmsg')->find($id);
        $news = db('goods')->find($info['ids']);
//        $openids = db('user')->column('openid');
        $openids = getFansOpenids();
        $access_token = getAccessToken();
        $send_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
        $pic = getWebUrl().'/uploads/'.str_replace('\\', '/', $news['pic']);
        $url = getWebUrl().'/mobile/goods/index/id/'.$news['id'];
        $arr = [
            'title'=>$news['name'],
            'desc'=>$news['content'],
            'pic'=>$pic,
            'url'=>$url
        ];
        foreach($openids as $val)
        {
            $arr['openid'] = $val;
            $aa = sendMsg($send_url,$arr);
            print_r($aa);
        }
        $data = ['status'=>1,'id'=>$id];
        db('sendmsg')->update($data);
    }
}