<?php

define('IN_AOS', true);
$exc = new exchange($aos->table('goods_label'), $db, 'label_id', 'label_name');
/* act操作项的初始化 */
if ($operation == 'index' || $operation == 'label_list')
{
    $operation = 'list';
}
/*------------------------------------------------------ */
//-- 配送方式列表
/*------------------------------------------------------ */
admin_priv('label_manage');
if ($operation == 'list')
{
    $label_list = label_list();
    $smarty->assign('label_list', $label_list);
    $smarty->display('label_list.htm');
}
elseif ($operation == 'post')
{
  //print_r($_POST);die;
  if(isset($_POST['label_id']))
  {
    $ids= count($_POST['label_id']);
  }
  for($i=0; $i<$ids; $i++)
  {
    if(!empty($_POST[label_id][$i])){
      $sql = "UPDATE " . $aos->table('goods_label') . " SET label_name = '".$_POST[label_name][$i]."',label_desc = '".$_POST[label_desc][$i]."' WHERE label_id = ".$_POST[label_id][$i];
      $db->query($sql);
    }elseif(!empty($_POST[label_name][$i])){
      $sql = "insert into " . $aos->table('goods_label') . " (label_name,label_desc)values('".$_POST[label_name][$i]."','".$_POST[label_desc][$i]."')";
      $db->query($sql);
    }
    
  }
  if($db)
  {
    $links[] = array('text' => '返回列表', 'href' => 'index.php?act=label&op=list');
    sys_msg('修改成功', 0, $links);
  }
}
elseif ($operation== 'toggle_enabled')
{
    $label_id       = intval($_POST['id']);
    $enabled        = intval($_POST['val']);

    if ($exc->edit("enabled = '$enabled'", $label_id))
    {
        clear_cache_files();
        make_json_result($enabled);
    }
}

/**
 * 取得配送方式
 * @return  array   配送方式
 */
function label_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('goods_label');
    return $GLOBALS['db']->getAll($sql);
}

?>