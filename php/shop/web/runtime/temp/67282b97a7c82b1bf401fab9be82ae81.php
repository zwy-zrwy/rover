<?php /*a:1:{s:76:"/www/wwwroot/shop.zhouweiyaocloud.xyz/application/user/view/login/index.html";i:1585114428;}*/ ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/css/style.css" />
    <link rel="stylesheet" href="/static/css/base.css" />
    <title>用户登录</title>
</head>

<body>
<div class="register_header">
    <div class="reg_hc">
        <img src="/static/images/logo.png" />
        <h4>登录</h4>
        <span class="forget fr"><a href="<?php echo url('login/register'); ?>">去注册</a></span>
    </div>
</div>
<div class="register_con">
    <div class="register_conr">
        <div class="register">
        </div>
        <div class="clear"></div>
        <div class="user per_register" style="padding-top: 40px;">
            <span>用户名：</span>
            <img src="/static/images/user.png" class="psw" style="margin-top: 10px;" />
            <input type="text" name="username" id="IDnum" class="input_comm" placeholder="请输入用户名" style="margin-top: 0px;">
            <br />
            <span>密码：</span>
            <img src="/static/images/password.png" class="psw" />
            <input type="password" name="password" id="password" class="input_comm" placeholder="请输入密码" />
            <br />
            <span>验证码：</span>
            <input type="text" name="code" class="yanzhengma" placeholder="请输入验证码" />
            <img src="<?php echo url('verify'); ?>" onclick="javascript:this.src=this.src+'?time='+Math.random()"  class="yanzheng" />
            <div class="clear"></div>
            <input type="button" name="denglu" value="登 录" class="btn_register" id="toIndex" onclick="login()"/>
        </div>
    </div>>
</div>
</body>
<script src="/static/layui/layui.js"></script>
<script>
    layui.use(['layer','jquery'], function() {
        var $ = layui.jquery,
            layer = layui.layer;
    });
    function login()
    {
        var username = $("input[name='username']").val();
        var password = $("input[name='password']").val();
        var code = $("input[name='code']").val();
        $.ajax({
            'url':"<?php echo url('index'); ?>",
            'data':{'username':username,'password':password,'code':code},
            'type':'post',
            'dataType':'json',
            success:function(res) {
                layer.msg(res.msg);
                if(res.code == 0){
                    location.href = "<?php echo url('index/index/index'); ?>";
                }
            }
        })
    }
</script>
<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/kuCity.js"></script>
<!--<script>-->
    <!--$('.business').click(function() {-->
        <!--$('.business_register').show();-->
        <!--$('.per_register').hide();-->
        <!--$(this).addClass('active1').removeClass('cancel');-->
        <!--$('.personal').addClass('cancel').removeClass('active1');-->
    <!--});-->
    <!--$('.personal').click(function() {-->
        <!--$('.business_register').hide();-->
        <!--$('.per_register').show();-->
        <!--$(".business").addClass('cancel').removeClass('active1');-->
        <!--$(this).addClass('active1').removeClass('cancel');-->
    <!--});-->
    <!--$("#toIndex").click(function(){-->
        <!--window.location.href = "index.html"-->
    <!--})-->
<!--</script>-->
<!--<script>-->
    <!--$('.search').kuCity();-->
<!--</script>-->
</html>