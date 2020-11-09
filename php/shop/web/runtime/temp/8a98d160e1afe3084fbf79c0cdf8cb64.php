<?php /*a:2:{s:55:"D:\SVN\zhouweiyao\application\admin\view\goods\add.html";i:1575287813;s:52:"D:\SVN\zhouweiyao\application\admin\view\layout.html";i:1576717639;}*/ ?>
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
        <div style="padding: 15px;"><form action="<?php echo url('admin/goods/add'); ?>" method="post" class="layui-form" enctype="multipart/form-data">
    <div class="layui-form-item">
        <label class="layui-form-label">商品名</label>
        <div class="layui-input-block">
            <input type="text" name="name" required  lay-verify="required" placeholder="请输入商品名" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
            <label class="layui-form-label">品牌</label>
            <div class="layui-input-block">
                <select name="brand_id" lay-verify="required">
                    <?php if(is_array($brand) || $brand instanceof \think\Collection || $brand instanceof \think\Paginator): $i = 0; $__LIST__ = $brand;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <option value="<?php echo htmlentities($vo['id']); ?>"><?php echo htmlentities($vo['name']); ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
            </div>
        </div>
    <div class="layui-form-item">
        <label class="layui-form-label">预览图</label>
        <div class="layui-input-block">
            <input type="file" name="pic" required  lay-verify="required"  class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">展示图</label>
        <div class="layui-input-block">
            <input type="file" name="pics[]"  class="layui-input" multiple="multiple">
        </div>
    </div>
    <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">简介</label>
        <div class="layui-input-block">
            <textarea name="content" placeholder="请输入商品简介" class="layui-textarea"></textarea>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">价格</label>
        <div class="layui-input-block">
            <input type="text" name="price" required  lay-verify="required" placeholder="请输入商品价格" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">库存</label>
        <div class="layui-input-block">
            <input type="text" name="number" required  lay-verify="required" placeholder="请输入商品库存" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">种类</label>
        <div class="layui-input-block">
            <select name="cid" lay-verify="required">
                <?php if(is_array($cat) || $cat instanceof \think\Collection || $cat instanceof \think\Paginator): $i = 0; $__LIST__ = $cat;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if(is_array($vo['sub']) || $vo['sub'] instanceof \think\Collection || $vo['sub'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['sub'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;if(is_array($sub['sub']) || $sub['sub'] instanceof \think\Collection || $sub['sub'] instanceof \think\Paginator): $i = 0; $__LIST__ = $sub['sub'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cat): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($cat['id']); ?>"><?php echo htmlentities($cat['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">规格</label>
        <div class="layui-input-block">
            <input type="radio"  name="is_attr" value="1" title="启用" >
            <input type="radio"  name="is_attr" value="0" title="禁用" checked >
            <table class="layui-table" id="sb">
                <thead>
                <tr><th>名称</th><th>价格</th><th>库存</th></tr>
                </thead>
                <tbody>
                <?php if(is_array($sttr) || $sttr instanceof \think\Collection || $sttr instanceof \think\Paginator): $i = 0; $__LIST__ = $sttr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td><?php echo htmlentities($vo['name']); ?></td><td><input class="layui-input" type="text" name="prices[<?php echo htmlentities($vo['id']); ?>]"></td><td><input  class="layui-input" type="text" name="nums[<?php echo htmlentities($vo['id']); ?>]" ></td>
                </tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">状态</label>
        <div class="layui-input-block">
            <input type="radio" name="status" value="1" title="启用" checked>
            <input type="radio" name="status" value="0" title="禁用">
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="submit" value="提交" class="layui-btn">
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form></div>
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