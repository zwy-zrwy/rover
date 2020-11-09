<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/**
 * 获得所有模块的名称以及链接地址
 *
 * @access      public
 * @param       string      $directory      插件存放的目录
 * @return      array
 */
function read_modules($directory = '.')
{
    global $_LANG;

    $dir         = @opendir($directory);
    $set_modules = true;
    $modules     = array();

    while (false !== ($file = @readdir($dir)))
    {
        if (preg_match("/^.*?\.php$/", $file))
        {
            include_once($directory. '/' .$file);
        }
    }
    @closedir($dir);
    unset($set_modules);

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }
    ksort($modules);

    return $modules;
}

/**
 * 系统提示信息
 *
 * @access      public
 * @param       string      msg_detail      消息内容
 * @param       int         msg_type        消息类型， 0消息，1错误，2询问
 * @param       array       links           可选的链接
 * @param       boolen      $auto_redirect  是否需要自动跳转
 * @return      void
 */
function sys_msg($msg_detail, $msg_type = 0, $links = array(), $auto_redirect = true)
{
    if (count($links) == 0)
    {
        $links[0]['text'] = '返回';
        $links[0]['href'] = 'javascript:history.go(-1)';
    }


    $GLOBALS['smarty']->assign('ur_here',     '系统信息');
    $GLOBALS['smarty']->assign('msg_detail',  $msg_detail);
    $GLOBALS['smarty']->assign('msg_type',    $msg_type);
    $GLOBALS['smarty']->assign('links',       $links);
    $GLOBALS['smarty']->assign('default_url', $links[0]['href']);
    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);

    $GLOBALS['smarty']->display('message.htm');

    exit;
}

/**
 * 记录管理员的操作内容
 *
 * @access  public
 * @param   string      $sn         数据的唯一值
 * @param   string      $action     操作的类型
 * @param   string      $content    操作的内容
 * @return  void
 */
function admin_log($sn = '', $action, $content)
{
    $log_info = $GLOBALS['_LANG']['log_action'][$action] . $GLOBALS['_LANG']['log_action'][$content] .': '. addslashes($sn);

    $sql = 'INSERT INTO ' . $GLOBALS['aos']->table('admin_log') . ' (log_time, user_id, log_info, ip_address) ' .
            " VALUES ('" . gmtime() . "', $_SESSION[admin_id], '" . stripslashes($log_info) . "', '" . real_ip() . "')";
    $GLOBALS['db']->query($sql);
}

/**
 * 将通过表单提交过来的年月日变量合成为"2004-05-10"的格式。
 *
 * 此函数适用于通过smarty函数html_select_date生成的下拉日期。
 *
 * @param  string $prefix      年月日变量的共同的前缀。
 * @return date                日期变量。
 */
function sys_joindate($prefix)
{
    /* 返回年-月-日的日期格式 */
    $year  = empty($_POST[$prefix . 'Year']) ? '0' :  $_POST[$prefix . 'Year'];
    $month = empty($_POST[$prefix . 'Month']) ? '0' : $_POST[$prefix . 'Month'];
    $day   = empty($_POST[$prefix . 'Day']) ? '0' : $_POST[$prefix . 'Day'];

    return $year . '-' . $month . '-' . $day;
}

/**
 * 设置管理员的session内容
 *
 * @access  public
 * @param   integer $user_id        管理员编号
 * @param   string  $username       管理员姓名
 * @param   string  $action_list    权限列表
 * @param   string  $last_time      最后登录时间
 * @return  void
 */
function set_admin_session($user_id, $username, $action_list, $last_time)
{
    $_SESSION['admin_id']    = $user_id;
    $_SESSION['admin_name']  = $username;
    $_SESSION['action_list'] = $action_list;
    $_SESSION['last_check']  = $last_time; // 用于保存最后一次检查订单的时间
}

/**
 * 插入一个配置信息
 *
 * @access  public
 * @param   string      $parent     分组的code
 * @param   string      $code       该配置信息的唯一标识
 * @param   string      $value      该配置信息值
 * @return  void
 */
