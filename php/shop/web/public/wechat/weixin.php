<?php

$appID = 'wxfb3351a32346d7d4';
$appsecret = '92f067f0e1641c7af314aeb289fcbb26';
$echostr = $_GET['echostr'];


$weixin = new Weixin();
if (isset($echostr)) {
	$weixin->valid();
}else{
	$weixin->responseMsg();
}


class Weixin {
	public function valid()
    {
        $echoStr = $_GET['echostr'];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $token = 'zhoudayao';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = file_get_contents("php://input");
	
        if (!empty($postStr)){
            //把接收到的xml数据转换成对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $MsgType = $postObj->MsgType;
			$Event = $postObj->Event;
			$EventKey = $postObj->EventKey;
            $keyword = trim($postObj->Content);
            $time = time();

            $image = 'zcPJ9kmSQFdb9GEBGVA8cHF2LK8MBBPRQPLlovTpRR7Eu7ZTe6OpnkdrooP79qSw';
            $voice = 'zzpm68MPk-H8CtzlVWSWXZtVVLa1plR7f-PATk_w4Kzv3i9B126vC-KWRyHxZOdV';
            $video = 'T_DyDsPXxpCUemHAt6MyWX-P5BWeDJ4LVzV3yzNdHk-Mnfd1rVcU2sY88TWPxzY3';

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
                        </xml>
                        ";

            $newsTpls = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[news]]></MsgType>
                        <ArticleCount>%s</ArticleCount>
                        <Articles>%s</Articles>
                        </xml>";


            if($MsgType == 'text')
            {
                if(!empty($keyword))
                {
                    if($keyword == '图片')
                    {
                        $resultStr = sprintf($imageTpl, $fromUsername, $toUsername, $time,$image);
                        echo $resultStr;
                    }
                    elseif($keyword == '语音')
                    {
                        $resultStr = sprintf($voiceTpl, $fromUsername, $toUsername, $time,$voice);
                        echo $resultStr;
                    }
                    elseif($keyword == '音乐')
                    {
                        $resultStr = sprintf($musicTpl, $fromUsername, $toUsername, $time,'哈哈哈的音乐','哈哈哈嘿嘿嘿嘿嘿哈哈哈哈哈哈哈哈哈哈或或或或或或或或或或或或','https://m.baidu.com',$image,$voice);
                        echo $resultStr;
                    }
                    elseif($keyword == '视频')
                    {
                        $resultStr = sprintf($videoTpl, $fromUsername, $toUsername, $time,$video,'哈哈哈的视频','哈哈哈嘿嘿嘿嘿嘿哈哈哈哈哈哈哈哈哈哈或或或或或或或或或或或或');
                        echo $resultStr;
                    }
                    else{
                        $res = $this->ask($keyword);
                        $contentStr = $res->result->content;
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time,$contentStr);
                        echo $resultStr;
                    }
                }
            }
            elseif($MsgType == 'event')
            {
                if($Event == 'subscribe')
                {
                    $str = '';
                    $arr = [
                        ['title'=>'测试首页','desc'=>'淘宝首页','pic'=>'http://zhouweiyao.xarlit.cn/static/images/good_shop2.png','url'=>'http://shop.zwy.xarlit.cn/'],
                        ['title'=>'测试商品','desc'=>'创维电视','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191114/e4969102a20f85c572fe4e12601743d7.jpg','url'=>'http://shop.zwy.xarlit.cn/index/goods/index/id/11.html'],
                        ['title'=>'测试商品2','desc'=>'乐视电视','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191114/ecada16a4d42ae1cce1b2c192c5011e5.jpg','url'=>'http://shop.zwy.xarlit.cn/index/goods/index/id/12.html'],
                        ['title'=>'测试商品3','desc'=>'百事可乐','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191117/e34e805900dc2cb204c34ac3a98c2f5e.jpg','url'=>'http://shop.zwy.xarlit.cn/index/goods/index/id/25.html']
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
                elseif ($Event == 'CLICK')
                {
                    if($EventKey == 'news')
                    {
                        $str = '';
                        $arr = [
                            ['title'=>'测试首页','desc'=>'淘宝首页','pic'=>'http://zhouweiyao.xarlit.cn/static/images/good_shop2.png','url'=>'http://shop.zwy.xarlit.cn/'],
                            ['title'=>'测试商品','desc'=>'创维电视','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191114/e4969102a20f85c572fe4e12601743d7.jpg','url'=>'http://shop.zwy.xarlit.cn/index/goods/index/id/11.html'],
                            ['title'=>'测试商品2','desc'=>'乐视电视','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191114/ecada16a4d42ae1cce1b2c192c5011e5.jpg','url'=>'http://shop.zwy.xarlit.cn/index/goods/index/id/12.html'],
                            ['title'=>'测试商品3','desc'=>'百事可乐','pic'=>'http://zhouweiyao.xarlit.cn/uploads/20191117/e34e805900dc2cb204c34ac3a98c2f5e.jpg','url'=>'http://shop.zwy.xarlit.cn/index/goods/index/id/25.html']
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
                    elseif($keyword == '视频')
                    {
                        $resultStr = sprintf($videoTpl, $fromUsername, $toUsername, $time,$video,'哈哈哈的视频','哈哈哈嘿嘿嘿嘿嘿哈哈哈哈哈哈哈哈哈哈或或或或或或或或或或或或');
                        echo $resultStr;
                    }
                }
            }
        }
        else
        {
            echo "";
            exit;
        }


    }
	
	public function curl_get($url)
	{
		//初始化
		$curl = curl_init();
		//设置抓取的url
		curl_setopt($curl, CURLOPT_URL, $url);
		//设置头文件的信息作为数据流输出
		curl_setopt($curl, CURLOPT_HEADER, 0);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		//执行命令
		$data = curl_exec($curl);
		//关闭URL请求
		curl_close($curl);
		//显示获得的数据
		return $data;
	}

    public function ask($key)
    {
        $host = "https://jisuiqa.market.alicloudapi.com";
        $path = "/iqa/query";
        $method = "GET";
        $appcode = "7e81a3b0fc0c4c80b17c39083f12f089";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "question=".$key;
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $res =  curl_exec($curl);
        return json_decode($res);
    }
}

?>