<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/30
 * Time: 9:21
 */
namespace app\index\controller;
use Endroid\QrCode\QrCode;
class Test
{
    public function index()
    {
        $goods_id = input('id');
        //二维码内容
        $content = getWebUrl().'/mobile/goods/index/id/'.$goods_id;
        $qrCode = new QrCode($content);
        $str = $qrCode->writeString();
        //二维码的网络地址
        $pic = 'uploads/goods_'.$goods_id.'.jpg';
        file_put_contents($pic,$str);
    }
}