<?php

define('IN_AOS', true);

$exc = new exchange($aos->table('wx_msg'), $db, 'title', 'name');
/* act操作项的初始化 */
if ($operation == 'wxmsg_list')
{
  $wxmsg = wxmsg();
  $smarty->assign('wxmsg', $wxmsg); 
  $smarty->display('wxmsg.htm');
}
elseif ($operation == 'post')
{

//print_r($_POST);
  if(isset($_POST['id']))
  {
    $ids= count($_POST['id']);
  }
  for($i=0; $i<$ids; $i++)
  {
    $sql = "UPDATE " . $aos->table('wx_msg') . " SET title = '".$_POST[title][$i]."',note = '".$_POST[note][$i]."' WHERE id = ".$_POST[id][$i];
    $db->query($sql);
  }
  if($db)
  {
    $links[] = array('text' => '返回模板消息', 'href' => 'index.php?act=wxmsg&op=wxmsg_list');
    sys_msg('修改成功', 0, $links);
  }
}



/**
 * 取得模板消息
 * @return  array   配送方式
 */
function wxmsg()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('wx_msg');
    return $GLOBALS['db']->getAll($sql);
}

?>