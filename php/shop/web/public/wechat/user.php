<?php
    $appid = 'wxfb3351a32346d7d4';
    $secret = 'af90069c185325bd9b49168c0f066550';
    if(empty($_GET['code']))
    {
        $scope = 'snsapi_userinfo';
        $redirect_url = urlencode('http://zhouweiyao.xarlit.cn/wechat/user.php');
        $code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope='.$scope.'&state=40#wechat_redirect';
        header('location:'.$code_url);
    }
    else
    {
        $access_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$_GET['code'].'&grant_type=authorization_code';
        $json = curl_get($access_token_url);
        $json = json_decode($json);
        $access_token = $json->access_token;
        $openid = $json->openid;
        $user_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $user = json_decode(curl_get($user_url),1);
        echo '<pre>';
        print_r($user);

    }

    function curl_get($url)
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
?>