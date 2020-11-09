<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

define('AOS_ADMIN', true);

error_reporting(E_ALL);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}

/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        1);

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path',      '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path',      '.:' . ROOT_PATH);
}

if (file_exists('../data/config.php'))
{
    include('../data/config.php');
}

/* 取得当前aoshop所在的根目录 */
if(!defined('ADMIN_PATH'))
{
    define('ADMIN_PATH','admin');
}

define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/aoshop.php', '', str_replace('\\', '/', __FILE__)));
define('RLPT_PATH', realpath(dirname(__FILE__).'/../../'));
if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

if (!empty($timezone))
{
    date_default_timezone_set($timezone);
}

if (isset($_SERVER['PHP_SELF']))
{
    define('PHP_SELF', $_SERVER['PHP_SELF']);
}
else
{
    define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}

require(ROOT_PATH . 'source/class/aoshop.class.php');
require(ROOT_PATH . 'source/class/error.class.php');
require(ROOT_PATH . 'source/class/wechat.class.php');
require(ROOT_PATH . 'source/library/time.php');
require(ROOT_PATH . 'source/library/base.php');
require(ROOT_PATH . 'source/library/common.php');
require(ROOT_PATH . 'source/library/version.php');
require(ROOT_PATH . ADMIN_PATH . '/includes/lib_main.php');
require(ROOT_PATH . ADMIN_PATH . '/includes/cls_exchange.php');
include(ROOT_PATH . ADMIN_PATH . '/includes/inc_menu.php');
include(ROOT_PATH . ADMIN_PATH . '/includes/inc_priv.php');

/* 对用户传入的变量进行转义操作。*/
if (!get_magic_quotes_gpc())
{
    if (!empty($_GET))
    {
        $_GET  = addslashes_deep($_GET);
    }
    if (!empty($_POST))
    {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE   = addslashes_deep($_COOKIE);
    $_REQUEST  = addslashes_deep($_REQUEST);
}

/* 创建 AOSHOP 对象 */
$aos = new AOS($db_name, $prefix);
define('DATA_DIR', $aos->data_dir());
define('IMAGE_DIR', $aos->image_dir());

/* 初始化数据库类 */
require(ROOT_PATH . 'source/class/mysql.class.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new aos_error('message.htm');

/* 初始化session */
require(ROOT_PATH . 'source/class/session.class.php');
$sess = new cls_session($db, $aos->table('sessions'), $aos->table('sessions_data'), 'AOSCP_ID');

/* 初始化 action */

$action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'index';
$operation = isset($_REQUEST['op']) ? trim($_REQUEST['op']) : 'index';

/* 载入系统参数 */
$_CFG = load_config();
// TODO : 登录部分准备拿出去做，到时候把以下操作一起挪过去
if ($action == 'captcha')
{
    include(ROOT_PATH . 'source/class/captcha.class.php');

    $img = new captcha('../data/captcha/',90,26);
    @ob_end_clean(); //清除之前出现的多余输入
    $img->generate_image();

    exit;
}
require(ROOT_PATH . 'source/language/admin/common.php');

if (!file_exists('../temp/caches'))
{
    @mkdir('../temp/caches', 0777);
    @chmod('../temp/caches', 0777);
}

if (!file_exists('../temp/compiled/admin'))
{
    @mkdir('../temp/compiled/admin', 0777);
    @chmod('../temp/compiled/admin', 0777);
}
if (!file_exists('../temp/compiled/mobile'))
{
    @mkdir('../temp/compiled/mobile', 0777);
    @chmod('../temp/compiled/mobile', 0777);
}

clearstatcache();
$wxconfig = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_config') . " WHERE `id` = 1" );
$admin_wechat = new wechat($wxconfig);
/* 创建 Smarty 对象。*/
require(ROOT_PATH . 'source/class/template.class.php');
$smarty = new cls_template;

$smarty->assign('action',$action);
$smarty->assign('operation',$operation);
$smarty->assign('aos_version',  VERSION);
if(is_mobile())
{
    $smarty->template_dir  = ROOT_PATH . ADMIN_PATH . '/mobile/templates';
    $smarty->compile_dir   = ROOT_PATH . 'temp/compiled/mobile';
}
else
{
    $smarty->template_dir  = ROOT_PATH . ADMIN_PATH . '/templates';
    $smarty->compile_dir   = ROOT_PATH . 'temp/compiled/admin';
}


if ((DEBUG_MODE & 2) == 2)
{
    $smarty->force_compile = true;
}


$smarty->assign('lang', $_LANG);
$smarty->assign('shop_name',    $GLOBALS['_CFG']['shop_name']);

/* 验证管理员身份 */
if ((!isset($_SESSION['admin_id']) || intval($_SESSION['admin_id']) <= 0) &&
    $_REQUEST['op'] != 'login' && $_REQUEST['op'] != 'signin' &&
    $_REQUEST['op'] != 'forget_pwd' && $_REQUEST['op'] != 'reset_pwd')
{
    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['AOSCP']['admin_id']) && !empty($_COOKIE['AOSCP']['admin_pass']))
    {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password, action_list, last_login ' .
                ' FROM ' .$aos->table('admin_user') .
                " WHERE user_id = '" . intval($_COOKIE['AOSCP']['admin_id']) . "'";
        $row = $db->GetRow($sql);

        if (!$row)
        {
            // 没有找到这个记录
            setcookie($_COOKIE['AOSCP']['admin_id'],   '', 1);
            setcookie($_COOKIE['AOSCP']['admin_pass'], '', 1);

            if (!empty($_REQUEST['is_ajax']))
            {
                make_json_error('对不起,您没有执行此项操作的权限!');
            }
            else
            {
                aos_header("Location: index.php?act=login&op=login\n");
            }

            exit;
        }
        else
        {
            // 检查密码是否正确
            if (md5($row['password'] . $_CFG['hash_code']) == $_COOKIE['AOSCP']['admin_pass'])
            {
                !isset($row['last_time']) && $row['last_time'] = '';
                set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_time']);

                // 更新最后登录时间和IP
                $db->query('UPDATE ' . $aos->table('admin_user') .
                            " SET last_login = '" . gmtime() . "', last_ip = '" . real_ip() . "'" .
                            " WHERE user_id = '" . $_SESSION['admin_id'] . "'");
            }
            else
            {
                setcookie($_COOKIE['AOSCP']['admin_id'],   '', 1);
                setcookie($_COOKIE['AOSCP']['admin_pass'], '', 1);

                if (!empty($_REQUEST['is_ajax']))
                {
                    make_json_error('对不起,您没有执行此项操作的权限!');
                }
                else
                {
                    aos_header("Location: index.php?act=login&op=login\n");
                }

                exit;
            }
        }
    }
    else
    {
        if (!empty($_REQUEST['is_ajax']))
        {
            make_json_error('对不起,您没有执行此项操作的权限!');
        }
        else
        {
            aos_header("Location: index.php?act=login&op=login\n");
        }

        exit;
    }
}

