<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title><?php echo $this->_var['shop_name']; ?>管理中心</title>
<link href="static/css/login.css" rel="stylesheet" type="text/css">
<link href="static/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="static/js/jquery.min.js"></script>
<script type="text/javascript" src="static/js/login.js"></script>
<script type="text/javascript" src="static/js/jquery.supersized.min.js"></script>
<script type="text/javascript" src="static/js/jquery.progressBar.js"></script>

<script language="JavaScript">
<!--
// 这里把JS用到的所有语言都赋值到这里
<?php $_from = $this->_var['lang']['js_languages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

if (window.parent != window)
{
  window.top.location.href = location.href;
}

//-->
</script>
</head>
<body>
<div class="login-layout">
    <div class="top">
        <h2><?php echo $this->_var['shop_name']; ?>管理中心<span><?php echo $this->_var['aos_version']; ?></span></h2>
    </div>
    <form method="post" id="form_login" action="index.php?act=login" name="theForm" >
        <div class="lock-holder">
            <div class="form-group pull-left input-username">
                <label>账号</label>
                <input name="username" id="user_name" autocomplete="off" type="text" class="input-text" value="" required>
            </div>
            <i class="fa fa-ellipsis-h dot-left"></i> <i class="fa fa-ellipsis-h dot-right"></i>
            <div class="form-group pull-right input-password-box">
                <label>密码</label>
                <input name="password" id="password" class="input-text" autocomplete="off" type="password" required pattern="[\S]{6}[\S]*" title="密码不少于6个字符">
            </div>
        </div>
        <div class="avatar"><img src="static/images/login/admin.png" alt=""></div>
        <div class="submit">
            <?php if ($this->_var['gd_version'] > 0): ?>
            <span>
                <div class="code">
                    <div class="arrow"></div>
                    <div class="code-img"><img src="index.php?act=captcha&<?php echo $this->_var['random']; ?>" name="codeimage" id="codeimage" border="0"/></div>
                    <a href="JavaScript:void(0);" id="hide" class="close" title="关闭"><i></i></a><a href="JavaScript:void(0);" onclick="javascript:document.getElementById('codeimage').src='index.php?act=captcha&' + Math.random();" class="change" title="<?php echo $this->_var['lang']['click_for_another']; ?>"><i></i></a>
                </div>
                <input name="captcha" type="text" required class="input-code" id="captcha" placeholder="输入验证" pattern="[A-z0-9]{4}" title="验证码为4个字符" autocomplete="off" value="" >
            </span>
            <?php endif; ?>
            <span>
                <input type="hidden" name="op" value="signin" />
                <input class="input-button btn-submit" type="submit" value="登录">
            </span>
        </div>
        <div class="submit2"></div>
    </form>
    <div class="bottom">
        <h6>Copyright 2015-<?php echo $this->_var['now_year']; ?> Xarlit Inc.</h6>
        <h6>Powered by Xarlit</h6>
    </div>
</div>
<script>
$(function(){
	$.supersized({
        slide_interval:4000,transition:1,transition_speed:1000,performance:1,min_width:0,min_height:0,vertical_center:1,horizontal_center:1,fit_always:0,fit_portrait:1,fit_landscape:0,slide_links:'blank',slides:[{image:'static/images/login/1.jpg'},{image:'static/images/login/2.jpg'},{image:'static/images/login/3.jpg'},{image:'static/images/login/4.jpg'},{image:'static/images/login/5.jpg'}]
    });
	//显示隐藏验证码
    $("#hide").click(function(){
        $(".code").fadeOut("slow");
    });
    $("#captcha").focus(function(){
        $(".code").fadeIn("fast");
    });
    //跳出框架在主窗口登录
	if(top.location!=this.location)	top.location=this.location;
    $('#user_name').focus();
    $("#captcha").nc_placeholder();
	//动画登录
    $('.btn-submit').click(function(e){
		
        if($("input[name='username']").val()==''){
            $("input[name='username']").focus().select();
            return false;
        }
        if($("input[name='password']").val()==''){
            $("input[name='password']").focus().select();
            return false;
        }
        if($("input[name='captcha']").val()==''){
            $("input[name='captcha']").focus().select();
            return false;
        }
		$('.input-username,dot-left').addClass('animated fadeOutRight')
        $('.input-password-box,dot-right').addClass('animated fadeOutLeft')
        $('.btn-submit').addClass('animated fadeOutUp')
        setTimeout(function () {
			$('.avatar').addClass('avatar-top');
            $('.submit').hide();
            $('.submit2').html('<div class="progress"><div class="progress-bar progress-bar-success" aria-valuetransitiongoal="100"></div></div>');
			$('.progress .progress-bar').progressbar({
				done :function() {
					$('#form_login').submit();
				}
			}); 
		},300);
    });
    // 回车提交表单
    $('#form_login').keydown(function(event){
        if (event.keyCode == 13) {
            $('.btn-submit').click();
        }
    });
});

</script>
</body>
</html>