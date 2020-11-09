<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/2
 * Time: 16:08
 */
namespace app\miniprogram\controller;
class Flow
{
    public function addToCart()
    {
        $goods_id = input('id');
        $goods_num = input('num');
        $uid = encryptDecrypt('zhouweiyao', input('token'),1);
        $data = db('goods')->where('id', $goods_id)->find();
        if ($data) {
            $orders = [
                'goods_id' => $goods_id,
                'goods_name' => $data['name'],
                'goods_num' => $goods_num,
                'goods_price' => $data['price'],
                'user_id' => $uid
            ];
            $res = db('cart')->insert($orders);
            if ($res) {
                $arr = ['code' => 0, 'msg' => '成功加入购物车！'];
            } else {
                $arr = ['code' => 1, 'msg' => '系统开小差啦...'];
            }
        } else {
            $arr = ['code' => 1, 'msg' => '抱歉，商品售罄...'];
        }
        return json($arr);
    }

    public function cart()
    {
        $uid = encryptDecrypt('zhouweiyao', input('token'),1);
        $data = CartGoods($uid);
        $arr = ['goods'=>$data];
        return json($arr);
    }

    public function update()
    {
        $post = input();
        $uid = encryptDecrypt('zhouweiyao', $post['token'],1);
        db('cart')->update(['id'=>$post['id'],'goods_num'=>$post['num']]);
        $data = CartGoods($uid);
        $arr = ['goods'=>$data];
        return json($arr);
    }

    public function del()
    {
        $post = input();
        $uid = encryptDecrypt('zhouweiyao', $post['token'],1);
        db('cart')->delete($post['id']);
        $data = CartGoods($uid);
        $arr = ['goods'=>$data];
        return json($arr);
    }
}