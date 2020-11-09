<?php

define('IN_AOS', true);


$exc = new exchange($aos->table("user_rank"), $db, 'rank_id', 'rank_name');
$exc_user = new exchange($aos->table("users"), $db, 'user_rank', 'user_rank');
admin_priv('rank_manage');
/*------------------------------------------------------ */
//-- 会员等级列表
/*------------------------------------------------------ */

if ($operation == 'rank_list')
{
    $ranks = array();
    $ranks = $db->getAll("SELECT * FROM " .$aos->table('user_rank'));
    $smarty->assign('user_ranks',   $ranks);
    $smarty->display('user_rank.htm');
}
elseif ($operation == 'post')
{
  if(isset($_POST['rank_id']))
  {
    $ids= count($_POST['rank_id']);
  }
  for($i=0; $i<$ids; $i++)
  {
    $sql = "UPDATE " . $aos->table('user_rank') . " SET min_points = '".$_POST[min_points][$i]."',max_points = '".$_POST[max_points][$i]."' WHERE rank_id = ".$_POST[rank_id][$i];
    $db->query($sql);
  }
  if($db)
  {
    $links[] = array('text' => '返回列表', 'href' => 'index.php?act=rank&op=rank_list');
    sys_msg('修改成功', 0, $links);
  }
}

?>