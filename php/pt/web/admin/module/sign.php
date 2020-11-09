<?php

define('IN_AOS', true);
admin_priv('users_sign');
/* act操作项的初始化 */
if ($operation == 'index')
{
    $operation = 'users_sign';
}



/*------------------------------------------------------ */
//-- 设置页面
/*------------------------------------------------------ */
if ($operation == 'users_sign')
{
    $sql="select * from ".$aos->table('sign_options')." where  option_name = 'sign'";
    $setting=$db->getRow($sql);
    $smarty->assign('sign_setting', $setting);

    $smarty->display('hpyer_setting.htm');
}

if ($operation == 'save_setting')
{
    $sign = $_POST['sign'];
    $result = $db->autoExecute($aos->table('sign_options'), $sign, 'UPDATE', "option_name='sign'");
    
    $link[0]['text'] = "返回";
    $link[0]['href'] = '?act=sign';

    sys_msg("设置成功",0, $link);
}


/*------------------------------------------------------ */
//-- 留言列表页面
/*------------------------------------------------------ */
if ($operation == 'sign_log_list')
{


    $list = get_sign_log_list();
    
    $pager = get_page($list['filter']);

    $smarty->assign('pager',   $pager);

    $smarty->assign('list', $list['item']);


    $smarty->display('hpyer_sign_log_list.htm');
}

/*------------------------------------------------------ */
//-- 批量删除留言记录
/*------------------------------------------------------ */
elseif ($operation == 'drop_sign_log')
{
    if (isset($_POST['checkboxes']))
    {
        $count = 0;
        foreach ($_POST['checkboxes'] AS $key => $id)
        {
            $sql = "DELETE FROM " .$aos->table('sign_log'). " WHERE id=$id";
            $db->query($sql);

            $count++;
        }

        $link[] = array('text' =>'返回列表', 'href' => 'index.php?act=sign&op=sign_log_list');
        sys_msg("共删除".$count."条记录", 0, $link);
    }
    else
    {
        sys_msg("没有要删除的记录", 1);
    }
}

/*------------------------------------------------------ */
//-- 删除留言
/*------------------------------------------------------ */
elseif ($operation == 'remove_sign_log')
{
    $id = intval($_REQUEST['id']);

    $sql = "DELETE FROM ".$aos->table('sign_log')." WHERE id=$id";
    $db->query($sql);

    $link[] = array('text' =>'返回列表', 'href' => 'index.php?act=sign&op=sign_log_list');
    make_json_result($id);
}
/**
 * 获取签到日志列表
 * @return void
 */
function get_sign_log_list()
{
    /* 查询条件 */
    $filter['sort_by']    = empty($_REQUEST['sort_by'])    ? 'id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['user_name']  = empty($_REQUEST['user_name'])  ? '' : trim($_REQUEST['user_name']);

    /* 查询条件 */
    if ($filter['user_name'])
    {
        $where = " AND b.nickname LIKE '%" .$filter['user_name']. "%'";
    }

    $sql = "SELECT COUNT(*) 
            FROM ".$GLOBALS['aos']->table('sign_log')." AS a 
                LEFT JOIN ".$GLOBALS['aos']->table('users')." AS b ON a.uid=b.user_id 
            WHERE 1{$where}";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    $sql = "SELECT a.*,b.nickname
            FROM ".$GLOBALS['aos']->table('sign_log')." AS a 
                LEFT JOIN ".$GLOBALS['aos']->table('users')." AS b ON a.uid=b.user_id 
            WHERE 1{$where} 
            ORDER BY {$filter['sort_by']} {$filter['sort_order']} 
            LIMIT {$filter['start']}, {$filter['page_size']}";
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key=>$val)
    {
        $row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
    }

    $arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>