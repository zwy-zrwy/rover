<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/25
 * Time: 16:44
 */
    $access_token = '27_xJD_r5woc-kCmhRDx6h8DGUey4iuzVyJqxr1B1RogZUfndCuLu1SHdyCArP34TIocGJqKprNnCmAGiiZprA-UD-_OJ9WOV7c83VZNTiLzJHqTrLy5nxzyHW32OtYO9wsep3LdSoukkRYKRY6ENXgAFAVEO';
    $menu_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;

    $data = [
        'button'=>[
            ['name'=>'点击菜单','sub_button'=>[
                ['type'=>'click','name'=>'多图文','key'=>'news'],
                ['type'=>'click','name'=>'语音','key'=>'voice'],
            ]],
            ['name'=>'链接','sub_button'=>[
                ['type'=>'view','name'=>'百度','url'=>'https://m.baidu.com'],
                ['type'=>'view','name'=>'淘宝','url'=>'https://m.taobao.com'],
            ]],
            ['type'=>'view','name'=>'用户中心','url'=>'http://zhouweiyao.xarlit.cn/user/login']
        ]
    ];
    $menu = json_encode($data,JSON_UNESCAPED_UNICODE);
    $res = curl_post($menu_url,$menu);
    echo $res;
    function curl_post($url,$post_data)
    {
        //初始化
        $curl = curl_init();

        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }
?>