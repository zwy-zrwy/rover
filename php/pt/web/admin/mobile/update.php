<?php

define('IN_AOS', true);

$appname = $_REQUEST['appname'];
$version = $_REQUEST['version'];
$release = $_REQUEST['release'];

if ($operation == 'index')
{
	$smarty->assign('appname',$appname);
	$smarty->assign('version',$version);
	$smarty->assign('release',$release);
    $smarty->display('update.htm');
}
elseif ($operation == 'step')
{

	include_once(ROOT_PATH . '/source/class/cloud.class.php');
	$cloud = new Cloud('temp/update');
    $cloud->handle($release, $version, $appname);
}
?>