<?php /*a:1:{s:57:"D:\shop\zhouweiyao\application\user\view\order\index.html";i:1574314165;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/static/layui/css/layui.css">
    <script src="/static/layui/layui.js"></script>
    <title>Document</title>
</head>
<body>
<?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
<table class="layui-table">
    <tbody>
    <tr colspan="5">
        <b>订单号：</b><?php echo htmlentities($vo['order_sn']); ?>
        <b>总价：￥</b><?php echo htmlentities($vo['sum_price']); ?>
        <b>下单时间：</b><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($vo['add_time'])? strtotime($vo['add_time']) : $vo['add_time'])); ?>
        <a href="<?php echo url('info',['id'=>$vo['id']]); ?>" style="color:blue">查看订单</a>  <?php if($vo['pay_status'] == 0): ?><a href="<?php echo url('index/flow/pay',['id'=>$vo['id']]); ?>" style="color:red">立即支付</a> <?php endif; ?>
    </tr>
    </tbody>
    <thead>
    <tr>
        <th>图片</th><th>名称</th><th>价格</th><th>数量</th><th>规格</th>
    </tr>
    </thead>
    <?php if(is_array($vo['goods']) || $vo['goods'] instanceof \think\Collection || $vo['goods'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['goods'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?>
    <tbody>
    <tr>
        <td><img src="<?php echo htmlentities($goods['pic']); ?>" alt="" height="30px"></td><td><?php echo htmlentities($goods['goods_name']); ?></td>
        <td><?php echo htmlentities($goods['goods_price']); ?></td><td><?php echo htmlentities($goods['goods_num']); ?></td><td><?php echo htmlentities($goods['goods_sku']); ?></td>
    </tr>
    </tbody>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</table>
<?php endforeach; endif; else: echo "" ;endif; ?>
</body>
</html>
