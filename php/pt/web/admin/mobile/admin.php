<?php

define('IN_AOS', true);

/* 初始化 $exc 对象 */
$exc = new exchange($aos->table("admin_user"), $db, 'user_id', 'user_name');
admin_priv('admin_manage');
/*------------------------------------------------------ */
//-- 管理员列表页面
/*------------------------------------------------------ */
if ($operation == 'admin_list')
{
    /* 模板赋值 */
    $smarty->assign('ur_here',     '管理员列表');
    $smarty->assign('action_link', array('href'=>'admin.php?act=admin&op=add', 'text' => '添加管理员'));
    $smarty->assign('full_page',   1);
    $smarty->assign('admin_list',  get_admin_userlist());

    /* 显示页面 */
    
    $smarty->display('admin_list.htm');
}

/*------------------------------------------------------ */
//-- 查询
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    $smarty->assign('admin_list',  get_admin_userlist());

    make_json_result($smarty->fetch('admin_list.htm'));
}

/*------------------------------------------------------ */
//-- 添加管理员页面
/*------------------------------------------------------ */
elseif ($operation == 'add')
{
     /* 模板赋值 */
    $smarty->assign('ur_here',     '添加管理员');
    $smarty->assign('action_link', array('href'=>'admin.php?act=admin&op=list', 'text' => '管理员列表'));
    $smarty->assign('form_act',    'insert');
    $smarty->assign('action',      'add');

    /* 显示页面 */
    
    $smarty->display('admin_info.htm');
}

/*------------------------------------------------------ */
//-- 添加管理员的处理
/*------------------------------------------------------ */
elseif ($operation == 'insert')
{
    if($_POST['token']!=$_CFG['token'])
    {
         sys_msg('add_error', 1);
    }
    /* 判断管理员是否已经存在 */
    if (!empty($_POST['user_name']))
    {
        $is_only = $exc->is_only('user_name', stripslashes($_POST['user_name']));

        if (!$is_only)
        {
            sys_msg("管理员名称重复", 1);
        }
    }


    /* 获取添加日期及密码 */
    $add_time = gmtime();
    
    $password  = md5($_POST['password']);
    $role_id = '';
    $action_list = '';
    if (!empty($_POST['select_role']))
    {
        $sql = "SELECT action_list FROM " . $aos->table('role') . " WHERE role_id = '".$_POST['select_role']."'";
        $row = $db->getRow($sql);
        $action_list = $row['action_list'];
        $role_id = $_POST['select_role'];
    }

        $sql = "SELECT nav_list FROM " . $aos->table('admin_user') . " WHERE action_list = 'all'";
        $row = $db->getRow($sql);


    $sql = "INSERT INTO ".$aos->table('admin_user')." (user_name, email, password, add_time, nav_list, action_list, role_id) ".
           "VALUES ('".trim($_POST['user_name'])."', '".trim($_POST['email'])."', '$password', '$add_time', '$row[nav_list]', '$action_list', '$role_id')";

    $db->query($sql);
    /* 转入权限分配列表 */
    $new_id = $db->Insert_ID();

    /*添加链接*/
    $link[0]['text'] = '设置管理员权限';
    $link[0]['href'] = 'index.php?act=admin&op=allot&id='.$new_id.'&user='.$_POST['user_name'].'';

    $link[1]['text'] = '继续添加管理员';
    $link[1]['href'] = 'index.php?act=admin&op=add';

    sys_msg('添加' . "&nbsp;" .$_POST['user_name'] . "&nbsp;" . "成功",0, $link);

    /* 记录管理员操作 */
    admin_log($_POST['user_name'], 'add', 'privilege');
 }

/*------------------------------------------------------ */
//-- 编辑管理员信息
/*------------------------------------------------------ */
elseif ($operation == 'edit')
{

    $_REQUEST['id'] = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    /* 查看是否有权限编辑其他管理员的信息 */
    if ($_SESSION['admin_id'] != $_REQUEST['id'])
    {
        admin_priv('admin_manage');
    }

    /* 获取管理员信息 */
    $sql = "SELECT user_id, user_name, email, password, store_id, role_id FROM " .$aos->table('admin_user').
           " WHERE user_id = '".$_REQUEST['id']."'";
    $user_info = $db->getRow($sql);


    /* 模板赋值 */
    $smarty->assign('ur_here',     '修改管理员');
    $smarty->assign('action_link', array('text' => '管理员列表', 'href'=>'index.php?act=admin&op=list'));
    $smarty->assign('user',        $user_info);

    $smarty->assign('form_act',    'update');
    $smarty->assign('action',      'edit');

    
    $smarty->display('admin_info.htm');
}

