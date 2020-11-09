<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/21
 * Time: 12:20
 */

namespace app\index\controller;
use payment\Alipay;
class Notify
{
    public function alipay()
    {
        $post = input();
//        $str = serialize($post);
//        file_put_contents('a.txt',$str);
        $order = db('order_info')->where('order_sn',$post['out_trade_no'])->find();
        if($post['out_trade_no'] == $order['order_sn'] && $post['seller_id'] == config('alipay.partner'))
        {
            db('order_info')->where('order_sn',$_POST['out_trade_no'])->update(['pay_status'=>1]);
        }
    }
}