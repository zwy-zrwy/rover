<?php
/**
 * AOSHOP 前台公用文件
*/
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
define('ROOT_PATH', str_replace('source/aoshop.php', '', str_replace('\\', '/', __FILE__)));
define('RLPT_PATH', realpath(dirname(__FILE__).'/../../'));

/* 初始化设置 */
@ini_set('memory_limit',          '256M');
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

require(ROOT_PATH . 'data/config.php');

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
require(ROOT_PATH . 'source/class/wechat.class.php');
require(ROOT_PATH . 'source/class/error.class.php');
require(ROOT_PATH . 'source/class/sms.class.php');
require(ROOT_PATH . 'source/library/time.php');
require(ROOT_PATH . 'source/library/base.php');
require(ROOT_PATH . 'source/library/common.php');
require(ROOT_PATH . 'source/library/version.php');
require(ROOT_PATH . 'source/library/main.php');
require(ROOT_PATH . 'source/library/insert.php');
require(ROOT_PATH . 'source/library/goods.php');
/*
if(!is_mobile() && $controller != 'pc' && $module != 'app')
{
    header("Location:index.php?c=pc");exit;
}
*/




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
define('AOS_HTTP', $aos->http());

/* 初始化数据库类 */
require(ROOT_PATH . 'source/class/mysql.class.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db->set_disable_cache_tables(array($aos->table('sessions'), $aos->table('sessions_data'), $aos->table('cart')));
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new aos_error('message.htm');

/* 载入系统参数 */
$_CFG = load_config();

/* 载入语言文件 */
require(ROOT_PATH . 'source/language/common.php');

if ($_CFG['shop_closed'] == 1 && $controller != 'closed')
{
    header("Location:index.php?c=closed");exit;

    //die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_CFG['close_comment'] . '</p></div>');
}


if (!defined('INIT_NO_USERS'))
{
    /* 初始化session */
    include(ROOT_PATH . 'source/class/session.class.php');

    $sess = new cls_session($db, $aos->table('sessions'), $aos->table('sessions_data'));
    
    //判断是否存在user_id的session    
    if(isset($_SESSION['user_id'])){    
        //如果存在会员登录    
        if($_SESSION['user_id']>0){    
            //取得对应user_id的session MD5码 
            $user_session=md5($_SESSION['user_id'].'aos');  
            //取得之前的session_id   
            $old_session=$sess->get_session_id();    
            define('OLD_SESS_ID',$old_session);    
            define('SESS_ID',$user_session);    
        }else{    
            //不存在会员，继续用原有的session_id    
            define('SESS_ID', $sess->get_session_id());    
        }    
    }else{    
        //不存在会员，继续用原有的session_id    
        define('SESS_ID', $sess->get_session_id());    
    }
}
if(isset($_SERVER['PHP_SELF']))
{
    $_SERVER['PHP_SELF']=htmlspecialchars($_SERVER['PHP_SELF']);
}
if (!defined('INIT_NO_SMARTY'))
{
    header('Cache-control: private');
    header('Content-type: text/html; charset='.AO_CHARSET);

    /* 创建 Smarty 对象。*/
    require(ROOT_PATH . 'source/class/template.class.php');
    $smarty = new cls_template;
    $smarty->cache_lifetime = $_CFG['cache_time'];
    
    $smarty->template_dir   = ROOT_PATH . 'template/' . $_CFG['template'];
    $smarty->cache_dir      = ROOT_PATH . 'temp/caches';
    $smarty->compile_dir    = ROOT_PATH . 'temp/compiled';

    if ((DEBUG_MODE & 2) == 2)
    {
        $smarty->direct_output = true;
        $smarty->force_compile = true;
    }
    else
    {
        $smarty->direct_output = false;
        $smarty->force_compile = false;
    }

    $smarty->assign('lang', $_LANG);
    $smarty->assign('aos_charset', AO_CHARSET);
    $smarty->assign('aos_version',  VERSION);
    $smarty->assign('http', AOS_HTTP);
    $smarty->assign('template_path', 'template/' . $_CFG['template'] . '/');
}

if (!defined('INIT_NO_USERS'))
{

    $user_id  = isset($_SESSION['user_id']) ? intval($_SESSION['user_id'])  : 0;
    $smarty->assign('user_id', $user_id);

    if (isset($smarty))
    {
        $smarty->assign('aos_session', $_SESSION);
    }
}

if ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); 
}
if ((DEBUG_MODE & 4) == 4)
{
    include(ROOT_PATH . 'source/library/debug.php');
}

/* 判断是否支持 Gzip 模式 */
if (!defined('INIT_NO_SMARTY') && gzip_enabled())
{
    ob_start('ob_gzhandler');
}
else
{
    ob_start();
}

$parent_id = isset($_REQUEST['u']) ? intval($_REQUEST['u'])  : 0;
if(isset($_REQUEST['user'])  ? intval($_REQUEST['user']) : 0)
{
    $_SESSION['user_id'] = $_REQUEST['user'];
    $_SESSION['openid'] = getOpenid($_REQUEST['user']);
}
$aos_url=$aos->url();
$cur_url = AOS_HTTP.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$wxconfig = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_config') . " WHERE `id` = 1" );
$appid = $wxconfig['appid'];   
$gmtime = gmtime();
$smarty->assign('appid', $appid);
$smarty->assign('timestamp', $gmtime);
$wechat = new wechat($wxconfig);
$signature = $wechat->signature($timestamp,$cur_url);
$smarty->assign('signature', $signature);
//$wechat->text($msg);