function insert_config($parent, $code, $value)
{
    global $aos, $db, $_LANG;

    $sql = 'SELECT id FROM ' . $aos->table('shop_config') . " WHERE code = '$parent' AND type = 1";
    $parent_id = $db->getOne($sql);

    $sql = 'INSERT INTO ' . $aos->table('shop_config') . ' (parent_id, code, value) ' .
            "VALUES('$parent_id', '$code', '$value')";
    $db->query($sql);
}

/**
 * 判断管理员对某一个操作是否有权限。
 *
 * 根据当前对应的action_code，然后再和用户session里面的action_list做匹配，以此来决定是否可以继续执行。
 * @param     string    $priv_str    操作对应的priv_str
 * @param     string    $msg_type       返回的类型
 * @return true/false
 */
function admin_priv($priv_str, $msg_type = '' , $msg_output = true)
{
    global $_LANG;

    if ($_SESSION['action_list'] == 'all')
    {
        return true;
    }

    //if (strpos(',' . $_SESSION['action_list'] . ',', ',' . $priv_str . ',') === false)
    if(!in_array($priv_str,explode(",",$_SESSION['action_list'])))
    {
        $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
        if ( $msg_output)
        {
            sys_msg('对不起,您没有执行此项操作的权限!', 0, $link);
        }
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * 检查管理员权限
 *
 * @access  public
 * @param   string  $authz
 * @return  boolean
 */
function check_authz($authz)
{
    return (preg_match('/,*'.$authz.',*/', $_SESSION['action_list']) || $_SESSION['action_list'] == 'all');
}

/**
 * 检查管理员权限，返回JSON格式数剧
 *
 * @access  public
 * @param   string  $authz
 * @return  void
 */
function check_authz_json($authz)
{
    if (!check_authz($authz))
    {
        make_json_error('对不起,您没有执行此项操作的权限!');
    }
}

/**
 * 取得优惠券类型数组（用于生成下拉列表）
 *
 * @return  array       分类数组 bonus_typeid => bonus_type_name
 */
function get_bonus_type()
{
    $bonus = array();
    $sql = 'SELECT type_id, type_name, type_money FROM ' . $GLOBALS['aos']->table('bonus_type') .
           ' WHERE send_type = 3';
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $bonus[$row['type_id']] = $row['type_name'].' [' .sprintf($GLOBALS['_CFG']['currency_format'], $row['type_money']).']';
    }

    return $bonus;
}

/**
 * 取得广告位置数组（用于生成下拉列表）
 *
 * @return  array       分类数组 position_id => position_name
 */
function get_position_list()
{
    $position_list = array();
    $sql = 'SELECT position_id, position_name, ad_width, ad_height '.
           'FROM ' . $GLOBALS['aos']->table('ad_position')." where position_id != 4";
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $position_list[$row['position_id']] = addslashes($row['position_name']). ' [' .$row['ad_width']. 'x' .$row['ad_height']. ']';
    }

    return $position_list;
}


/**
 * 取得商品列表：用于把商品添加到组合、关联类、赠品类
 * @param   object  $filters    过滤条件
 */
function get_goods_list($keyword="",$cat_id="")
{
    //$filter->keyword = json_str_iconv($filter->keyword);
    $where = " where  is_delete = 0 "; // 取得过滤条件
    if(!empty($keyword)){
        $where .= " and  goods_name like '%".trim($keyword)."%' "; 
    }
    if(!empty($cat_id)){
        $where .= " and  cat_id = $cat_id "; 
    }
    /* 取得数据 */
    $sql = 'SELECT goods_id, goods_name, shop_price '.
           'FROM ' . $GLOBALS['aos']->table('goods') . ' AS g ' . $where .
           'LIMIT 50';
    $row = $GLOBALS['db']->getAll($sql);

    return $row;
}

/**
 * 返回是否
 * @param   int     $var    变量 1, 0
 */
function get_yes_no($var)
{
    return empty($var) ? '<img src="images/no.gif" border="0" />' : '<img src="images/yes.gif" border="0" />';
}

/**
 * 生成过滤条件：用于 get_goodslist 和 get_goods_list
 * @param   object  $filter
 * @return  string
 */
