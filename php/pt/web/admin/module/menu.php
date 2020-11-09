<?php

define('IN_AOS', true);
include_once(ROOT_PATH . '/source/class/image.class.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($aos->table('menu'), $db, 'menu_id', 'menu_name');
/* act操作项的初始化 */
if ($operation == 'index' || $operation == 'menu_manage')
{
    $operation = 'list';
}
/*------------------------------------------------------ */
//-- 配送方式列表
/*------------------------------------------------------ */
admin_priv('menu_manage');
if ($operation == 'list')
{
    $menu_list = menu_list();
    $smarty->assign('menu_list', $menu_list);
    $smarty->display('menu_list.htm');
}
elseif ($operation == 'post')
{
  //print_r($_POST);die;
  if(isset($_POST['menu_id']))
  {
    $ids= count($_POST['menu_id']);
  }
  for($i=0; $i<$ids; $i++)
  {
    if(!empty($_POST[menu_id][$i])){
      $sql = "UPDATE " . $aos->table('menu') . " SET menu_name = '".$_POST[menu_name][$i]."',menu_url = '".$_POST[menu_url][$i]."',sort_order = '".$_POST[sort_order][$i]."' WHERE menu_id = ".$_POST[menu_id][$i];
      $db->query($sql);

      $m_img = array('name' => $_FILES['menu_img']['name'][$i] , 'type' => $_FILES['menu_img']['type'][$i] , 'tmp_name' => $_FILES['menu_img']['tmp_name'][$i] , 'error' => $_FILES['menu_img']['error'][$i], 'size' => $_FILES['menu_img']['size'][$i]);


      if (($m_img['tmp_name'] != '' && $m_img['tmp_name'] != 'none'))
      {
          if ($_POST[menu_id][$i] > 0)
          {
              /* 删除原来的图片文件 */
              $sql = "SELECT menu_img FROM " . $aos->table('menu') .
                      " WHERE menu_id = ".$_POST[menu_id][$i];
              $row = $db->getRow($sql);
              if ($row['menu_img'] != '' && is_file(ROOT_PATH . $row['menu_img']))
              {
                  @unlink(ROOT_PATH . $row['menu_img']);
                  //oss_delete_file($row['goods_thumb']);
              }
          }
          $menu_img   = $image->upload_image($m_img,'menu_img');
          $sql = "UPDATE " . $aos->table('menu') . " SET menu_img = '".$menu_img."' WHERE menu_id = ".$_POST[menu_id][$i];
          $db->query($sql);

      }
      
    }elseif(!empty($_POST[menu_name][$i])){
      $sql = "insert into " . $aos->table('menu') . " (menu_name,menu_url,sort_order)values('".$_POST[menu_name][$i]."','".$_POST[menu_url][$i]."','".$_POST[sort_order][$i]."')";
      $db->query($sql);
    }
    
  }
  if($db)
  {
    $links[] = array('text' => '返回列表', 'href' => 'index.php?act=menu&op=list');
    sys_msg('修改成功', 0, $links);
  }
}
elseif ($operation== 'toggle_enabled')
{
    $menu_id       = intval($_POST['id']);
    $enabled        = intval($_POST['val']);

    if ($exc->edit("enabled = '$enabled'", $menu_id))
    {
        clear_cache_files();
        make_json_result($enabled);
    }
}

/**
 * 取得配送方式
 * @return  array   配送方式
 */
function menu_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('menu')." where type = 0";
    return $GLOBALS['db']->getAll($sql);
}

?>