<?php
define('IN_AOS', true);

/*微信管理员*/
if ($operation == 'wxmanage')
{
  $sql = "select s.*,u.nickname from " . $GLOBALS['aos']->table('wxmanage') ." as s," . $GLOBALS['aos']->table('users') ." as u WHERE u.`openid` = s.`openid` and s.`store_id` = 0";
  $wxmanage = $db->getAll($sql);
  $smarty->assign('wxmanage',$wxmanage);
  $smarty->assign('id',           $store_id);
  $smarty->display('notice_wxmanage.htm');
}
/*解绑微信管理员*/
elseif ($operation == 'unbinding')
{
    $result = array('error' => 0, 'message' => '');
  $id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
  if($id)
  {
     $res = $db->query("DELETE FROM " . $GLOBALS['aos']->table('wxmanage') ." WHERE `id` = '".$id."'"); 
    if($res)
    {
        $result['error'] = 1;
        $result['message'] = '删除成功';
        $result['manage_id'] = $id;
        die(json_encode($result));
    }
    else
    {
        $result['error'] = 0;
        $result['message'] = '删除失败';
        die(json_encode($result));
    }
  }   
}

?>