<?php

/*文章分类管理程序*/

define('IN_AOS', true);

//分类列表
if ($operation == 'articlecat_list')
{
    $articlecat = article_cat_list();
    $smarty->assign('ur_here',     '文章分类列表');
    $smarty->assign('articlecat',        $articlecat);
    $smarty->display('articlecat_list.htm');
}
elseif ($operation == 'post')
{
  //print_r($_POST);die;
  if(isset($_POST['cat_id']))
  {
    $ids= count($_POST['cat_id']);
  }
  for($i=0; $i<$ids; $i++)
  {
    $sql = "UPDATE " . $aos->table('article_cat') . " SET cat_value = '".$_POST[cat_value][$i]."',cat_desc = '".$_POST[cat_desc][$i]."' WHERE cat_id = ".$_POST[cat_id][$i];
    $db->query($sql);
  }
  if($db)
  {
    $links[] = array('text' => "返回列表", 'href' => 'index.php?act=articlecat&op=articlecat_list');
    sys_msg('修改成功', 0, $links);
  }
}


function article_cat_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('article_cat');
    return $GLOBALS['db']->GetAll($sql);
}
?>
