<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

error_reporting(E_ALL);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}

/* 取得当前aoshop所在的根目录 */
define('ROOT_PATH', str_replace('api', '', str_replace('\\', '/', dirname(__FILE__))));

/* 初始化设置 */
@ini_set('memory_limit',          '16M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        1);

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path', '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path', '.:' . ROOT_PATH);
}

if (file_exists(ROOT_PATH . 'data/config.php'))
{
    include(ROOT_PATH . 'data/config.php');
}
else
{
    include(ROOT_PATH . 'includes/config.php');
}

if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

if (PHP_VERSION >= '5.1' && !empty($timezone))
{
    date_default_timezone_set($timezone);
}

$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF', $php_self);

//require(ROOT_PATH . 'source/constant.php');
require(ROOT_PATH . 'source/class/aoshop.class.php');
require(ROOT_PATH . 'source/library/base.php');
require(ROOT_PATH . 'source/library/common.php');

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
$data_dir = $aos->data_dir();

/* 初始化数据库类 */
require(ROOT_PATH . 'source/class/mysql.class.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 初始化session */
require(ROOT_PATH . 'source/class/session.class.php');
$sess_name  = defined("SESS_NAME") ? SESS_NAME : 'AOS_ID';
$sess       = new cls_session($db, $aos->table('sessions'), $aos->table('sessions_data'), $sess_name);

/* 载入系统参数 */
$_CFG = load_config();

/* 初始化用户插件 */
//$user = init_users();

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

/* 判断是否支持 Gzip 模式 */
if (gzip_enabled())
{
    ob_start('ob_gzhandler');
}

header('Content-type: text/html; charset=' . AO_CHARSET);

?>