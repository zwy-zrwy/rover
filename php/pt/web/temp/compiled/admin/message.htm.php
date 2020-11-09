<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="renderer" content="webkit">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?php echo $this->_var['shop_name']; ?>管理中心<?php if ($this->_var['ur_here']): ?> - <?php echo $this->_var['ur_here']; ?> <?php endif; ?></title>
<link rel="stylesheet" href="static/css/style.css"/>
<script src="static/js/jquery.min.js"></script>
<script src="static/js/layer.js"></script>
<script src="static/js/common.js"></script>
</head>
<body>
<div class="header">
	<div class="wrap">
		<h1 class="logo"><a href="./"></a></h1>
		<div class="account">
            <ul>
                <li>您好&nbsp;:&nbsp;<strong><?php echo $this->_var['admin_name']; ?></strong></li>
                <li><a href="index.php?act=admin&op=modif"><span>设置</span></a></li>
                <li><a href="javascript:void(0)" onClick="clearCache()">更新站点缓存</a></li>
                <li><a href="index.php?act=login&op=logout"><span>退出</span></a></li>
            </ul>
		</div>
	</div>
</div>

<div class="message">
<h3><?php echo $this->_var['msg_detail']; ?></h3>
<?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['link']):
?>
<p><a href="<?php echo $this->_var['link']['href']; ?>" <?php if ($this->_var['link']['target']): ?>target="<?php echo $this->_var['link']['target']; ?>"<?php endif; ?>><?php echo $this->_var['link']['text']; ?></a></p>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>
<script language="JavaScript">
<!--
var seconds = 3;
var defaultUrl = "<?php echo $this->_var['default_url']; ?>";
onload = function()
{
  if (defaultUrl == 'javascript:history.go(-1)' && window.history.length == 0)
  {
    document.getElementById('redirectionMsg').innerHTML = '';
    return;
  }
}
function redirection()
{
  if (seconds <= 0)
  {
    window.clearInterval();
    return;
  }

  seconds --;
  document.getElementById('spanSeconds').innerHTML = seconds;

  if (seconds == 0)
  {
    window.clearInterval();
    location.href = defaultUrl;
  }
}
//-->
</script>
</body>
</html>