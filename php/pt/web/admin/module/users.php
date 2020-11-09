<?php

define('IN_AOS', true);
include_once(ROOT_PATH . '/source/class/image.class.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($aos->table('users'), $db, 'user_id', 'nickname');
/*------------------------------------------------------ */
//-- 用户帐号列表
/*------------------------------------------------------ */
admin_priv('user_manage');

if ($operation == 'users_manage')
{
    $smarty->assign('ur_here',      '会员列表');
    $user_list = user_list();
    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $pager = get_page($user_list['filter']);
    $smarty->assign('pager',   $pager);
    $smarty->display('users_list.htm');
}

if ($operation == 'virtual_member')
{
    $smarty->assign('ur_here',      '虚拟会员列表');
    $user_list = user_list(1);
    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $pager = get_page($user_list['filter']);
    $smarty->assign('pager',   $pager);
    $smarty->display('virtual_member.htm');
}

/*------------------------------------------------------ */
//-- ajax返回用户列表
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    $user_list = user_list();

    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);

    $sort_flag  = sort_flag($user_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('users_list.htm'), '', array('filter' => $user_list['filter'], 'page_count' => $user_list['page_count']));
}

//添加虚拟会员
elseif ($operation == 'user_add')
{
    $smarty->assign('form_act',      'insert');
    $smarty->display('user_info.htm');
}
elseif ($operation == 'insert')
{
    $nickname = trim($_POST['nickname']);
    $province = trim($_POST['province']);
    $city = trim($_POST['city']);
    $sql="insert into ".$aos->table('users')." (nickname,province,city,virtual) values ('$nickname','$province','$city',1)";
    $db->query($sql);
    $user_id=$db->insert_id();
    if(!empty($_FILES['headimgurl'])){
        $headimgurl   = $image->upload_image($_FILES['headimgurl'],'avatar',"avatar_".$user_id.".jpg");
        $sql="update ".$aos->table('users')." set headimgurl='$headimgurl' where user_id = '$user_id'";
        $db->query($sql);
    }
    
    sys_msg('添加成功');

}
elseif ($operation == 'user_edit')
{
    $sql = "SELECT user_id,nickname,headimgurl,province,city FROM " .$aos->table('users'). " WHERE user_id='$_GET[id]'";
    $user_info = $db->GetRow($sql);
    $smarty->assign('user_info', $user_info);
    $smarty->assign('form_act',      'update');
    $smarty->display('user_info.htm');
}
elseif ($operation == 'update')
{
    $user_id =intval($_POST['user_id']);
    $sql = "SELECT user_id,headimgurl FROM " .$aos->table('users'). " WHERE user_id='$user_id' AND virtual = 1";
    $user_info = $db->GetRow($sql);
    if(!$user_info){
        sys_msg('会员不存在');
    }
    $nickname = trim($_POST['nickname']);
    $province = trim($_POST['province']);
    $city = trim($_POST['city']);
    $sql="update ".$aos->table('users')." set nickname='$nickname',province='$province',city='$city' where user_id = '$user_id'";
    $db->query($sql);
    if(!empty($_FILES['headimgurl'])){
        @unlink($user_info['headimgurl']);
        $headimgurl   = $image->upload_image($_FILES['headimgurl'],'avatar',"avatar_".$user_id.".jpg");
        $sql="update ".$aos->table('users')." set headimgurl='$headimgurl' where user_id = '$user_id'";
        $db->query($sql);
    }
    sys_msg('修改成功');
}

elseif ($operation== 'check_nickname')
{
    $user_id = intval($_REQUEST['user_id']);
    $nickname = htmlspecialchars(json_str_iconv(trim($_POST["param"])));

    /* 检查是否重复 */
    if (!$exc->is_only('nickname', $nickname, $user_id))
    {
        $result['info']= '您填写的用户名称已存在';
    }
    else
    {
        $result['status']= 'y';
    }
    die(json_encode($result));
}

/*------------------------------------------------------ */
//-- 批量删除会员帐号
/*------------------------------------------------------ */

