<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="renderer" content="webkit">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?php echo $this->_var['shop_name']; ?>管理中心<?php if ($this->_var['ur_here']): ?> - <?php echo $this->_var['ur_here']; ?> <?php endif; ?></title>
<script src="static/js/jquery.min.js"></script>
<script src="static/js/layer.js"></script>
<script src="static/js/common.js"></script>
<script src="static/js/validform.js"></script>
<link rel="stylesheet" href="static/css/style.css"/>
<link rel="stylesheet" href="static/css/font-awesome.min.css"/>
<!--[if lte IE 10]><script>window.location.href='http://cdn.dmeng.net/upgrade-your-browser.html?referrer='+location.href;</script><![endif]-->
<script>
if (window.top != window)
{
  window.top.location.href = document.location.href;
}
</script>
</head>
<body>
<div class="header">
	<div class="wrap">
		<h1 class="logo"><a href="./"></a></h1>
        <div class="version"><?php echo $this->_var['aos_version']; ?></div>
        <div class="auth"></div>
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