if ($operation != 'login' && $operation != 'signin' &&
    $operation != 'forget_pwd' && $operation != 'reset_pwd')
{
    $admin_path = preg_replace('/:\d+/', '', $aos->url()) . ADMIN_PATH;
    if (!empty($_SERVER['HTTP_REFERER']) &&
        strpos(preg_replace('/:\d+/', '', $_SERVER['HTTP_REFERER']), $admin_path) === false)
    {
        if (!empty($_REQUEST['is_ajax']))
        {
            make_json_error('对不起,您没有执行此项操作的权限!');
        }
        else
        {
            aos_header("Location: index.php?act=login&op=login\n");
        }

        exit;
    }
}

//header('Cache-control: private');
header('content-type: text/html; charset=' . AO_CHARSET);
header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

if ((DEBUG_MODE & 1) == 1)
{
  error_reporting(E_ALL);
}
else
{
  error_reporting(E_ALL ^ E_NOTICE);
}
if ((DEBUG_MODE & 4) == 4)
{
  include(ROOT_PATH . 'source/library/debug.php');
}

/* 判断是否支持gzip模式 */
if (gzip_enabled())
{
  ob_start('ob_gzhandler');
}
else
{
  ob_start();
}



foreach ($menus AS $key => $val)
{
  $menus[$key]['label'] = $_LANG[$key];
  if (is_array($val))
  {
    foreach ($val AS $k => $v)
    {
      
      
      if (!admin_priv($purview[$k], '', false))
      //if(!in_array($purview[$k],explode(",",$_SESSION['action_list'])))
      {
        continue;
      }
      $menus[$key]['children'][$k]['key']  = $k;
      $menus[$key]['children'][$k]['label']  = $_LANG[$k];
      $menus[$key]['children'][$k]['action'] = $v;
      
    }
  }
  else
  {
    $menus[$key]['action'] = $val;
  }

  // 如果children的子元素长度为0则删除该组
  if(empty($menus[$key]['children']))
  {
    unset($menus[$key]);
  }
}
	
//print_r($menus);
$smarty->assign('menus',     $menus);
$smarty->assign('admin_id', $_SESSION['admin_id']);
$smarty->assign('admin_name', $_SESSION['admin_name']);

?>
