<?php
/*支付宝回掉页面*/
define('IN_AOS', true);
$is_wap = true;
require('../source/aoshop.php');
require(ROOT_PATH . 'source/library/order.php');
if (empty($_SESSION['user_id']))
{
  if($_GET['result'] == 'success')
  {
      $smarty->display('alipay_success.htm');
      exit;
  }
  else
  {
      $smarty->display('alipay_fail.htm');
      exit;
  }
}
else
{
  $order_id = $_GET['out_trade_no'];
  if($_GET['result'] == 'success')
  {
    $order = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['aos']->table('order_info') . " WHERE order_id = '$order_id' limit 1");
    if($order['extension_code'] == 'tuan')
    {
      $back_act = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=share&tuan_id='.$order['extension_id'];
    }
    else
    {
      $back_act = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=user&a=order_detail&order_id='.$order_id;
    }
    aos_header("Location: ".$back_act."\n");
    exit;
  }
  else
  {
    $back_act = AOS_HTTP . $_SERVER ['HTTP_HOST'].$_CFG['directory'].'/index.php?c=user&a=order_detail&order_id='.$order_id;
    aos_header("Location: ".$back_act."\n");
    exit;
  }
}
?>
