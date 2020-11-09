<?php
// file_put_contents('hah.txt',$_GET['ehcostr']);
if(empty($_GET['echostr']))
{

    $post = file_get_contents("php://input");
    $postObj = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);
    $fromUsername = $postObj->FromUserName;
    $toUsername = $postObj->ToUserName;
    $MsgType = $postObj->MsgType;
    $Event = $postObj->Event;
    $EventKey = $postObj->EventKey;
    $keyword = trim($postObj->Content);
    $time = time();
    if($MsgType == 'text')
    {
        $res = ask($keyword);
        $result = $res->result->content;
        echo '<xml>
				  <ToUserName><![CDATA['.$fromUsername.']]></ToUserName>
				  <FromUserName><![CDATA['.$toUsername.']]></FromUserName>
				  <CreateTime>'.$time.'</CreateTime>
				  <MsgType><![CDATA[text]]></MsgType>
				  <Content><![CDATA['.$result.']]></Content>
				</xml>';
    }
    elseif( $MsgType == 'event' )
    {
        if($Event == 'subscribe')
        {
            echo  '<xml>
					<ToUserName><!['.$fromUsername.']]></ToUserName>
					<FromUserName><!['.$toUsername.']]></FromUserName>
					<CreateTime>'.$time.'</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>4</ArticleCount>
					<Articles>
						<item>
							<Title><![CDATA[测试首页]]></Title>
                			<Description><![CDATA[淘宝首页]]></Description>
                            <PicUrl><![CDATA[http://zhouweiyao.xarlit.cn/static/images/good_shop2.png]]></PicUrl>
                            <Url><![CDATA[http://shop.zwy.xarlit.cn/]]></Url>
                        </item>
                        <item>
                            <Title><![CDATA[测试商品]]></Title>
                            <Description><![CDATA[创维电视]]></Description>
                            <PicUrl><![CDATA[http://zhouweiyao.xarlit.cn/uploads/20191114/e4969102a20f85c572fe4e12601743d7.jpg]]></PicUrl>
                            <Url><![CDATA[http://shop.zwy.xarlit.cn/index/goods/index/id/11.html]]></Url>
                        </item>
                        <item>
                            <Title><![CDATA[测试商品2]]></Title>
                            <Description><![CDATA[乐视电视]]></Description>
                            <PicUrl><![CDATA[http://zhouweiyao.xarlit.cn/uploads/20191114/ecada16a4d42ae1cce1b2c192c5011e5.jpg]]></PicUrl>
                            <Url><![CDATA[http://shop.zwy.xarlit.cn/index/goods/index/id/12.html]]></Url>
                        </item>
                        <item>
                            <Title><![CDATA[测试商品3]]></Title>
                            <Description><![CDATA[百事可乐]]></Description>
                            <PicUrl><![CDATA[http://zhouweiyao.xarlit.cn/uploads/20191117/e34e805900dc2cb204c34ac3a98c2f5e.jpg]]></PicUrl>
                            <Url><![CDATA[http://shop.zwy.xarlit.cn/index/goods/index/id/25.html]]></Url>
                        </item>
					</Articles>
				</xml>';
        }
    }

}
else
{
    $timestamp = $_GET['timestamp'];
    $nonce = $_GET['nonce'];
    $token	= 'zhoudayao';
    $arr = [$timestamp,$nonce,$token];
    sort($arr,SORT_STRING);
    $str = implode($arr);
    $str = sha1($str);
    $signature = $_GET['signature'];
    if($str == $signature)
    {
        echo $_GET['echostr'];
    }
    // file_put_contents('a.txt',$str);
    // file_put_contents('b.txt',$signature);
}

function ask($key)
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

?>