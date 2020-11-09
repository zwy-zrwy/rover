<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/20
 * Time: 16:48
 */

namespace app\user\controller;
class Order extends Base
{
    public function index()
    {
        $uid = session('user_id');
        $data = db('order_info')->where('uid',$uid)->paginate()->each(function($item, $key){
                $item['goods'] = getOrderGoods($item['id']);
            return $item;
        });
//        echo '<pre>';
//        print_r($data);die;
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function info()
    {
        $post = input();
        $data  = orderInfo($post['id']);
        if( $data['uid'] !== session('user_id'))
        {
            $this->error('订单不存在');
        }
//        echo '<pre>';
//        print_r($data);die;
        $this->assign('data',$data);
        return $this->fetch();
    }

}