function get_where_sql($filter)
{
    $time = date('Y-m-d');

    $where  = isset($filter->is_delete) && $filter->is_delete == '1' ?
        ' WHERE is_delete = 1 ' : ' WHERE is_delete = 0 ';
    $where .= isset($filter->cat_id) && $filter->cat_id > 0 ? ' AND ' . get_children($filter->cat_id) : '';
    $where .= isset($filter->brand_id) && $filter->brand_id > 0 ? " AND brand_id = '" . $filter->brand_id . "'" : '';
    $where .= isset($filter->intro_type) && $filter->intro_type != '0' ? ' AND ' . $filter->intro_type . " = '1'" : '';
    $where .= isset($filter->intro_type) && $filter->intro_type == 'is_promote' ?
        " AND promote_start_date <= '$time' AND promote_end_date >= '$time' " : '';
    $where .= isset($filter->keyword) && trim($filter->keyword) != '' ?
        " AND (goods_name LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_sn LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_id LIKE '%" . mysql_like_quote($filter->keyword) . "%') " : '';
    $where .= isset($filter->suppliers_id) && trim($filter->suppliers_id) != '' ?
        " AND (suppliers_id = '" . $filter->suppliers_id . "') " : '';

    $where .= isset($filter->in_ids) ? ' AND goods_id ' . db_create_in($filter->in_ids) : '';
    $where .= isset($filter->exclude) ? ' AND goods_id NOT ' . db_create_in($filter->exclude) : '';
    $where .= isset($filter->stock_warning) ? ' AND goods_number <= warn_number' : '';

    return $where;
}

/**
 * 获取地区列表的函数。
 *
 * @access  public
 * @param   int     $region_id  上级地区id
 * @return  void
 */
function area_list($region_id)
{
    $area_arr = array();

    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('region').
           " WHERE parent_id = '$region_id' ORDER BY region_id";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['type']  = ($row['region_type'] == 0) ? '一级地区'  : '';
        $row['type'] .= ($row['region_type'] == 1) ? '二级地区' : '';
        $row['type'] .= ($row['region_type'] == 2) ? '三级地区'     : '';
        $row['type'] .= ($row['region_type'] == 3) ? '四级地区' : '';

        $area_arr[] = $row;
    }

    return $area_arr;
}

/**
 * 清空表数据
 * @param   string  $table_name 表名称
 */
function truncate_table($table_name)
{
    $sql = 'TRUNCATE TABLE ' .$GLOBALS['aos']->table($table_name);

    return $GLOBALS['db']->query($sql);
}

/**
 *  返回字符集列表数组
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_charset_list()
{
    return array(
        'UTF8'   => 'UTF-8',
        'GB2312' => 'GB2312/GBK',
        'BIG5'   => 'BIG5',
    );
}


/**
 * 创建一个JSON格式的数据
 *
 * @access  public
 * @param   string      $content
 * @param   integer     $error
 * @param   string      $message
 * @param   array       $append
 * @return  void
 */
function make_json_response($content='', $error="0", $message='', $append=array())
{
    $res = array('error' => $error, 'message' => $message, 'content' => $content);
    if (!empty($append))
    {
        foreach ($append AS $key => $val)
        {
            $res[$key] = $val;
        }
    }
    $val = json_encode($res);
    exit($val);
}

/**
 *
 *
 * @access  public
 * @param
 * @return  void
 */
function make_json_result($content, $message='', $append=array())
{
    make_json_response($content, 0, $message, $append);
}

/**
 * 创建一个JSON格式的错误信息
 *
 * @access  public
 * @param   string  $msg
 * @return  void
 */
function make_json_error($msg)
{
    make_json_response('', 1, $msg);
}

/**
 * 根据过滤条件获得排序的标记
 *
 * @access  public
 * @param   array   $filter
 * @return  array
 */
function sort_flag($filter)
{
    $flag['tag']    = 'sort_' . preg_replace('/^.*\./', '', $filter['sort_by']);
    //$flag['img']    = '<img src="images/' . ($filter['sort_order'] == "DESC" ? 'sort_desc.gif' : 'sort_asc.gif') . '"/>';

    $flag['img']    = $filter['sort_order'];



    return $flag;
}

