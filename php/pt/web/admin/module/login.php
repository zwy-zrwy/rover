<?php

define('IN_AOS', true);


/*------------------------------------------------------ */
//-- 退出登录
/*------------------------------------------------------ */
if ($operation == 'logout')
{
    /* 清除cookie */
    setcookie('AOSCP[admin_id]',   '', 1);
    setcookie('AOSCP[admin_pass]', '', 1);

    $sess->destroy_session();
    $url = "index.php?act=login&op=login";
    echo "<script>window.top.location.replace('".$url."');</script>"; 
}



/*------------------------------------------------------ */
//-- 登陆界面
/*------------------------------------------------------ */
if ($operation == 'login')
{
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");

        if (gd_version() > 0){
            $smarty->assign('gd_version', gd_version());
            $smarty->assign('random',     mt_rand());
        }
        if (isset($_SESSION['login_err']) && $_SESSION['login_err']) {
            $smarty->assign('login_err',$_SESSION['login_err']);
            unset($_SESSION['login_err']);
        }
        $smarty->assign('now_year',date('Y'));
        $smarty->display('login.htm');
}

/*------------------------------------------------------ */
//-- 验证登陆信息
/*------------------------------------------------------ */
elseif ($operation == 'signin')
{
    include_once(ROOT_PATH . 'source/class/captcha.class.php');

    /* 检查验证码是否正确 */
    $validator = new captcha();
    if (!empty($_POST['captcha']) && !$validator->check_word($_POST['captcha']))
    {
        sys_msg('您输入的验证码不正确。', 1);
    }


    $_POST['username'] = isset($_POST['username']) ? trim($_POST['username']) : '';
    $_POST['password'] = isset($_POST['password']) ? trim($_POST['password']) : '';

    $sql="SELECT `ec_salt` FROM ". $aos->table('admin_user') ."WHERE user_name = '" . $_POST['username']."'";
    $ao_salt =$db->getOne($sql);
    if(!empty($ao_salt))
    {
         /* 检查密码是否正确 */
         $sql = "SELECT user_id, user_name, password, last_login, action_list, last_login,ec_salt".
            " FROM " . $aos->table('admin_user') .
            " WHERE user_name = '" . $_POST['username']. "' AND password = '" . md5(md5($_POST['password']).$ao_salt) . "'";
    }
    else
    {
         /* 检查密码是否正确 */
         $sql = "SELECT user_id, user_name, password, last_login, action_list, last_login,ec_salt".
            " FROM " . $aos->table('admin_user') .
            " WHERE user_name = '" . $_POST['username']. "' AND password = '" . md5($_POST['password']) . "'";
    }
    $row = $db->getRow($sql);

    if ($row)
    {
        // 登录成功
        set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_login']);
		if(empty($row['ec_salt']))
	    {
			$ao_salt=rand(1,9999);
			$new_possword=md5(md5($_POST['password']).$ao_salt);
             $db->query("UPDATE " .$aos->table('admin_user').
                 " SET ec_salt='" . $ao_salt . "', password='" .$new_possword . "'".
                 " WHERE user_id='$_SESSION[admin_id]'");
		}

        if($row['action_list'] == 'all' && empty($row['last_login']))
        {
            $_SESSION['shop_guide'] = true;
        }

        // 更新最后登录时间和IP
        $db->query("UPDATE " .$aos->table('admin_user').
                 " SET last_login='" . gmtime() . "', last_ip='" . real_ip() . "'".
                 " WHERE user_id='$_SESSION[admin_id]'");

        if (isset($_POST['remember']))
        {
            $time = gmtime() + 3600 * 24 * 365;
            setcookie('AOSCP[admin_id]',   $row['user_id'],                            $time);
            setcookie('AOSCP[admin_pass]', md5($row['password'] . $_CFG['hash_code']), $time);
        }

        // 清除购物车中过期的数据
        clear_cart();

        aos_header("Location: ./index.php\n");

        exit;
    }
    else
    {
        sys_msg('您输入的帐号信息不正确。', 1);
    }
}

/* 清除购物车中过期的数据 */
function clear_cart()
{
    /* 取得有效的session */
    $sql = "SELECT DISTINCT session_id " .
            "FROM " . $GLOBALS['aos']->table('cart') . " AS c, " .
                $GLOBALS['aos']->table('sessions') . " AS s " .
            "WHERE c.session_id = s.sesskey ";
    $valid_sess = $GLOBALS['db']->getCol($sql);

    // 删除cart中无效的数据
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('cart') .
            " WHERE session_id NOT " . db_create_in($valid_sess);
    $GLOBALS['db']->query($sql);
}
?>
