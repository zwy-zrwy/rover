<?php /*a:1:{s:57:"D:\shop\zhouweiyao\application\user\view\index\index.html";i:1574495535;}*/ ?>
<a href="<?php echo url('order/index'); ?>">我的订单</a>
<a href="<?php echo url('index/edit',['id'=>app('session')->get('user_id')]); ?>">修改个人信息</a>
<a href="<?php echo url('login/logout'); ?>">退出登录</a>
<!--<a href="<?php echo url('index/del',['id'=>app('session')->get('user_id')]); ?>">注销账户</a>-->