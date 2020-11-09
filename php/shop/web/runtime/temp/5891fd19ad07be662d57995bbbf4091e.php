<?php /*a:2:{s:58:"D:\shop\zhouweiyao\application\admin\view\goods\index.html";i:1574333125;s:53:"D:\shop\zhouweiyao\application\admin\view\layout.html";i:1576717639;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>后端</title>
    <link rel="stylesheet" href="/static/layui/css/layui.css">
    <script src="/static/layui/jquery-3.4.1.js"></script>
    <script src="/static/layui/layui.js"></script>
</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo">商城后台</div>
        <!-- 头部区域（可配合layui已有的水平导航） -->
        <ul class="layui-nav layui-layout-left">
            <li class="layui-nav-item"><a href="http://zhouweiyao.xarlit.cn/admin/access_token/index">更新Access_Token</a></li>
            <li class="layui-nav-item"><a href="http://zhouweiyao.xarlit.cn/admin/jsapi_ticket/index">更新jsapi_ticket</a></li>
            <li class="layui-nav-item"><a href="">用户</a></li>
            <li class="layui-nav-item">
                <a href="javascript:;">其它系统</a>
                <dl class="layui-nav-child">
                    <dd><a href="">邮件管理</a></dd>
                    <dd><a href="">消息管理</a></dd>
                    <dd><a href="">授权管理</a></dd>
                </dl>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">
                <a href="javascript:;">
                   欢迎你！<?php echo htmlentities(app('session')->get('admin_name')); ?>
                </a>
                <dl class="layui-nav-child">
                    <dd><a href="">基本资料</a></dd>
                    <dd><a href="">安全设置</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item"><a href="<?php echo url('admin/login/logout'); ?>">退出</a></li>
        </ul>
    </div>

    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="layui-nav layui-nav-tree"  lay-filter="test">
                <?php if(is_array($menu) || $menu instanceof \think\Collection || $menu instanceof \think\Paginator): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <li class="layui-nav-item" >
                    <a class="" href="javascript:;"><?php echo htmlentities($vo['name']); ?></a>
                    <dl class="layui-nav-child">
                        <?php if(is_array($vo['sub']) || $vo['sub'] instanceof \think\Collection || $vo['sub'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['sub'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>
                        <dd <?php if($sub['con'] == $con): ?> class="layui-this" <?php endif; ?>><a href="<?php echo url($sub['con']); ?>" ><?php echo htmlentities($sub['name']); ?></a></dd>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </dl>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>

    <div class="layui-body">
        <!-- 内容主体区域 -->
        <div style="padding: 15px;"><a href="<?php echo url('admin/goods/add'); ?>" class="layui-btn layui-btn-sm layui-btn-warm"><i class="layui-icon">&#xe654;</i>添加</a>
<table class="layui-table">
    <thead>
    <tr><th>ID</th><th>预览图</th><th>名称</th><th>品牌</th><th>简介</th><th>价格</th><th>库存</th><th>商品种类</th><th>状态</th><th>操作</th></tr></thead>
    <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tbody><tr>
        <td><?php echo htmlentities($vo['id']); ?></td><td><img src="<?php echo htmlentities($vo['pic']); ?>" alt="" height="40px"></td>
        <td><?php echo htmlentities($vo['name']); ?></td><td><?php echo htmlentities($vo['bname']); ?></td><td><?php echo htmlentities($vo['content']); ?></td><td><?php echo htmlentities($vo['price']); ?></td><td><?php echo htmlentities($vo['number']); ?></td><td><?php echo htmlentities($vo['cname']); ?></td><td><?php if($vo['status'] == 1): ?>启用<?php else: ?>禁用<?php endif; ?></td>
        <td width="200px" >
            <a href="<?php echo url('admin/goods/edit',['id'=>$vo['id']]); ?>" class="layui-btn layui-btn-sm  layui-btn-normal"><i class="layui-icon">&#xe6b2;</i>编辑</a>
            <a href="<?php echo url('admin/goods/del',['id'=>$vo['id']]); ?>" class="layui-btn  layui-btn-sm layui-btn-danger"><i class="layui-icon">&#xe640;</i>删除</a>
        </td>
    </tr></tbody>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</table>
<?php echo $data; ?>
</div>
    </div>

    <div class="layui-footer">
        <!-- 底部固定区域 -->
    </div>
</div>
<script>
    //JavaScript代码区域
    layui.use(['element','form'], function(){
        var element = layui.element,form = layui.form;

    });
    $(".layui-this").parent().parent().addClass('layui-nav-itemed');
</script>
</body>
</html>