/*------------------------------------------------------ */
//-- 更新管理员信息
/*------------------------------------------------------ */
elseif ($operation == 'update' || $operation == 'update_self')
{
    /* 变量初始化 */
    $admin_id    = !empty($_REQUEST['id'])        ? intval($_REQUEST['id'])      : 0;
    $admin_name  = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
    $admin_email = !empty($_REQUEST['email'])     ? trim($_REQUEST['email'])     : '';
    $ao_salt=rand(1,9999);
    $password = !empty($_POST['new_password']) ? ", password = '".md5(md5($_POST['new_password']).$ao_salt)."'"    : '';
    if($_POST['token']!=$_CFG['token'])
    {
         sys_msg('update_error', 1);
    }
    if ($operation == 'update')
    {
        $g_link = 'index.php?act=admin&op=admin_list';
        $nav_list = '';
    }
    else
    {
        $nav_list = !empty($_POST['nav_list'])     ? ", nav_list = '".@join(",", $_POST['nav_list'])."'" : '';
        $admin_id = $_SESSION['admin_id'];
        $g_link = 'index.php?act=admin&op=modif';
    }
    /* 判断管理员是否已经存在 */
    if (!empty($admin_name))
    {
        $is_only = $exc->num('user_name', $admin_name, $admin_id);
        if ($is_only == 1)
        {
            sys_msg("管理员名称重复", 1);
        }
    }


    //如果要修改密码
    $pwd_modified = false;

    if (!empty($_POST['new_password']))
    {
        /* 查询旧密码并与输入的旧密码比较是否相同 */
        $sql = "SELECT password FROM ".$aos->table('admin_user')." WHERE user_id = '$admin_id'";
        $old_password = $db->getOne($sql);
		$sql ="SELECT ec_salt FROM ".$aos->table('admin_user')." WHERE user_id = '$admin_id'";
        $old_ao_salt= $db->getOne($sql);
		if(empty($old_ao_salt))
	    {
			$old_ao_password=md5($_POST['old_password']);
		}
		else
	    {
			$old_ao_password=md5(md5($_POST['old_password']).$old_ao_salt);
		}
        if ($old_password <> $old_ao_password)
        {
           $link[] = array('text' => "返回", 'href'=>'javascript:history.back(-1)');
           sys_msg("原密码不正确", 0, $link);
        }

        /* 比较新密码和确认密码是否相同 */
        if ($_POST['new_password'] <> $_POST['pwd_confirm'])
        {
           $link[] = array('text' => "返回", 'href'=>'javascript:history.back(-1)');
           sys_msg("两次输入密码不同", 0, $link);
        }
        else
        {
            $pwd_modified = true;
        }
    }


    //更新管理员信息
    if($pwd_modified)
    {
        $sql = "UPDATE " .$aos->table('admin_user'). " SET ".
               "user_name = '$admin_name', ".
               "email = '$admin_email', ".
               "ec_salt = '$ao_salt' ".
               $password.
               $nav_list.
               "WHERE user_id = '$admin_id'";
    }
    else
    {
        $sql = "UPDATE " .$aos->table('admin_user'). " SET ".
               "user_name = '$admin_name', ".
               "email = '$admin_email' ".
               $nav_list.
               "WHERE user_id = '$admin_id'";
    }

   $db->query($sql);
   /* 记录管理员操作 */
   admin_log($_POST['user_name'], 'edit', 'privilege');

   /* 如果修改了密码，则需要将session中该管理员的数据清空 */
   if ($pwd_modified && $operation == 'update_self')
   {
       $sess->delete_spec_admin_session($_SESSION['admin_id']);
       $msg = '您已经成功的修改了密码，因此您必须重新登录!';
   }
   else
   {
       $msg = '您已经成功的修改了个人帐号信息!';
   }

   /* 提示信息 */
   $link[] = array('text' => strpos($g_link, 'list') ? '返回管理员列表' : '编辑个人资料', 'href'=>$g_link);
   sys_msg("$msg<script>parent.document.getElementById('header-frame').contentWindow.document.location.reload();</script>", 0, $link);

}

/*------------------------------------------------------ */
//-- 编辑个人资料
/*------------------------------------------------------ */
elseif ($operation == 'modif')
{
    /* 不能编辑demo这个管理员 */
    if ($_SESSION['admin_name'] == 'demo')
    {
       $link[] = array('text' => "返回列表", 'href'=>'index.php?act=admin&op=admin_list');
       sys_msg("不能编辑demo这个管理员", 0, $link);
    }

    /* 获得当前管理员数据信息 */
    $sql = "SELECT user_id, user_name, email, nav_list ".
           "FROM " .$aos->table('admin_user'). " WHERE user_id = '".$_SESSION['admin_id']."'";
    $user_info = $db->getRow($sql);

   
    /* 模板赋值 */
    $smarty->assign('lang',        $_LANG);
    $smarty->assign('ur_here',     '编辑个人资料');
    $smarty->assign('action_link', array('text' => '管理员列表', 'href'=>'index.php?act=admin&op=admin_list'));
    $smarty->assign('user',        $user_info);
    $smarty->assign('form_act',    'update_self');
    $smarty->assign('action',      'modif');

    /* 显示页面 */
    
    $smarty->display('admin_info.htm');
}

