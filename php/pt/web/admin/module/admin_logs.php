<?php

define('IN_AOS', true);

/*------------------------------------------------------ */
//-- 获取所有日志列表
/*------------------------------------------------------ */
if ($operation == 'admin_logs')
{
    /* 权限的判断 */
    admin_priv('logs_manage');

    $user_id   = !empty($_REQUEST['id'])       ? intval($_REQUEST['id']) : 0;
    $admin_ip  = !empty($_REQUEST['ip'])       ? $_REQUEST['ip']         : '';
    $log_date  = !empty($_REQUEST['log_date']) ? $_REQUEST['log_date']   : '';

    /* 查询IP地址列表 */
    $ip_list = array();
    $res = $db->query("SELECT DISTINCT ip_address FROM " .$aos->table('admin_log'));
    while ($row = $db->FetchRow($res))
    {
        $ip_list[$row['ip_address']] = $row['ip_address'];
    }

    $smarty->assign('ur_here',   '管理员日志');
    $smarty->assign('ip_list',   $ip_list);
    $smarty->assign('full_page', 1);

    $log_list = get_admin_logs();

    $smarty->assign('log_list',        $log_list['list']);
    $smarty->assign('filter',          $log_list['filter']);
    $smarty->assign('record_count',    $log_list['record_count']);
    $smarty->assign('page_count',      $log_list['page_count']);

    $sort_flag  = sort_flag($log_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    
    $pager = get_page($log_list['filter']);


    $smarty->assign('pager',   $pager);

    
    $smarty->display('admin_logs.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    $log_list = get_admin_logs();

    $smarty->assign('log_list',        $log_list['list']);
    $smarty->assign('filter',          $log_list['filter']);
    $smarty->assign('record_count',    $log_list['record_count']);
    $smarty->assign('page_count',      $log_list['page_count']);

    $sort_flag  = sort_flag($log_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('admin_logs.htm'), '',
        array('filter' => $log_list['filter'], 'page_count' => $log_list['page_count']));
}

/*------------------------------------------------------ */
//-- 批量删除日志记录
/*------------------------------------------------------ */
if ($operation == 'batch_drop')
{
    admin_priv('logs_drop');

    $drop_type_date = isset($_REQUEST['drop_type_date']) ? $_REQUEST['drop_type_date'] : '';

    /* 按日期删除日志 */
    if ($drop_type_date)
    {
        if ($_REQUEST['log_date'] == '0')
        {
            aos_header("Location: index.php?act=admin_logs&op=admin_logs\n");
            exit;
        }
        elseif ($_REQUEST['log_date'] > '0')
        {
            $where = " WHERE 1 ";
            switch ($_REQUEST['log_date'])
            {
                case '1':
                    $a_week = gmtime()-(3600 * 24 * 7);
                    $where .= " AND log_time <= '".$a_week."'";
                    break;
                case '2':
                    $a_month = gmtime()-(3600 * 24 * 30);
                    $where .= " AND log_time <= '".$a_month."'";
                    break;
                case '3':
                    $three_month = gmtime()-(3600 * 24 * 90);
                    $where .= " AND log_time <= '".$three_month."'";
                    break;
                case '4':
                    $half_year = gmtime()-(3600 * 24 * 180);
                    $where .= " AND log_time <= '".$half_year."'";
                    break;
                case '5':
                    $a_year = gmtime()-(3600 * 24 * 365);
                    $where .= " AND log_time <= '".$a_year."'";
                    break;
            }
            $sql = "DELETE FROM " .$aos->table('admin_log').$where;
            $res = $db->query($sql);
            if ($res)
            {
                admin_log('','remove', 'adminlog');

                $link[] = array('text' => "返回日志列表", 'href' => 'index.php?act=admin_logs&op=admin_logs');
                sys_msg("删除成功", 1, $link);
            }
        }
    }
    /* 如果不是按日期来删除, 就按ID删除日志 */
    else
    {
        $count = 0;
        foreach ($_REQUEST['checkboxes'] AS $key => $id)
        {
            $sql = "DELETE FROM " .$aos->table('admin_log'). " WHERE log_id = '$id'";
            $result = $db->query($sql);

            $count++;
        }
        if ($result)
        {
            admin_log('', 'remove', 'adminlog');

            $link[] = array('text' => "返回日志列表", 'href' => 'index.php?act=admin_logs&op=admin_logs');
            sys_msg("成功删除".$count."条日志",  0, $link);
        }
    }
}

/* 获取管理员操作记录 */
function get_admin_logs()
{
    $user_id  = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    $admin_ip = !empty($_REQUEST['ip']) ? $_REQUEST['ip']         : '';

    $filter = array();
    $filter['sort_by']      = empty($_REQUEST['sort_by']) ? 'al.log_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order']   = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    //查询条件
    $where = " WHERE 1 ";
    if (!empty($user_id))
    {
        $where .= " AND al.user_id = '$user_id' ";
    }
    elseif (!empty($admin_ip))
    {
        $where .= " AND al.ip_address = '$admin_ip' ";
    }

    /* 获得总记录数据 */
    $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['aos']->table('admin_log'). ' AS al ' . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    /* 获取管理员日志记录 */
    $list = array();
    $sql  = 'SELECT al.*, u.user_name FROM ' .$GLOBALS['aos']->table('admin_log'). ' AS al '.
            'LEFT JOIN ' .$GLOBALS['aos']->table('admin_user'). ' AS u ON u.user_id = al.user_id '.
            $where .' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];
    $res  = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $rows['log_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['log_time']);

        $list[] = $rows;
    }

    return array('list' => $list, 'filter' => $filter, 'page_count' =>  $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>