<?php

define('IN_AOS', true);

$exc = new exchange($aos->table('store'), $db, 'store_id', 'store_name');
admin_priv('store_manage');
/*------------------------------------------------------ */
//-- 办事处列表
/*------------------------------------------------------ */
if ($operation == 'store_list')
{

    $store_list = get_storelist();
    $smarty->assign('store_list',  $store_list['store']);
    $smarty->assign('filter',       $store_list['filter']);
    $smarty->assign('record_count', $store_list['record_count']);
    $smarty->assign('page_count',   $store_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($store_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    
    $smarty->display('store_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    $store_list = get_storelist();
    $smarty->assign('store_list',  $store_list['store']);
    $smarty->assign('filter',       $store_list['filter']);
    $smarty->assign('record_count', $store_list['record_count']);
    $smarty->assign('page_count',   $store_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($store_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('store_list.htm'), '',
        array('filter' => $store_list['filter'], 'page_count' => $store_list['page_count']));
}

/*------------------------------------------------------ */
//-- 列表页编辑名称
/*------------------------------------------------------ */
elseif ($operation == 'edit_store_name')
{
    check_authz_json('store_manage');

    $id     = intval($_POST['id']);
    $name   = json_str_iconv(trim($_POST['val']));

    /* 检查名称是否重复 */
    if ($exc->num("store_name", $name, $id) != 0)
    {
        make_json_error(sprintf('该门店名称已存在，请您换一个名称', $name));
    }
    else
    {
        if ($exc->edit("store_name = '$name'", $id))
        {
            admin_log($name, 'edit', 'store');
            clear_cache_files();
            make_json_result(stripslashes($name));
        }
        else
        {
            make_json_result(sprintf('编辑门店名称时出错，请您再试一次', $name));
        }
    }
}

/*------------------------------------------------------ */
//-- 删除办事处
/*------------------------------------------------------ */
elseif ($operation == 'remove')
{
    check_authz_json('store_manage');

    $id = intval($_GET['id']);
    $name = $exc->get_name($id);
    $exc->drop($id);

    /* 记日志 */
    admin_log($name, 'remove', 'store');

    /* 清除缓存 */
    clear_cache_files();

    $url = 'index.php?act=store&op=store_list';

    aos_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */
elseif ($operation == 'batch')
{
    /* 取得要操作的记录编号 */
    if (empty($_POST['checkboxes']))
    {
        sys_msg('没有选择记录');
    }
    else
    {
        /* 检查权限 */
        admin_priv('store_manage');

        $ids = $_POST['checkboxes'];

        if (isset($_POST['remove']))
        {
            /* 删除记录 */
            $sql = "DELETE FROM " . $aos->table('store') .
                    " WHERE store_id " . db_create_in($ids);
            $db->query($sql);

            /* 更新管理员、配送地区、发货单、退货单和订单关联的办事处 */
            $table_array = array('admin_user', 'region', 'order_info', 'delivery_order', 'back_order');
            foreach ($table_array as $value)
            {
                $sql = "UPDATE " . $aos->table($value) . " SET store_id = 0 WHERE store_id " . db_create_in($ids) . " ";
                $db->query($sql);
            }

            /* 记日志 */
            admin_log('', 'batch_remove', 'store');

            /* 清除缓存 */
            clear_cache_files();

            sys_msg('批量删除成功');
        }
    }
}

/*------------------------------------------------------ */
//-- 添加、编辑办事处
/*------------------------------------------------------ */
elseif ($operation == 'store_add' || $operation == 'store_edit')
{
    /* 检查权限 */
    admin_priv('store_manage');

    /* 是否添加 */
    $is_add = $operation == 'store_add';
    $smarty->assign('form_action', $is_add ? 'insert' : 'update');

    /* 初始化、取得办事处信息 */
    if ($is_add)
    {
        $store = array(
            'store_id'     => 0,
            'store_name'   => '',
            'store_mobile'   => '',
            'store_address'   => '',
            'store_coordinate'   => '',
            'store_desc'   => '',
        );
    }
    else
    {
        if (empty($_GET['id']))
        {
            sys_msg('invalid param');
        }

        $id = $_GET['id'];
        $sql = "SELECT * FROM " . $aos->table('store') . " WHERE store_id = '$id'";
        $store = $db->getRow($sql);
        if (empty($store))
        {
            sys_msg('store does not exist');
        }

    }

    $smarty->assign('store', $store);


    /* 显示模板 */
    
    $smarty->display('store_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑办事处
/*------------------------------------------------------ */
elseif ($operation == 'insert' || $operation == 'update')
{
    /* 检查权限 */
    admin_priv('store_manage');

    /* 是否添加 */
    $is_add = $operation == 'insert';

    /* 提交值 */
    $store = array(
        'store_id'     => intval($_POST['id']),
        'store_name'   => sub_str($_POST['store_name'], 255, false),
        'store_mobile'   => $_POST['store_mobile'],
        'store_address'   => $_POST['store_address'],
        'store_coordinate'   => $_POST['store_coordinate'],
        'store_desc'   => $_POST['store_desc']
    );

    /* 判断名称是否重复 */
    if (!$exc->is_only('store_name', $store['store_name'], $store['store_id']))
    {
        sys_msg("门店名称重复");
    }

    /* 保存办事处信息 */
    if ($is_add)
    {
        $db->autoExecute($aos->table('store'), $store, 'INSERT');
        $store['store_id'] = $db->insert_id();
    }
    else
    {
        $db->autoExecute($aos->table('store'), $store, 'UPDATE', "store_id = '$store[store_id]'");
    }

    /* 记日志 */
    if ($is_add)
    {
        admin_log($store['store_name'], 'add', 'store');
    }
    else
    {
        admin_log($store['store_name'], 'edit', 'store');
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    if ($is_add)
    {
        $links = array(
            array('href' => 'index.php?act=store&op=store_add', 'text' => '继续添加新的门店'),
            array('href' => 'index.php?act=store&op=store_list', 'text' => "返回门店列表")
        );
        sys_msg("添加成功", 0, $links);
    }
    else
    {
        $links = array(
            array('href' => 'index.php?act=store&op=store_list&' . list_link_postfix(), 'text' => "返回门店列表")
        );
        sys_msg("编辑成功", 0, $links);
    }
}
/*门店微信管理员*/
elseif ($operation == 'wxmanage')
{
  $store_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;

  $sql = "select s.*,u.nickname from " . $GLOBALS['aos']->table('wxmanage') ." as s," . $GLOBALS['aos']->table('users') ." as u WHERE u.`openid` = s.`openid` and s.`store_id` = " . $store_id;
  $wxmanage = $db->getAll($sql);
  $smarty->assign('wxmanage',$wxmanage);
  $smarty->assign('id',           $store_id);
  $smarty->display('store_wxmanage.htm');
}
/*解绑门店微信管理员*/
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
/**
 * 取得办事处列表
 * @return  array
 */
function get_storelist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'store_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);

        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('store');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT * FROM " . $GLOBALS['aos']->table('store') . " ORDER BY $filter[sort_by] $filter[sort_order]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $arr[] = $rows;
    }

    return array('store' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>