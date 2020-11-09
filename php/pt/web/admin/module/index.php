<?php

define('IN_AOS', true);
require_once(ROOT_PATH . '/source/library/order.php');
if ($operation == 'index')
{


	    $gd = gd_version();

    /* 检查文件目录属性 */
    $warning = array();

    if ($_CFG['shop_closed'])
    {
        $warning[] = '您的商店已被暂时关闭。在设置好您的商店之后别忘记打开哦！';
    }

    if (file_exists('../install'))
    {
        $warning[] = '您还没有删除 install 文件夹，出于安全的考虑，我们建议您删除 install 文件夹。';
    }
    

    $open_basedir = ini_get('open_basedir');
    if (!empty($open_basedir))
    {
        /* 如果 open_basedir 不为空，则检查是否包含了 upload_tmp_dir  */
        $open_basedir = str_replace(array("\\", "\\\\"), array("/", "/"), $open_basedir);
        $upload_tmp_dir = ini_get('upload_tmp_dir');

        if (empty($upload_tmp_dir))
        {
            if (stristr(PHP_OS, 'win'))
            {
                $upload_tmp_dir = getenv('TEMP') ? getenv('TEMP') : getenv('TMP');
                $upload_tmp_dir = str_replace(array("\\", "\\\\"), array("/", "/"), $upload_tmp_dir);
            }
            else
            {
                $upload_tmp_dir = getenv('TMPDIR') === false ? '/tmp' : getenv('TMPDIR');
            }
        }

        if (!stristr($open_basedir, $upload_tmp_dir))
        {
            $warning[] = sprintf('您的服务器设置了 open_base_dir 且没有包含 %s，您将无法上传文件。', $upload_tmp_dir);
        }
    }

    $result = file_mode_info('../' . DATA_DIR);
    if ($result < 2)
    {
        $warning[] = sprintf('%s 目录不可写入，%s', 'data', '您将无法上传图片文件。');
    }
    else
    {
        $result = file_mode_info('../' . DATA_DIR . '/ads_img');
        if ($result < 2)
        {
            $warning[] = sprintf('%s 目录不可写入，%s', DATA_DIR . '/ads_img', '您将无法上传广告的图片文件。');
        }

    }

    $result = file_mode_info('../images');
    if ($result < 2)
    {
        $warning[] = sprintf('%s 目录不可写入，%s', 'images', '您将无法上传任何商品图片。');
    }
    else
    {
        $result = file_mode_info('../' . IMAGE_DIR . '/upload');
        if ($result < 2)
        {
            $warning[] = sprintf('%s 目录不可写入，%s', IMAGE_DIR . '/upload', '您将无法通过编辑器上传任何图片。');
        }
    }

    $result = file_mode_info('../temp');
    if ($result < 2)
    {
        $warning[] = sprintf('%s 目录不可写入，%s', 'images', '您的网站将无法浏览。');
    }

    $result = file_mode_info('../temp/backup');
    if ($result < 2)
    {
        $warning[] = sprintf('%s 目录不可写入，%s', 'images', '您就无法备份当前的模版文件。');
    }

    if (!is_writeable('../data/order_print.html'))
    {
        $warning[] = 'data目录下的order_print.html文件属性为不可写，您将无法修改订单打印模板。';
    }
    clearstatcache();

    $smarty->assign('warning_arr', $warning);
    

    /* 已完成的订单 */
    $order['finished']     = $db->GetOne('SELECT COUNT(*) FROM ' . $aos->table('order_info').
    " WHERE 1 " . order_query_sql('finished'));
    $status['finished']    = CS_FINISHED;

    /* 待发货的订单： */
    $order['await_ship']   = $db->GetOne('SELECT COUNT(*)'.
    ' FROM ' .$aos->table('order_info') .
    " WHERE 1 " . order_query_sql('await_ship'));
    $status['await_ship']  = 3;
    
    /* 待付款的订单： */
    $order['await_pay']    = $db->GetOne('SELECT COUNT(*)'.
    ' FROM ' .$aos->table('order_info') .
    " WHERE 1 " . order_query_sql('await_pay'));
    $status['await_pay']   = 1;

    /* “未确认”的订单 */
    $order['unconfirmed']  = $db->GetOne('SELECT COUNT(*) FROM ' .$aos->table('order_info').
    " WHERE 1 " . order_query_sql('unconfirmed'));
    $status['unconfirmed'] = 0;

    /* “部分发货”的订单 */
    $order['shipped_part']  = $db->GetOne('SELECT COUNT(*) FROM ' .$aos->table('order_info').
    " WHERE  shipping_status=4" );
    $status['shipped_part'] = OS_SHIPPED_PART;

//    $today_start = mktime(0,0,0,date('m'),date('d'),date('Y'));
    $order['stats']        = $db->getRow('SELECT COUNT(*) AS oCount, IFNULL(SUM(order_amount), 0) AS oAmount' .
    ' FROM ' .$aos->table('order_info'));

    $smarty->assign('order', $order);
    $smarty->assign('status', $status);

    /* 商品信息 */
    $goods['total']   = $db->GetOne('SELECT COUNT(*) FROM ' .$aos->table('goods').
    ' WHERE is_delete = 0');

    $goods['new']     = $db->GetOne('SELECT COUNT(*) FROM ' .$aos->table('goods').
    ' WHERE is_delete = 0 AND is_new = 1');

    $goods['best']    = $db->GetOne('SELECT COUNT(*) FROM ' .$aos->table('goods').
    ' WHERE is_delete = 0 AND is_best = 1');

    $goods['hot']     = $db->GetOne('SELECT COUNT(*) FROM ' .$aos->table('goods').
    ' WHERE is_delete = 0 AND is_hot = 1');


    /* 缺货商品 */
    if ($_CFG['use_storage'])
    {
        $sql = 'SELECT COUNT(*) FROM ' .$aos->table('goods'). ' WHERE is_delete = 0 AND goods_number <= warn_number';
        $goods['warn'] = $db->GetOne($sql);
    }
    else
    {
        $goods['warn'] = 0;
    }
    $smarty->assign('goods', $goods);


    /* 未审核评论 */
    $smarty->assign('comment_number', $db->getOne('SELECT COUNT(*) FROM ' . $aos->table('comment') .
    ' WHERE status = 0 AND parent_id = 0'));

    $mysql_ver = $db->version();   // 获得 MySQL 版本

    /* 系统信息 */
    $sys_info['os']            = PHP_OS;
    $sys_info['ip']            = $_SERVER['SERVER_ADDR'];
    $sys_info['web_server']    = $_SERVER['SERVER_SOFTWARE'];
    $sys_info['php_ver']       = PHP_VERSION;
    $sys_info['mysql_ver']     = $mysql_ver;
    $sys_info['zlib']          = function_exists('gzclose') ? '是':'否';
    $sys_info['safe_mode']     = (boolean) ini_get('safe_mode') ?  '是':'否';
    $sys_info['safe_mode_gid'] = (boolean) ini_get('safe_mode_gid') ? '是' : '否';
    $sys_info['timezone']      = function_exists("date_default_timezone_get") ? date_default_timezone_get() : '无需设置';
    $sys_info['socket']        = function_exists('fsockopen') ? '是' : '否';

    if ($gd == 0)
    {
        $sys_info['gd'] = 'N/A';
    }
    else
    {
        if ($gd == 1)
        {
            $sys_info['gd'] = 'GD1';
        }
        else
        {
            $sys_info['gd'] = 'GD2';
        }

        $sys_info['gd'] .= ' (';

        /* 检查系统支持的图片类型 */
        if ($gd && (imagetypes() & IMG_JPG) > 0)
        {
            $sys_info['gd'] .= ' JPEG';
        }

        if ($gd && (imagetypes() & IMG_GIF) > 0)
        {
            $sys_info['gd'] .= ' GIF';
        }

        if ($gd && (imagetypes() & IMG_PNG) > 0)
        {
            $sys_info['gd'] .= ' PNG';
        }

        $sys_info['gd'] .= ')';
    }


    /* 允许上传的最大文件大小 */
    $sys_info['max_filesize'] = ini_get('upload_max_filesize');

    $smarty->assign('sys_info', $sys_info);


    /* 退款申请 */
    $smarty->assign('new_repay', $db->getOne('SELECT COUNT(*) FROM ' . $aos->table('user_account') . ' WHERE process_type = ' . 1 . ' AND is_paid = 0 '));

    
    $smarty->assign('aos_appname',  APPNAME);
    $smarty->assign('aos_version',  VERSION);
    $smarty->assign('aos_release',  RELEASE);
    $smarty->assign('aos_lang',     $_CFG['lang']);
    $smarty->assign('aos_charset',  strtoupper(AO_CHARSET));
    $smarty->assign('install_date', local_date($_CFG['date_format'], $_CFG['install_date']));
    $smarty->assign('pmp_desktop',PMP_DESKTOP);
    $smarty->assign('pmp_market',PMS_MARKET);
	
	
    $smarty->assign('shop_url', $aos->url());
	$smarty->assign('admin_url', $aos->url() . 'admin');
	$smarty->assign('now_year',date('Y'));
    $smarty->assign('now_time',gmtime());
    $smarty->assign('shop_domain', $_CFG['shop_domain']);
    $smarty->assign('directory', $_CFG['directory']);
    $smarty->display('index.htm');
}
//清除缓存
elseif ($operation == 'clear_cache')
{
    $res = clear_all_files();
    die(json_encode($res));
}

?>
