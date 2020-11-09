<?php

define('IN_AOS', true);
$exc = new exchange($aos->table('shipping'), $db, 'shipping_id', 'shipping_name');
/* act操作项的初始化 */
if ($operation == 'index' || $operation == 'shipping_manage')
{
    $operation = 'list';
}
/*------------------------------------------------------ */
//-- 配送方式列表
/*------------------------------------------------------ */
admin_priv('ship_manage');
if ($operation == 'list')
{
    $shipping_list = shipping_list();
    $smarty->assign('shipping_list', $shipping_list);
    $smarty->display('shipping_list.htm');
}
elseif ($operation == 'post')
{
  //print_r($_POST);die;
  if(isset($_POST['shipping_id']))
  {
    $ids= count($_POST['shipping_id']);
  }
  for($i=0; $i<$ids; $i++)
  {
    if(!empty($_POST[shipping_id][$i])){
      $sql = "UPDATE " . $aos->table('shipping') . " SET shipping_name = '".$_POST[shipping_name][$i]."',shipping_code = '".$_POST[shipping_code][$i]."' WHERE shipping_id = ".$_POST[shipping_id][$i];
      $db->query($sql);
    }elseif(!empty($_POST[shipping_name][$i])){
      $sql = "insert into " . $aos->table('shipping') . " (shipping_name,shipping_code)values('".$_POST[shipping_name][$i]."','".$_POST[shipping_code][$i]."')";
      $db->query($sql);
    }
    
  }
  if($db)
  {
    $links[] = array('text' => '返回列表', 'href' => 'index.php?act=shipping&op=list');
    sys_msg('修改成功', 0, $links);
  }
}
elseif ($operation== 'toggle_enabled')
{
    $shipping_id       = intval($_POST['id']);
    $enabled        = intval($_POST['val']);

    if ($exc->edit("enabled = '$enabled'", $shipping_id))
    {
        clear_cache_files();
        make_json_result($enabled);
    }
}

/**
 * 取得配送方式
 * @return  array   配送方式
 */
function shipping_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('shipping') . " WHERE shipping_id > 0";
    return $GLOBALS['db']->getAll($sql);
}

?>