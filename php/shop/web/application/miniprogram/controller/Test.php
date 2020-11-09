<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/5
 * Time: 18:43
 */

namespace app\miniprogram\controller;


class Test
{
    public function index()
    {
        $str = '10_10_10';
        $data = explode('_',$str);
        foreach($data as $key=>$val)
        {
            $post['code'] = $data[$key];
            $post['encryptedData'] = $data[$key];
            $post['iv'] = $data[$key];
        }

        //加密:"z0JAx4qMwcF+db5TNbp/xwdUM84snRsXvvpXuaCa4Bk="
        echo encryptDecrypt('zhouweiyao', '10',0);

        echo '<br/>';

        //解密:"Helloweba欢迎您"
        echo encryptDecrypt('zhouweiyao', '3fHzyokrMnFk7QjBHMyRxJry6ykkhstsrmv+Di+6UCE=',1);
    }
}