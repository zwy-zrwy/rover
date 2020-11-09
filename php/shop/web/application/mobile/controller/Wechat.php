<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/26
 * Time: 11:18
 */
namespace app\mobile\controller;
class Wechat
{
    public function index()
    {
        $this->wxconfig = getWxconfig();
        $echostr = input('echostr');
        if (isset($echostr)) {
            $this->valid();
        } else {
            $this->responseMsg();
        }
    }

    public function valid()
    {
        $echoStr = input('echostr');
        if ($this->checkSignature()) {
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = input('signature');
        $timestamp = input('timestamp');
        $nonce = input('nonce');
        $token = $this->wxconfig['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = file_get_contents("php://input");
        if (!empty($postStr)) {
            //把接收到的xml数据转换成对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $MsgType = $postObj->MsgType;
            $Event = $postObj->Event;
            $EventKey = $postObj->EventKey;
            $keyword = trim($postObj->Content);
            $Latitude = $postObj->Latitude;
            $Longitude = $postObj->Longitude;
            $time = time();

            $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>";
            $imageTpl = "<xml>
                          <ToUserName><![CDATA[%s]]></ToUserName>
                          <FromUserName><![CDATA[%s]]></FromUserName>
                          <CreateTime>%s</CreateTime>
                          <MsgType><![CDATA[image]]></MsgType>
                          <Image>
                            <MediaId><![CDATA[%s]]></MediaId>
                          </Image>
                        </xml>";
            $voiceTpl = "<xml>
                          <ToUserName><![CDATA[%s]]></ToUserName>
                          <FromUserName><![CDATA[%s]]></FromUserName>
                          <CreateTime>%s</CreateTime>
                          <MsgType><![CDATA[voice]]></MsgType>
                          <Voice>
                            <MediaId><![CDATA[%s]]></MediaId>
                          </Voice>
                        </xml>";
            $videoTpl = "<xml>
                          <ToUserName><![CDATA[%s]]></ToUserName>
                          <FromUserName><![CDATA[%s]]></FromUserName>
                          <CreateTime>%s</CreateTime>
                          <MsgType><![CDATA[video]]></MsgType>
                          <Video>
                            <MediaId><![CDATA[%s]]></MediaId>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                          </Video>
                        </xml>";
            $musicTpl = "<xml>
                          <ToUserName><![CDATA[%s]]></ToUserName>
                          <FromUserName><![CDATA[%s]]></FromUserName>
                          <CreateTime>%s</CreateTime>
                          <MsgType><![CDATA[music]]></MsgType>
                          <Music>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            <MusicUrl><![CDATA[%s]]></MusicUrl>
                            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                            <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
                          </Music>
                        </xml>";
            $newsTpl = "<xml>
                          <ToUserName><![CDATA[%s]]></ToUserName>
                          <FromUserName><![CDATA[%s]]></FromUserName>
                          <CreateTime>%s</CreateTime>
                          <MsgType><![CDATA[news]]></MsgType>
                          <ArticleCount>1</ArticleCount>
                          <Articles>
                            <item>
                              <Title><![CDATA[%s]]></Title>
                              <Description><![CDATA[%s]]></Description>
                              <PicUrl><![CDATA[%s]]></PicUrl>
                              <Url><![CDATA[%s ]]></Url>
                            </item>
                          </Articles>
                        </xml>";
            $newsTpls = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[news]]></MsgType>
                        <ArticleCount>%s</ArticleCount>
                        <Articles>%s</Articles>
                        </xml>";

            $image = db('material')->where('type','image')->value('media_id');
            $voice = db('material')->where('type','voice')->value('media_id');
            $video = db('material')->where('type','video')->value('media_id');

            if ($MsgType == 'text') {
                if (!empty($keyword)) {
                    $res = db('reply')->where('keys',$keyword)->find();
                    if ($res) {
                        if($res['type'] == 1 )
                        {
                            $contentStr = $res['content'];
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
                            echo $resultStr;
                        }
                        elseif($res['type'] == 2 )
                        {
                            $info = db('goods')->find($res['content']);
                            $url = getWebUrl().'/mobile/goods/index/id/'.$res['content'];
                            $pic = getWebUrl().'/uploads/'.str_replace('\\','/',$info['pic']);
                            $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time,$info['name'],$info['content'],$pic,$url);
                            echo $resultStr;
                        }
                        elseif ($res['type'] == 3)
                        {
                            $resultStr = sprintf($voiceTpl, $fromUsername, $toUsername, $time,$voice);
                            echo $resultStr;
                        }
                        elseif ($res['type'] ==4)
                        {
                            $resultStr = sprintf($videoTpl, $fromUsername, $toUsername, $time,$video,'哈哈哈的视频','哈哈哈嘿嘿嘿嘿嘿哈哈哈哈哈哈哈哈哈哈或或或或或或或或或或或或');
                            echo $resultStr;
                        }
                    }
                    elseif ($keyword == '测试')
                    {
                        $contentStr = 'http://zhouweiyao.xarlit.cn/mobile/share/index';
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
                        echo $resultStr;
                    }
                    else
                    {
                        $res = ask($keyword);
                        $contentStr = $res->result->content;
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
                        echo $resultStr;
                    }
                }
            }
            elseif($MsgType == 'event')
            {
                if($Event == 'subscribe')
                {
                    $contentStr = db('wxconfig')->where('id',1)->value('msg');
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
                    echo $resultStr;
                }
                elseif ($Event == 'CLICK')
                {
                    if($EventKey == 'news')
                    {
                        $str = '';
                        $arr = [
                            ['title'=>'测试首页','desc'=>'淘宝首页','pic'=>'http://zhouweiyao.xarlit.cn/static/images/good_shop2.png','url'=>'http://zhouweiyao.xarlit.cn/'],
                            ['title'=>'测试商品','desc'=>'创维电视','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191114/e4969102a20f85c572fe4e12601743d7.jpg','url'=>'http://zhouweiyao.xarlit.cn/index/goods/index/id/11.html'],
                            ['title'=>'测试商品2','desc'=>'乐视电视','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191114/ecada16a4d42ae1cce1b2c192c5011e5.jpg','url'=>'http://zhouweiyao.xarlit.cn/index/goods/index/id/12.html'],
                            ['title'=>'测试商品3','desc'=>'百事可乐','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191117/e34e805900dc2cb204c34ac3a98c2f5e.jpg','url'=>'http://zhouweiyao.xarlit.cn/index/goods/index/id/25.html']
                        ];
                        foreach($arr as $key=>$val)
                        {
                            $str .="<item>
                            <Title><![CDATA[".$arr[$key]['title']."]]></Title>
                            <Description><![CDATA[".$arr[$key]['desc']."]]></Description>
                            <PicUrl><![CDATA[".$arr[$key]['pic']."]]></PicUrl>
                            <Url><![CDATA[".$arr[$key]['url']."]]></Url>
                            </item>";
                        }
                        $num = count($arr);
                        $resultStr = sprintf($newsTpls, $fromUsername, $toUsername, $time,$num,$str);
                        echo $resultStr;
                    }
                    elseif($EventKey == 'voice')
                    {
                        $resultStr = sprintf($voiceTpl, $fromUsername, $toUsername, $time,$voice);
                        echo $resultStr;
                    }
                    elseif($EventKey == 'video')
                    {
                        $resultStr = sprintf($videoTpl, $fromUsername, $toUsername, $time,$video,'哈哈哈的视频','哈哈哈嘿嘿嘿嘿嘿哈哈哈哈哈哈哈哈哈哈或或或或或或或或或或或或');
                        echo $resultStr;
                    }
                    elseif ($EventKey == 'address')
                    {
                        $user = db('user')->where('openid',$fromUsername)->find();
                        $locations = $user['Longitude'].','.$user['Latitude'];
                        $contentStr = GouldAddress($locations);
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
                        echo $resultStr;
                    }
                }
                elseif($Event == 'LOCATION')
                {
                    db('user')->where('openid',$fromUsername)->update(['Longitude'=>$Longitude,'Latitude'=>$Latitude]);
                }
            }
        }
    }
}