elseif ($operation == 'batch_remove')
{
    if (isset($_POST['checkboxes']))
    {
        $sql = "SELECT user_name FROM " . $aos->table('users') . " WHERE user_id " . db_create_in($_POST['checkboxes']);
        $col = $db->getCol($sql);
        $usernames = implode(',',addslashes_deep($col));
        $count = count($col);
        /* 通过插件来删除用户 */
        $users = init_users();
        $users->remove_user($col);

        admin_log($usernames, 'batch_remove', 'users');

        $lnk[] = array('text' => '返回上一页', 'href'=>'index.php?act=users&op=users_manage');
        sys_msg(sprintf('已经成功删除了 %d 个会员账号。', $count), 0, $lnk);
    }
    else
    {
        $lnk[] = array('text' => '返回上一页', 'href'=>'users.php?act=list');
        sys_msg('您现在没有需要删除的会员！', 0, $lnk);
    }
}

/*------------------------------------------------------ */
//-- 删除会员帐号
/*------------------------------------------------------ */

elseif ($operation == 'remove')
{
    $id=intval($_REQUEST['id']);
    $sql = "SELECT nickname FROM " . $aos->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
    $username = $db->getOne($sql);
    /* 通过插件来删除用户 */
    $users=remove_user($id); //已经删除用户所有数据

    /* 记录管理员操作 */
    admin_log(addslashes($username), 'remove', 'users');

    /* 提示信息 */
    make_json_result($id);
}

/*------------------------------------------------------ */
//--  收货地址查看
/*------------------------------------------------------ */
elseif ($operation == 'address_list')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $sql = "SELECT * ".
           " FROM " .$aos->table('user_address').
           " WHERE user_id='$id'";
    $address = $db->getAll($sql);

    foreach($address as $idx=>$value)
    {
        $area = explode(',',$value['area']);
        $area['province'] = get_region_name($area['0']);
        $area['city'] = get_region_name($area['1']);
        $area['district'] = get_region_name($area['2']);
        $address[$idx]['area']  = $area['province'].$area['city'].$area['district'];
    }
    $smarty->assign('address',          $address);
    
    $smarty->assign('ur_here',          '收货地址');
    $smarty->assign('action_link',      array('text' => '会员列表', 'href'=>'index.php?act=users&op=users_manage&' . list_link_postfix()));
    $smarty->display('user_address_list.htm');
}
/**
 *  返回用户列表数据
 *
 * @access  public
 * @param
 *
 * @return void
 */
function user_list($virtual = 0)
{
    $result = get_filter();
    //if ($result === false)
    //{
        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        
        $filter['mobile']    = trim($_REQUEST['mobile']);
        $filter['sort_by']    = empty($_REQUEST['sort_by'])    ? 'user_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC'     : trim($_REQUEST['sort_order']);

        $ex_where = " WHERE 1 AND virtual = $virtual ";
        if ($filter['keywords'])
        {
            $ex_where .= " AND nickname LIKE '%" . mysql_like_quote($filter['keywords']) ."%'";
        }
        if ($filter['mobile'])
        {
            $ex_where .= " AND mobile LIKE '%" . mysql_like_quote($filter['mobile']) ."%'";
        }
        $filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('users') . $ex_where);

        /* 分页大小 */
        $filter = page_and_size($filter);
        $sql = "SELECT user_id, mobile, nickname, realname, sex, pay_points, reg_time, headimgurl, province, city, subscribe ".
                " FROM " . $GLOBALS['aos']->table('users') . $ex_where .
                " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
                " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    /*}
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }*/

    $user_list = $GLOBALS['db']->getAll($sql);

    $count = count($user_list);
    for ($i=0; $i<$count; $i++)
    {
        $user_list[$i]['reg_time'] = local_date($GLOBALS['_CFG']['date_format'], $user_list[$i]['reg_time']);
        $user_list[$i]['dist_amount'] = get_user_dist($user_list[$i]['user_id']);
    }

    $arr = array('user_list' => $user_list, 'filter' => $filter,
        'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

function get_user_dist($user_id)
{
    $sql = "SELECT SUM(dist_money) FROM " .$GLOBALS['aos']->table('account_log').
           " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getOne($sql);
}
?>