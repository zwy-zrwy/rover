<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/*绑定微信管理员*/
if ($action == 'binding')
{
  $store_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
  if($store_id)
  {
	  $sql = "select `id` from ".$aos->table('wxmanage')." where `store_id` = " . $store_id ." and `openid` = '".$_SESSION['openid']."' ";
	  $nums = $db->getOne($sql);
	  if($nums)
	  {
	      show_message('您已经绑定过了！','', 'index.php');
	      exit();
	  }

	  $sql = "insert into ".$aos->table('wxmanage')." set `store_id` = '".$store_id."',`openid` = '".$_SESSION['openid']."' ";
	  $db->query($sql);

	  show_message('绑定成功！','', 'index.php');
	  exit();
  }else{
	  $sql = "select `id` from ".$aos->table('wxmanage')." where `store_id` = 0 and `openid` = '".$_SESSION['openid']."' ";
	  $nums = $db->getOne($sql);
	  if($nums)
	  {
	      show_message('您已经绑定过了！','', 'index.php');
	      exit();
	  }

	  $sql = "insert into ".$aos->table('wxmanage')." set `store_id` = '0',`openid` = '".$_SESSION['openid']."' ";
	  $db->query($sql);

	  show_message('绑定成功！','', 'index.php');
	  exit();

  }
}


?>