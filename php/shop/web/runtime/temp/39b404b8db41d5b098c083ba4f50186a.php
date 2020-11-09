<?php /*a:1:{s:60:"D:\shop\zhouweiyao\application\user\view\login\register.html";i:1574492568;}*/ ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/css/style.css" />
    <link rel="stylesheet" href="/static/css/base.css" />
    <title>注册</title>
</head>

<body>
<div class="register_header">
    <div class="reg_hc">
        <img src="/static/images/logo.png" />
        <h4>欢迎注册</h4>
        <span class="forget fr">已有账号？<a href="login.html">请登录</a></span>
    </div>
</div>
<div class="register_con">
    <div class="register_conr">
        <div class="register">
            <h5 class="personal active1">个人注册</h5>
            <h6 class="business cancel">商户注册</h6>
        </div>
        <div class="clear"></div>
        <div class="user per_register">
            <span>用户名：</span>
            <img src="/static/images/user.png" class="psw" style="margin-top: 10px;" />
            <input type="text" name="username" id="IDnum" class="input_comm" placeholder="请输入用户名" style="margin-top: 0px;">
            <br />
            <span>密码：</span>
            <img src="/static/images/password.png" class="psw" />
            <input type="password" name="password" id="password" class="input_comm" placeholder="请至少使用两种字符组合" />
            <br />
            <span>确认密码：</span>
            <img src="/static/images/password.png" class="psw" />
            <input type="password" name="repassword" id="repassword" class="input_comm" placeholder="请确认密码" />
            <br />
            <span>邮箱：</span>
            <img src="/static/images/email.png" class="psw"  />
            <input type="email" name="email" class="input_comm" placeholder="请输入邮箱" />
            <button class="quire_yzm" onclick="emailCode()">获取验证码</button>
            <br />
            <span>验证码：</span>
            <input type="text" name="code" class="yanzhengma" placeholder="请输入验证码" />
            <div class="clear"></div>
            <input type="hidden" name="recode" class="yanzhengma"/>
            <input type="button" name="denglu" value="注   册" class="btn_register" onclick="register()"/>
        </div>
        <!--商户注册-->
        <div class="user business_register">
            <span>商店名称：</span>
            <img src="/static/images/business.png" class="psw" style="margin-top: 10px;" />
            <input type="text" name="username" id="IDnum" class="input_comm" placeholder="请输商店名称" style="margin-top: 0px;">
            <br />
            <span>密码：</span>
            <img src="/static/images/password.png" class="psw" />
            <input type="password" name="password" id="password" class="input_comm" placeholder="请输入密码" />
            <br />
            <span>确认密码：</span>
            <img src="/static/images/password.png" class="psw" />
            <input type="password" name="repassword" id="repassword" class="input_comm" placeholder="请确认密码" />
            <br />
            <span>手机号：</span>
            <img src="/static/images/phone.png" class="psw" />
            <input type="text" name="phone" id="phone" class="input_comm" placeholder="手机号" />
            <br />
            <span>邮箱：</span>
            <img src="/static/images/email.png" class="psw" />
            <input type="text" name="yanzhengma" class="input_comm" placeholder="请输入邮箱" />
            <button class="quire_yzm" >获取验证码</button>
            <br />
            <span>城市：</span>
            <img src="/static/images/place2.png" class="psw" />
            <input type="text" placeholder="请输入或选择商铺所在城市" class="input_comm search" />
            <span>验证码：</span>
            <input type="text" name="yanzhengma" class="yanzhengma" placeholder="请输入验证码" autocomplete="off"/>
            <img src="/static/images/yanzhengma.png" class="yanzheng" />
            <p class="change fr">
                <a href="#">看不清楚 再换一张</a>
            </p>
            <div class="clear"></div>
            <input type="button" name="register" value="注   册" class="btn_register"/>
        </div>
    </div>
    <div class="clear"></div>
</div>
</body>
<script src="/static/layui/layui.js"></script>
<script>
    layui.use(['layer','jquery'], function() {
        var $ = layui.jquery,
            layer = layui.layer;
    });
    function register()
    {
        var username = $(".per_register input[name='username']").val();
        var password = $(".per_register input[name='password']").val();
        var repassword = $(".per_register input[name='repassword']").val();
        var email = $(".per_register input[name='email']").val();
        var code = $(".per_register input[name='code']").val();
        var recode = $(".per_register input[name='recode']").val();
        $.ajax({
            'url':"<?php echo url('register'); ?>",
            'data':{'username':username,'password':password,'repassword':repassword,'email':email,'code':code,'recode':recode},
            'type':'post',
            'dataType':'json',
            success:function(res) {
                if(res.code == 0){
                    layer.msg(res.msg);
                    location.href = "<?php echo url('login/index'); ?>";
                }
                else
                {
                    layer.msg(res.msg);
                }
            }
        })
    }
    var flag = true;
    function emailCode()
    {
        if(flag)
        {
            flag = false;
            var username = $(".per_register input[name='username']").val();
            var password = $(".per_register input[name='password']").val();
            var repassword = $(".per_register input[name='repassword']").val();
            var email = $(".per_register input[name='email']").val();
            $.ajax({
                'url':"<?php echo url('email'); ?>",
                'data':{'username':username,'email':email},
                'type':'post',
                'dataType':'json',
                success:function(res){
                    if(res.code == 0){
                        layer.msg(res.msg);
                        $(".per_register input[type='hidden']").val(res.recode);
                        flag = true;
                    }
                }
            })
        }
        return false;
    }
</script>
<script src="/static/js/jquery.min.js"></script>
<script src="/static/js/kuCity.js"></script>
<script>
    $('.business').click(function() {
        $('.business_register').show();
        $('.per_register').hide();
        $(this).addClass('active1').removeClass('cancel');
        $('.personal').addClass('cancel').removeClass('active1');
    });
    $('.personal').click(function() {
        $('.business_register').hide();
        $('.per_register').show();
        $(".business").addClass('cancel').removeClass('active1');
        $(this).addClass('active1').removeClass('cancel');
    });
</script>
<script>
    $('.search').kuCity();
</script>
</html>