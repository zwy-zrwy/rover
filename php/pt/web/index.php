<?php
/*入口文件*/
define('IN_AOS', true);
$module = isset($_REQUEST['m']) ? trim($_REQUEST['m']) : 'index';
$controller = isset($_REQUEST['c']) ? trim($_REQUEST['c']) : 'index';
$action = isset($_REQUEST['a']) ? trim($_REQUEST['a']) : 'index';
$c_arr=array('alipay','auto','pc','closed');
if(in_array($controller, $c_arr) || $module == 'app'){
  $is_wap = true;
}
require('source/aoshop.php');
$smarty->assign('action',$action);
if ((DEBUG_MODE & 2) != 2)
{
  $smarty->caching = true;
}
if($module == 'app')
{

  include_once('app/app.php');
  


  include_once('app/'.$controller.'.php');
}
else
{
  include_once('source/module/'.$controller.'.php');
}

?>