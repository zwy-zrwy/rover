<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/15
 * Time: 11:51
 */

namespace app\index\controller;
use think\Controller;
use payment\Alipay;
class Flow extends Controller
{
    public function addToCart()
    {
        if (!session('user_id')) {
            $arr = ['code' => 1, 'msg' => '请登录后再操作'];
            return json($arr);
        }
        $goods_id = input('id');
        $goods_num = input('num');
        $goods_sku = input('sku');
        $uid = session('user_id');
        $data = db('goods')->where('id', $goods_id)->find();
        if ($data) {
            $orders = [
                'goods_id' => $goods_id,
                'goods_name' => $data['name'],
                'goods_num' => $goods_num,
                'goods_price' => $data['price'],
                'goods_sku' => $goods_sku,
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

    public function addToCart2()
    {
        if (!session('user_id')) {
            $arr = ['code' => 1, 'msg' => '请登录后再进行操作...'];
            return json($arr);
        }
        $data = db('goods')->where('status', 1)->where('id', input('id'))->find();
        if ($data) {
            $orders = [
                'goods_id' => input('id'),
                'goods_name' => $data['name'],
                'goods_num' => 1,
                'goods_price' => $data['price'],
                'user_id' => session('user_id')
            ];
            $res = db('cart')->insert($orders);
            if ($res) {
                $arr = ['code' => 0, 'msg' => '成功加入购物车'];
            } else {
                $arr = ['code' => 1, 'msg' => '系统开小差啦...'];
            }
        } else {
            $arr = ['code' => 1, 'msg' => '抱歉，商品售罄...'];
        }
        return json($arr);
    }

    public function index()
    {
        $user_id = session('user_id');
        $data = db('cart')->alias('c')->field('c.*,sku.price,sku.num,sku.attr_id,s.name')->leftJoin('sku sku', 'c.goods_sku = sku.id')->leftJoin('sttr s', 'sku.attr_id=s.id')->where('c.user_id',$user_id)->order('c.id')->select();
        foreach ($data as $key => $val) {
            $data[$key]['pic'] = getGoodsPic($val['goods_id']);
            if ($data[$key]['attr_id']) {
                $price = db('sku')->where('id', $data[$key]['goods_sku'])->value('price');
                $data[$key]['goods_price'] = $price;
                $data[$key]['sum_price'] = $val['goods_num'] * $val['price'];
            } else {
                $data[$key]['sum_price'] = $val['goods_num'] * $val['goods_price'];
            }
        }
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function update()
    {
        $post = input();
        $good = db('cart')->where('id', input('id'))->find();
        if ($good['goods_sku']) {
            $price = db('sku')->where('id', $good['goods_sku'])->value('price');
        } else {
            $price = $good['goods_price'];
        }
        $num = $post['goods_num'];
        $price_sum = $price * $num;
        $res = db('cart')->update($post);
        if ($res) {
            $arr = ['code' => 0, 'msg' => '修改成功', 'sum' => $price_sum];
        } else {
            $arr = ['code' => 1, 'msg' => '修改失败'];
        }
        return json($arr);
    }

    public function del()
    {
        $id = input('id');
        $res = db('cart')->delete($id);
        if ($res) {
            $arr = ['code' => 0, 'msg' => '移除成功'];
        } else {
            $arr = ['code' => 1, 'msg' => '移除失败'];
        }
        return json($arr);
    }
    public function checkout()
    {
        $uid = session('user_id');
        $post  = input();
        $data = db('cart')->alias('c')->field('c.*,sku.price,sku.num,sku.attr_id,s.name')->leftJoin('sku sku', 'c.goods_sku = sku.id')->leftJoin('sttr s', 'sku.attr_id=s.id')->order('c.id')->where('c.id','in',$post['id'])->select();
        foreach ($data as $key => $val) {
            $data[$key]['pic'] = getGoodsPic($val['goods_id']);
            if ($data[$key]['attr_id']) {
                $price = db('sku')->where('id', $data[$key]['goods_sku'])->value('price');
                $data[$key]['goods_price'] = $price;
                $data[$key]['sum_price'] = $val['goods_num'] * $val['price'];
            } else {
                $data[$key]['sum_price'] = $val['goods_num'] * $val['goods_price'];
            }
            $sum_all[] = $data[$key]['sum_price'];
        }
        $total_price = array_sum($sum_all);
        $address = db('user_address')->where('uid', $uid)->select();
        $pay = db('pay')->where('status', 1)->select();
        $this->assign(['data' => $data, 'total_price' => $total_price, 'address' => $address, 'pay' => $pay]);
        return $this->fetch();
    }

    public function check()
    {
        $ids = input('id');
        if($ids)
        {
            $arr = ['code'=>0,'msg'=>'正在生产订单信息，请稍后','list'=>$ids];
        }
       else
       {
           $arr = ['code'=>1,'msg'=>'请选择要结算的宝贝！'];
       }
        return json($arr);
    }

    public function done()
    {
        $post = input();
        $rule = [
            'address_id|地址'=>'require',
            'pay_id|支付方式'=>'require',
            ];
         $result = $this->validate($post,$rule);
        if($result !== true)
        {
            $this->error($result);
        }
        $uid = session('user_id');
        $address = db('user_address')->find($post['address_id']);
        $pay = db('pay')->find($post['pay_id']);
        $osn = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $order_info = [
            'order_sn' => $osn,
            'uid' => $uid,
            'mobile' => $address['mobile'],
            'name' => $address['name'],
            'address' => $address['address'],
            'add_time' => time(),
            'payment' => $pay['code'],
            'shipping' => '韵达',
            'sum_price' => $post['sum_price'],
        ];
        $oid = db('order_info')->insertGetId($order_info);
        if ($oid) {
            $carts = db('cart')->where('user_id', $uid)->select();
            foreach ($carts as $key => $val) {
                $goods[$key]['order_id'] = $oid;
                $goods[$key]['goods_name'] = $val['goods_name'];
                if ($val['goods_sku']) {
                    $price = db('sku')->where('id', $val['goods_sku'])->value('price');
                } else {
                    $price = $val['goods_price'];
                }
                $goods[$key]['goods_price'] = $price;
                $goods[$key]['goods_num'] = $val['goods_num'];
                $goods[$key]['goods_sku'] = $val['goods_sku'];
                $goods[$key]['goods_id'] = $val['goods_id'];
            }
            $res = db('order_goods')->insertAll($goods);
            if ($res) {
                db('cart')->where('user_id', $uid)->delete();
                $this->success('下单成功，正在前往支付页面', 'flow/pay?oid='.$oid);
            } else {
                $this->error('下单失败，系统开小差了..');
            }
        } else {
            $this->error('下单失败');
        }
    }
    public function pay()
    {
        $oid = input('oid');
        $order = db('order_info')->find($oid);
        if($order['pay_status'] == 0)
        {
            $config = config('alipay.');
            $data = [
                "_input_charset" => $config['input_charset'],
                "notify_url" => 'http://zhouweiyao.xarlit.cn/index/notify/alipay', // 异步接收支付状态通知的链接
                "out_trade_no" => $order['order_sn'], // 订单号
                "partner" => $config['partner'],
                "price" => $order['sum_price'],
                "return_url" => 'http://zhouweiyao.xarlit.cn/user/order/info/id/'.$oid, // 页面跳转 同步通知
                "seller_email" => $config['seller_email'],
                "service" => "create_direct_pay_by_user",
                "subject" => '测试商品'
            ];
            $alipay = new Alipay($config);
            $new=$alipay->buildRequestPara($data);
            $go_pay=$alipay->buildRequestForm($new, 'get','支付');
            echo $go_pay;die;
        }
    }

    public function price()
    {
        $id = input('id');
        $data = db('sku')->find($id);
        $arr = ['code'=>0,'msg'=>'正在查询详细信息','num'=>$data['num'],'price'=>$data['price']];
        return json($arr);
    }
}