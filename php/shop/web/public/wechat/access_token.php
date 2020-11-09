<?php
//    $app_id = 'wxfb3351a32346d7d4';
//    $appSecret = 'af90069c185325bd9b49168c0f066550';
//    $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$app_id.'&secret='.$appSecret;
//    $data = curl_get($access_token_url);
//    echo $data;
//    die('髣髴兮若轻云之蔽月，飘飖兮若流风之回雪');
    $access_token = '27_xJD_r5woc-kCmhRDx6h8DGUey4iuzVyJqxr1B1RogZUfndCuLu1SHdyCArP34TIocGJqKprNnCmAGiiZprA-UD-_OJ9WOV7c83VZNTiLzJHqTrLy5nxzyHW32OtYO9wsep3LdSoukkRYKRY6ENXgAFAVEO';
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