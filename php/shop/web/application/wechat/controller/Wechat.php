<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/27
 * Time: 14:00
 */

namespace app\wechat\controller;
use think\Controller;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Voice;
use EasyWeChat\Kernel\Messages\Video;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use think\facade\Env;
class Wechat extends Controller
{
    public function index()
    {
        $wxconfig = db('wxconfig')->find(1);
        $config = [
            'app_id' => $wxconfig['appid'],
            'secret' => $wxconfig['appsecret'],
            'token' => $wxconfig['token'],
            'response_type' => 'array',
            //...
        ];

        $app = Factory::officialAccount($config);
        $app->server->push(function ($message) {
            $res = db('reply')->where('keys',$message['Content'])->find();
            if($res)
            {
                if($res['type'] ==1)
                {
                    return $res['content'];
                }
                elseif($res['type'] ==2)
                {
                    $arr = db('goods')->find($res['content']);
                    $pic = getWebUrl().'/uploads/'.str_replace('\\','/',$arr['pic']);
                    $items = [
                        new NewsItem([
                            'title'       => $arr['name'],
                            'description' => $arr['content'],
                            'url'         => getWebUrl().'/index/goods/index/id/'.$res['content'],
                            'image'       => $pic,
                            // ...
                        ]),
                    ];
                    $news = new News($items);
                    return $news;
                }
                elseif ($res['type'] ==3)
                {
                    $mediaId = db('material')->where('id',$res['content'])->value('media_id');
                    $voice = new Voice($mediaId);
                    return $voice;
                }
                elseif ($res['type'] ==4)
                {
                    $mediaId = db('material')->where('id',$res['content'])->value('media_id');
                    $video = new Video($mediaId, [
                        'title' => '测试',
                        'description' => '测试视频哈哈哈',
                    ]);
                    return $video;
                }
            }
            else
            {
                $res = ask($message['Content']);
                $reply = $res->result->content;
                return $reply;
            }
        });
        $response = $app->server->serve();


        // 将响应输出
        $response->send();exit; // Laravel 里请使用：return $response;
    }
}