//$_SESSION=array();
if(empty($_SESSION['openid']) && empty($_SESSION['user_id']) && !isset($is_wap) && $controller != 'closed')
//if($a=1)
{
  if($_GET['code']){
    $json = $wechat->getOauthAccessToken();
    //print_r($json);die;
    if($json['openid']){
      $user = $GLOBALS['db']->getRow("SELECT user_id,unionid,headimgurl FROM " . $GLOBALS['aos']->table('users') . " WHERE openid='$json[openid]'");
      $user_id =$user['user_id'];
      if($user_id)
      {
        if(empty(trim($user['unionid']))){
            $info = $wechat->getOauthUserinfo($json['access_token'],$json['openid']);
        
            if(empty($info))
            {
              $url = $wechat->getOauthRedirect($cur_url,'wxbase');
              header("Location:$url");exit;
            }
            $sql = "UPDATE ".$aos->table('users')." set unionid = '$info[unionid]' where user_id = '$user_id'";
            $db->query($sql);
        }

        $avatar_file = ROOT_PATH . 'uploads/avatar/avatar_'.$user_id.'.jpg';
        if (is_readable($avatar_file) == false) { 
          //保存图像到本地
          $headimgurl    = $wechat->http_get($user['headimgurl']);
          $path   = ROOT_PATH . 'uploads/avatar/avatar_'.$user_id.'.jpg';
          @file_put_contents($path,$headimgurl);
        }
        $_SESSION['openid'] = $json['openid'];
        set_session($user_id);
        set_cookie($user_id);
        update_user_info();  //更新用户信息
        $pos = strpos($cur_url, 'code');
        $str = $cur_url[$pos-1];
        if($str == '&')
        {
            $url = substr($cur_url,0,strrpos($cur_url,'&code')); 
        }
        elseif($str == '?')
        {
            $url = substr($cur_url,0,strrpos($cur_url,'?code')); 
        }
        header("Location:$url");exit;
      }
      else
      {
        $info = $wechat->getOauthUserinfo($json['access_token'],$json['openid']);
        
        if(empty($info))
        {
          $url = $wechat->getOauthRedirect($cur_url,'wxbase');
          header("Location:$url");exit;
        }
        if($info[headimgurl])
        {
          //保存图像到本地
          $avatar    = $wechat->http_get($info[headimgurl]);
          $path   = ROOT_PATH . 'uploads/avatar/avatar_'.$user_id.'.jpg';
          @file_put_contents($path,$avatar);
        }
        $info['nickname'] = replaceSpecialChar($info[nickname]);
        
        $time = gmtime();
        if(!empty($info[unionid])){
            $sql="select user_id from ".$aos->table('users')." where unionid = '$info[unionid]'";
            $id = $db->getOne($sql);
            if($id){
                $sql = "UPDATE ".$aos->table('users')." set openid = '$info[openid]' where user_id = '$id'";
                $res=$db->query($sql);
            }else{
                $sql = "INSERT INTO " . $aos->table('users') . " (openid, nickname, sex, headimgurl, country, province, city, reg_time, subscribe,unionid,parent_id) VALUES ('$info[openid]', '$info[nickname]', '$info[sex]', '$info[headimgurl]', '$info[country]', '$info[province]', '$info[city]', '$time', '$info[subscribe]','$info[unionid]','$parent_id')";
                $res=$db->query($sql);
                $id = $db->insert_id();
            }
        }else{
            $sql = "INSERT INTO " . $aos->table('users') . " (openid, nickname, sex, headimgurl, country, province, city, reg_time, subscribe,parent_id) VALUES ('$info[openid]', '$info[nickname]', '$info[sex]', '$info[headimgurl]', '$info[country]', '$info[province]', '$info[city]', '$time', '$info[subscribe]','$parent_id')";
                $res=$db->query($sql);
                $id = $db->insert_id();
        }
        
        if($res){
           $sql="select type_id,type_money,use_start_date,use_end_date from ".$aos->table('bonus_type')." where send_type = 4 and send_start_date < $time and send_end_date > $time";
            $bonus_list=$db->getAll($sql);
            if(!empty($bonus_list)){
                foreach($bonus_list as $vo){
                    $sql="insert into ".$aos->table('user_bonus')." (bonus_type_id,user_id) values ('$vo[type_id]','$id')";
                    $db->query($sql);
                    $openid=getOpenid($id);
                    $use_time=local_date("m月d日", $vo['use_start_date']).'-'.local_date("m月d日", $vo['use_end_date']);
                    $wx_title = "获得优惠劵通知";
                    $wx_desc = "恭喜您获得优惠劵\r\n优惠劵金额：".$vo['type_money']."元\r\n有效期：".$use_time;
                    
                    $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                }
            } 
        }
        
        header("Location:$cur_url");exit;
      }
    }
    $pos = strpos($cur_url, 'code');
    $str = $cur_url[$pos-1];
    if($str == '&')
    {
      $url = substr($cur_url,0,strrpos($cur_url,'&code')); 
    }
    elseif($str == '?')
    {
      $url = substr($cur_url,0,strrpos($cur_url,'?code')); 
    }
    header("Location:$url");exit;
  }
  else
  {
    $url = $wechat->getOauthRedirect($cur_url,'wxbase','snsapi_base');
    header("Location:$url");exit;
  }
}

?>