/**
 * 分页的信息加入条件的数组
 *
 * @access  public
 * @return  array
 */
function page_and_size($filter)
{
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    else
    {
        $filter['page_size'] = 10;
    }

    /* 每页显示 */
    $filter['page'] = (empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    /* page 总数 */
    $filter['page_count'] = (!empty($filter['record_count']) && $filter['record_count'] > 0) ? ceil($filter['record_count'] / $filter['page_size']) : 1;

    /* 边界处理 */
    if ($filter['page'] > $filter['page_count'])
    {
        $filter['page'] = $filter['page_count'];
    }

    $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];

    return $filter;
}

/**
 *  将含有单位的数字转成字节
 *
 * @access  public
 * @param   string      $val        带单位的数字
 *
 * @return  int         $val
 */
function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last)
    {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

/**
 * 生成链接后缀
 */
function list_link_postfix()
{
    return 'uselastfilter=1';
}

/**
 * 保存过滤条件
 * @param   array   $filter     过滤条件
 * @param   string  $sql        查询语句
 * @param   string  $param_str  参数字符串，由list函数的参数组成
 */
function set_filter($filter, $sql, $param_str = '')
{
    $filterfile = basename(PHP_SELF, '.php');
    if ($param_str)
    {
        $filterfile .= $param_str;
    }
    setcookie('AOSCP[lastfilterfile]', sprintf('%X', crc32($filterfile)), time() + 600);
    setcookie('AOSCP[lastfilter]',     urlencode(serialize($filter)), time() + 600);
    setcookie('AOSCP[lastfiltersql]',  base64_encode($sql), time() + 600);
}

/**
 * 取得上次的过滤条件
 * @param   string  $param_str  参数字符串，由list函数的参数组成
 * @return  如果有，返回array('filter' => $filter, 'sql' => $sql)；否则返回false
 */
function get_filter($param_str = '')
{
    $filterfile = basename(PHP_SELF, '.php');
    if ($param_str)
    {
        $filterfile .= $param_str;
    }
    if (isset($_GET['uselastfilter']) && isset($_COOKIE['AOSCP']['lastfilterfile'])
        && $_COOKIE['AOSCP']['lastfilterfile'] == sprintf('%X', crc32($filterfile)))
    {
        return array(
            'filter' => unserialize(urldecode($_COOKIE['AOSCP']['lastfilter'])),
            'sql'    => base64_decode($_COOKIE['AOSCP']['lastfiltersql'])
        );
    }
    else
    {
        return false;
    }
}

/**
 * URL过滤
 * @param   string  $url  参数字符串，一个urld地址,对url地址进行校正
 * @return  返回校正过的url;
 */
function sanitize_url($url , $check = 'http://')
{
    if (strpos( $url, $check ) === false)
    {
        $url = $check . $url;
    }
    return $url;
}

/**
 * 检查分类是否已经存在
 *
 * @param   string      $cat_name       分类名称
 * @param   integer     $parent_cat     上级分类
 * @param   integer     $exclude        排除的分类ID
 *
 * @return  boolean
 */
function cat_exists($cat_name, $parent_cat, $exclude = 0)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('category').
    " WHERE parent_id = '$parent_cat' AND cat_name = '$cat_name' AND cat_id<>'$exclude'";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

function brand_exists($brand_name)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('brand').
    " WHERE brand_name = '" . $brand_name . "'";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

/**
 * 获取当前管理员信息
 *
 * @access  public
 * @param
 *
 * @return  Array
 */
function admin_info()
{
    $sql = "SELECT * FROM ". $GLOBALS['aos']->table('admin_user')."
            WHERE user_id = '$_SESSION[admin_id]'
            LIMIT 0, 1";
    $admin_info = $GLOBALS['db']->getRow($sql);

    if (empty($admin_info))
    {
        return $admin_info = array();
    }

    return $admin_info;
}

/**
 * 供货商列表信息
 *
 * @param       string      $conditions
 * @return      array
 */
function suppliers_list_info($conditions = '')
{
    $where = '';
    if (!empty($conditions))
    {
        $where .= 'WHERE ';
        $where .= $conditions;
    }

    /* 查询 */
    $sql = "SELECT suppliers_id, suppliers_name, suppliers_desc
            FROM " . $GLOBALS['aos']->table("suppliers") . "
            $where";

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 供货商名
 *
 * @return  array
 */
function suppliers_list_name()
{
    /* 查询 */
    $suppliers_list = suppliers_list_info(' is_check = 1 ');

    /* 供货商名字 */
    $suppliers_name = array();
    if (count($suppliers_list) > 0)
    {
        foreach ($suppliers_list as $suppliers)
        {
            $suppliers_name[$suppliers['suppliers_id']] = $suppliers['suppliers_name'];
        }
    }

    return $suppliers_name;
}
//分页
function get_page($filter)
{
    //global $cfg_isreurl,$keyword;
    $totalpage = $filter['page_count'];
    $pagenum=$filter['page_size'];
    $page = empty($filter['page']) ? 1 : $filter['page'];

    $total=$filter['record_count'];
    $pagetxt = '';

    //if($total <= $pagenum)
    //{
        
    //}

    //else
    //{
        //获取除page参数外的其他参数
        $query_str = explode('&',$_SERVER['QUERY_STRING']);

        if($query_str[0] != '')
        {
            $query_strs = '';

            foreach($query_str as $k)
            {
                $query_str_arr = explode('=', $k);

                if(strstr($query_str_arr[0],'page') == '')
                {
                    $query_str_arr[0] = isset($query_str_arr[0]) ? $query_str_arr[0] : '';
                    $query_str_arr[1] = isset($query_str_arr[1]) ? $query_str_arr[1] : '';
                    if(!empty($query_str_arr[1])){
                        $query_strs .= $query_str_arr[0].'='.$query_str_arr[1].'&';
                    }
                    
                }
            }

            $nowurl = 'index.php?'.$query_strs;
        }
        else
        {
            $nowurl = 'index.php?';
        }

        

        $previous = $page - 1;

        if($totalpage == $page)
            $next = $page;
        else
            $next = $page + 1;

        $pagetxt .= '<div class="pages">';

        $pagetxt .= '<span class="page_info">共'.$totalpage.'页'.$total.'条记录</span>';

        //上一页 第一页
        if($page > 1)
        {
            
                $pagetxt .= '<a href="'.$nowurl.'page=1" title="第一页">首页</a>';
                $pagetxt .= '<a href="'.$nowurl.'page='.$previous.'" title="上一页">上一页</a>';
            
        }
        else
        {
            $pagetxt .= '<a href="javascript:;" title="已是第一页">首页</a>';
            $pagetxt .= '<a href="javascript:;" title="已是第一页">第一页</a>';
        }

        //当总页数小于10
        if($totalpage < 10)
        {
            for($i=1; $i <= $totalpage; $i++)
            {
                if($page == $i)
                {
                    $pagetxt .= '<a href="javascript:;" class="on">'.$i.'</a>';
                }
                else
                {
                    
                        $pagetxt .= '<a href="'.$nowurl.'page='.$i.'" class="num" title="第 '.$i.' 页">'.$i.'</a>';
                    
                }
            }
        }
        else
        {
            if($page==1 or $page==2 or $page==3)
            {
                $m = 1;
                $b = 7;
            }

            //如果页面大于前三页并且小于后三页则显示当前页前后各三页链接
            if($page>3 and $page<$totalpage-2)
            {
                $m = $page-3;
                $b = $page+3;
            }

            //如果页面为最后三页则显示最后7页链接
            if($page==$totalpage or $page==$totalpage-1 or $page==$totalpage-2)
            {
                $m = $totalpage - 7;
                $b = $totalpage;
            }
            if($page > 4)
            {
                $pagetxt .= '<a href="javascript:;">...</a>';
            }

            //显示数字页码
            for($i=$m; $i<=$b; $i++)
            {
                if($page == $i)
                {
                    $pagetxt .= '<a href="'.$nowurl.'page='.$i.'" class="on">'.$i.'</a>';
                }
                else
                {
                    
                        $pagetxt .= '<a href="'.$nowurl.'page='.$i.'" class="num" title="第 '.$i.' 页">'.$i.'</a>';
                    
                }
            }
            if($page < $totalpage-3)
            {
                $pagetxt .= '<a href="javascript:;">...</a>';
            }
        }

        //下一页 最后页
        if($page < $totalpage)
        {
            
                $pagetxt .= '<a href="'.$nowurl.'page='.$next.'" title="下一页">下一页</a>';
                $pagetxt .= '<a href="'.$nowurl.'page='.$totalpage.'" title="最后一页">尾页</a>';
            
        }
        else
        {
            $pagetxt .= '<a href="javascript:;" title="已是最后一页">下一页</a>';
            $pagetxt .= '<a href="javascript:;" title="已是最后一页">尾页</a>';
        }
        $pagetxt .= '</div>';
    //}

    return $pagetxt;
}
/**
 * 删除用户
 *
 * @access  public
 * @param
 *
 * @return void
 */
function remove_user($id)
{
    $post_id = $id;

    if (!empty($post_id))
    {
        /* 如果需要同步或是aoshop插件执行这部分代码 */
        $sql = "SELECT user_id FROM "  . $GLOBALS['aos']->table('users') . " WHERE ";
        $sql .= (is_array($post_id)) ? db_create_in($post_id, 'user_id') : "user_id='". $post_id . "' LIMIT 1";
        $col = $GLOBALS['db']->getCol($sql);

        if ($col)
        {
            $sql = "UPDATE " . $GLOBALS['aos']->table('users') . " SET parent_id = 0 WHERE " . db_create_in($col, 'parent_id'); //将删除用户的下级的parent_id 改为0
            $GLOBALS['db']->query($sql);
            
            /* 删除用户订单 */
            $sql = "SELECT order_id FROM " . $GLOBALS['aos']->table('order_info') . " WHERE " . db_create_in($col, 'user_id');
            $GLOBALS['db']->query($sql);
            $col_order_id = $GLOBALS['db']->getCol($sql);
            if ($col_order_id)
            {
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('order_info') . " WHERE " . db_create_in($col_order_id, 'order_id');
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('order_goods') . " WHERE " . db_create_in($col_order_id, 'order_id');
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('back_goods') . " WHERE back_id in ( select back_id from ". $GLOBALS['aos']->table('back_order') ." where " . db_create_in($col_order_id, 'order_id').")";
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('back_order') . " WHERE " . db_create_in($col_order_id, 'order_id');
                $GLOBALS['db']->query($sql);
                
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('delivery_order') . " WHERE " . db_create_in($col_order_id, 'order_id');
                $GLOBALS['db']->query($sql);
            }

            
            $sql = "DELETE FROM " . $GLOBALS['aos']->table('collect') . " WHERE " . db_create_in($col, 'user_id'); //删除会员收藏商品
            $GLOBALS['db']->query($sql);
            $sql = "DELETE FROM " . $GLOBALS['aos']->table('comment') . " WHERE " . db_create_in($col, 'user_id'); //删除用户留言
            $GLOBALS['db']->query($sql);
            $sql = "DELETE FROM " . $GLOBALS['aos']->table('user_address') . " WHERE " . db_create_in($col, 'user_id'); //删除用户地址
            $GLOBALS['db']->query($sql);
            $sql = "DELETE FROM " . $GLOBALS['aos']->table('user_bonus') . " WHERE " . db_create_in($col, 'user_id'); //删除用户优惠券
            $GLOBALS['db']->query($sql);
            $sql = "DELETE FROM " . $GLOBALS['aos']->table('user_account') . " WHERE " . db_create_in($col, 'user_id'); //删除用户帐号金额
            $GLOBALS['db']->query($sql);
            
            $sql = "DELETE FROM " . $GLOBALS['aos']->table('account_log') . " WHERE " . db_create_in($col, 'user_id'); //删除用户日志
            $GLOBALS['db']->query($sql);

            $sql = "DELETE FROM " . $GLOBALS['aos']->table('users') . " WHERE " . db_create_in($col, 'user_id'); //删除用户
            $GLOBALS['db']->query($sql);
            return true;
        }
    }

}
?>