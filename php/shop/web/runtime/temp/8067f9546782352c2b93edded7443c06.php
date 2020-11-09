<?php /*a:1:{s:60:"D:\shop\zhouweiyao\application\index\view\flow\checkout.html";i:1574239594;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="/static/layui/css/layui.css">
    <script src="/static/layui/layui.js"></script>
    <script src="/static/layui/jquery-3.4.1.js"></script>
</head>
<body>
<h2>商品列表</h2>
<table class="layui-table">
    <thead>
    <tr>
        <th>图片</th><th>名称</th><th>价格</th><th>数量</th><th>小计</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td><img src="<?php echo htmlentities($vo['pic']); ?>" alt=""></td><td><?php echo htmlentities($vo['goods_name']); ?></td><td><?php echo htmlentities($vo['goods_price']); ?></td><td><?php echo htmlentities($vo['goods_num']); ?></td>
        <td>￥<?php echo htmlentities($vo['sum_price']); ?></td>
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    <tr>
        <td colspan="5" style="text-align:right;font-size:24px">总计￥<?php echo htmlentities($total_price); ?></td>
    </tr>
    </tbody>
</table>

<h2>收货地址</h2>
<form action="<?php echo url('flow/done'); ?>"  method="post" class="layui-form">
    <input type="hidden" name="sum_price" value="<?php echo htmlentities($total_price); ?>">
<table class="layui-table">
    <thead><tr><th></th></tr></thead>
    <tbody>
    <?php if(is_array($address) || $address instanceof \think\Collection || $address instanceof \think\Paginator): $i = 0; $__LIST__ = $address;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td>
            <input type="radio" name="address_id" value="<?php echo htmlentities($vo['id']); ?>"><?php echo htmlentities($vo['name']); ?> <b>联系电话：</b><?php echo htmlentities($vo['mobile']); ?>  <b>地址：</b><?php echo htmlentities($vo['address']); ?>
        </td>
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
</table>
<h2>支付方式</h2>
    <table class="layui-table">
        <thead><tr><th></th></tr></thead>
        <tbody>
        <?php if(is_array($pay) || $pay instanceof \think\Collection || $pay instanceof \think\Paginator): $i = 0; $__LIST__ = $pay;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <tr>
            <td>
                <input type="radio" name="pay_id" value="<?php echo htmlentities($vo['id']); ?>"><?php echo htmlentities($vo['name']); ?> <img src="<?php echo htmlentities($vo['pic']); ?>" alt="">
            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        </tbody>
    </table>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="submit" value="提交" class="layui-btn">
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form>
</body>
<script>
    layui.use(['layer','jquery','form'], function() {
        var $ = layui.jquery,
            layer = layui.layer,form = layui.form;
    });
</script>
</html>