/*------------------------------------------------------ */
//-- 为管理员分配权限
/*------------------------------------------------------ */
elseif ($operation == 'allot')
{
    include_once(ROOT_PATH . 'source/language/admin/priv_action.php');

    admin_priv('allot_priv');
    if ($_SESSION['admin_id'] == $_GET['id'])
    {
        admin_priv('all');
    }

    /* 获得该管理员的权限 */
    $priv_str = $db->getOne("SELECT action_list FROM " .$aos->table('admin_user'). " WHERE user_id = '$_GET[id]'");

    /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
    if ($priv_str == 'all')
    {
       $link[] = array('text' => "返回列表", 'href'=>'index.php?act=admin&op=list');
       sys_msg("不能编辑", 0, $link);
    }

    /* 获取权限的分组数据 */
    $sql_query = "SELECT action_id, parent_id, action_code,relevance FROM " .$aos->table('admin_action').
                 " WHERE parent_id = 0";
    $res = $db->query($sql_query);
    while ($rows = $db->FetchRow($res))
    {
        $priv_arr[$rows['action_id']] = $rows;
    }

    /* 按权限组查询底级的权限名称 */
    $sql = "SELECT action_id, parent_id, action_code,relevance FROM " .$aos->table('admin_action').
           " WHERE parent_id " .db_create_in(array_keys($priv_arr));
    $result = $db->query($sql);
    while ($priv = $db->FetchRow($result))
    {
        $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
    }

    // 将同一组的权限使用 "," 连接起来，供JS全选
    foreach ($priv_arr AS $action_id => $action_group)
    {
        $priv_arr[$action_id]['priv_list'] = join(',', @array_keys($action_group['priv']));

        foreach ($action_group['priv'] AS $key => $val)
        {
            $priv_arr[$action_id]['priv'][$key]['cando'] = (strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all') ? 1 : 0;
        }
    }

    /* 赋值 */
    $smarty->assign('lang',        $_LANG);
    $smarty->assign('ur_here',     '分派权限' . ' [ '. $_GET['user'] . ' ] ');
    $smarty->assign('action_link', array('href'=>'index.php?act=admin&op=admin_list', 'text' => '管理员列表'));
    $smarty->assign('priv_arr',    $priv_arr);
    $smarty->assign('form_act',    'update_allot');
    $smarty->assign('user_id',     $_GET['id']);

    /* 显示页面 */
    
    $smarty->display('admin_allot.htm');
}

/*------------------------------------------------------ */
//-- 更新管理员的权限
/*------------------------------------------------------ */
elseif ($operation == 'update_allot')
{
    
    if($_POST['token']!=$_CFG['token'])
    {
         sys_msg('update_allot_error', 1);
    }
    /* 取得当前管理员用户名 */
    $admin_name = $db->getOne("SELECT user_name FROM " .$aos->table('admin_user'). " WHERE user_id = '$_POST[id]'");

    /* 更新管理员的权限 */
    $act_list = @join(",", $_POST['action_code']);
    $sql = "UPDATE " .$aos->table('admin_user'). " SET action_list = '$act_list', role_id = '' ".
           "WHERE user_id = '$_POST[id]'";

    $db->query($sql);
    /* 动态更新管理员的SESSION */
    if ($_SESSION["admin_id"] == $_POST['id'])
    {
        $_SESSION["action_list"] = $act_list;
    }

    /* 记录管理员操作 */
    admin_log(addslashes($admin_name), 'edit', 'privilege');

    /* 提示信息 */
    $link[] = array('text' => "返回列表", 'href'=>'index.php?act=admin&op=admin_list');
    sys_msg("修改&nbsp;" . $admin_name . "&nbsp;" . "成功", 0, $link);

}

/*------------------------------------------------------ */
//-- 删除一个管理员
/*------------------------------------------------------ */
elseif ($operation == 'remove')
{
    $id = intval($_REQUEST['id']);

    /* 获得管理员用户名 */
    $admin_user_info = $db->getRow('SELECT user_name FROM '.$aos->table('admin_user')." WHERE user_id='$id'");

    $admin_name = $admin_user_info['user_name'];

    /* demo这个管理员不允许删除 */
    if ($admin_name == 'demo')
    {
        make_json_error('您不能删除demo这个管理员!');
    }

    /* ID为1的不允许删除 */
    if ($id == 1)
    {
        make_json_error('此管理员您不能进行删除操作!');
    }

    

    /* 管理员不能删除自己 */
    if ($id == $_SESSION['admin_id'])
    {
        make_json_error('您不能删除自己!');
    }

    if ($exc->drop($id))
    {
        $sess->delete_spec_admin_session($id); // 删除session中该管理员的记录

        admin_log(addslashes($admin_name), 'remove', 'privilege');
        clear_cache_files();
        make_json_result($id);
    }

    $url = 'index.php?act=admin&op=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    aos_header("Location: $url\n");
    exit;
}


/* 获取管理员列表 */
function get_admin_userlist()
{
    $list = array();
    $sql  = 'SELECT user_id, user_name, email, add_time, last_login '.
            'FROM ' .$GLOBALS['aos']->table('admin_user').' ORDER BY user_id DESC';
    $list = $GLOBALS['db']->getAll($sql);

    foreach ($list AS $key=>$val)
    {
        $list[$key]['add_time']     = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
        $list[$key]['last_login']   = local_date($GLOBALS['_CFG']['time_format'], $val['last_login']);
    }

    return $list;
}


?>
